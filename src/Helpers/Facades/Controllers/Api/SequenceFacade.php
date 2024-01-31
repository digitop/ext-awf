<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Events\NextProductEvent;
use AWF\Extension\Helpers\Models\NextProductEventModel;
use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\NextProductResponseData;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_LOG;
use AWF\Extension\Responses\CustomJsonResponse;
use AWF\Extension\Responses\NextProductEventResponse;
use AWF\Extension\Responses\SequenceFacadeResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Http\FormRequest;

class SequenceFacade extends Facade
{
    public function create(Request|FormRequest|null $request = null, Model|string|null $model = null): JsonResponse|null
    {
        $sequences = AWF_SEQUENCE::where('SEINPR', '=', 0)
            ->orderBy('SEPILL', 'DESC')
            ->orderBy('SEQUID', 'ASC')
            ->get();

        if ($sequences === null || !array_key_exists(0, $sequences->all()) || empty($sequences[0])) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    false,
                    [],
                    __('response.unprocessable_entity')
                ),
                Response::HTTP_OK
            ));
        }

        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                true,
                (new SequenceFacadeResponse($sequences, $model))->generate()
            ),
            Response::HTTP_OK
        ));
    }

    public function show(Request|FormRequest|null $request = null, Model|string|null ...$model): JsonResponse|null
    {
        list($workCenter, $pillar) = $model;

        if (is_string($pillar) && $pillar === 'null') {
            $pillar = null;
        }

        $logs = AWF_SEQUENCE_LOG::where('WCSHNA', '=', $workCenter->WCSHNA)
            ->whereNull('LSTIME')
            ->whereNull('LETIME')
            ->get();

        if ($logs === null || !array_key_exists(0, $logs->all()) || empty($logs[0])) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    false,
                    [],
                    __('response.no_new_data_available')
                ),
                Response::HTTP_OK
            ));
        }

        $sequences = new Collection();

        foreach ($logs as $log) {
            $sequence = AWF_SEQUENCE::where('SEQUID', '=', $log->SEQUID)->first();

            if ($pillar !== null) {
                $sequence = AWF_SEQUENCE::where('SEQUID', '=', $log->SEQUID)->where('SEPILL', '=', $pillar)->first();
            }

            if (array_key_exists('side', $request->all())) {
                $sequence = AWF_SEQUENCE::where('SEQUID', '=', $log->SEQUID)
                    ->where('SESIDE', '=', $request->side)
                    ->first();

                if ($pillar !== null) {
                    $sequence = AWF_SEQUENCE::where('SEQUID', '=', $log->SEQUID)
                        ->where('SESIDE', '=', $request->side)
                        ->where('SEPILL', '=', $pillar)
                        ->first();
                }
            }

            if (!empty($sequence)) {
                $sequences->add($sequence);
            }
        }

        $sequence = $sequences->sort(function ($a, $b) {
            foreach ([['column' => 'SEPILL', 'order' => 'desc'], ['column' => 'SEQUID']] as $sortingInstruction) {

                $a[$sortingInstruction['column']] = $a[$sortingInstruction['column']] ?? '';
                $b[$sortingInstruction['column']] = $b[$sortingInstruction['column']] ?? '';

                if (empty($sortingInstruction['order']) || strtolower($sortingInstruction['order']) === 'asc') {
                    $x = ($a[$sortingInstruction['column']] <=> $b[$sortingInstruction['column']]);
                }
                else {
                    $x = ($b[$sortingInstruction['column']] <=> $a[$sortingInstruction['column']]);
                }

                if ($x !== 0) {
                    return $x;
                }

            }

            return 0;
        })
            ->take($request->limit ?? 2);

        event(new NextProductEvent(
                (new NextProductEventResponse($sequence, null))->generate()
            )
        );

        foreach ($sequence as $item) {
            AWF_SEQUENCE_LOG::where('WCSHNA', '=', $workCenter->WCSHNA)
                ->where('SEQUID', '=', $item->SEQUID)
                ->whereNull('LSTIME')
                ->whereNull('LETIME')
                ->update([
                    'LSTIME' => (new \DateTime()),
                ]);
        }

        $workCenter->features()->where('WFSHNA', '=', 'OPSTATUS')->first()?->update([
            'WFVALU' => 'default',
        ]);

        publishMqtt(env('DEPLOYMENT_SUBDOMAIN') . '/api/SEQUENCE_CHANGE/', [
            [
                "to" => 'dh:' . $workCenter->operatorPanels[0]->dashboard->DHIDEN,
                "payload" => [
                    "status" => "default",
                ],
            ]
        ]);

        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                true,
                (new SequenceFacadeResponse($sequence, $workCenter))->generate()
            ),
            Response::HTTP_OK
        ));
    }

    public function set(string $pillar, string $sequenceId): RedirectResponse
    {
        $sequences = AWF_SEQUENCE::where('SEPILL', '=', $pillar)
            ->where('SEQUID', '<', $sequenceId)
            ->where('SEINPR', '=', 0)
            ->update([
                'SEINPR' => 1,
            ]);

        return back()->with('notification-success', __('responses.update'));
    }
}
