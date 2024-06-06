<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Models\AWF_SEQUENCE;
use App\Http\Controllers\api\dashboard\scrapStation\ScrapStationController;
use AWF\Extension\Models\AWF_SEQUENCE_WORKCENTER;
use AWF\Extension\Responses\CustomJsonResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use App\Models\SERIALNUMBER;
use App\Models\WORKCENTER;
use App\Models\DASHBOARD_MODULE_SETTINGS;
use App\Models\REPNO;
use App\Models\DASHBOARD;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use App\Events\Api\OperatorPanelSaveSerialEvent;
use App\Http\Controllers\api\dashboard\operatorPanel\OperatorPanelController;

/**
 * API endpoint for barcode/serial verification of qualification stations
 */
class CheckProductFacade extends Facade
{
    public function check(Request|FormRequest|null $request = null, Model|string|null $model = null): JsonResponse|null
    {
        $moduleSetting = DASHBOARD_MODULE_SETTINGS::where([
            ['DHIDEN', $request->dashboard],
            ['DMSKEY', 'scrapStationFilter']
        ])
            ->first();

        if ($moduleSetting) {
            $scrapStationFilter = $moduleSetting->DMSVAL;
            // Ha van beallitva ertek a szuroben
            $workCenter = WORKCENTER::find($scrapStationFilter); // Selejt állomás megkeresese
        }

        if (strtolower($request->serial) === 'dummy') {
            publishMqtt(env('DEPLOYMENT_SUBDOMAIN') . '/api/SCAN_OK/', [
                [
                    "to" => 'wc:' . $workCenter->WCSHNA,
                    "payload" => [
                        "status" => true,
                        "serial" => $request->serial,
                    ],
                ]
            ]);

            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    true,
                    [
                        'orderCode' => null,
                        'name' => null,
                        "serial" => $request->serial,
                    ]
                ),
                Response::HTTP_OK
            )
            );
        }

        $start = (new \DateTime())->format('Y-m-d') . ' 00:00:00';
        $database = config('database.connections.mysql.database');

        $waitings = DB::connection('custom_mysql')->select('
            select asl.LSTIME, a.SEQUID, a.SEPONR, a.SEPSEQ, a.SESIDE, a.SEPILL, a.SEINPR, a.PRCODE,
                   a.ORCODE, r.PORANK, r.OPSHNA, p.PRNAME, r.RNREPN
            from AWF_SEQUENCE_LOG asl
                join AWF_SEQUENCE a on a.SEQUID = asl.SEQUID
                join ' . $database . '.PRODUCT p on p.PRCODE = a.PRCODE
                join ' . $database . '.REPNO r on r.ORCODE = a.ORCODE and r.WCSHNA = asl.WCSHNA
            where ((asl.LSTIME is null and a.SEINPR = (r.PORANK - 1)) or (asl.LSTIME > "' . $start .
            '" and a.SEINPR = r.PORANK)) and asl.LETIME is null and
                asl.WCSHNA = "' . $workCenter->WCSHNA . '"' .
            ' order by a.SEQUID limit 1
            '
        );

        if (!array_key_exists(0, $waitings) || empty($waitings[0])) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    false,
                    [],
                    __('response.check.not_next_product')
                ),
                Response::HTTP_OK
            ));
        }

        $serial = DB::select('
            select s.SNRDCN, r.RNREPN, s.PRCODE from SERIALNUMBER s
                join REPNO r on r.ORCODE = substring(s.RNREPN, 1, position("-" in s.RNREPN) - 1) and
                     s.SNSERN = "' . $request->serial . '" and r.WCSHNA = "' . $workCenter->WCSHNA . '"'
        );

        if (!array_key_exists(0, $serial) || empty($serial[0])) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    false,
                    [],
                    __(
                        'response.check.cannot_attach_piece',
                        ['waiting' => $waitings[0]->PRCODE ?? null, 'got' => $serial?->PRCODE ?? null]
                    )
                ),
                Response::HTTP_OK
            )
            );
        }

        $serial = $serial[0];

        if (array_key_exists(0, $waitings) && $serial->PRCODE !== $waitings[0]->PRCODE) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    false,
                    [],
                    __(
                        'response.check.cannot_attach_piece',
                        ['waiting' => $waitings[0]->PRCODE, 'got' => $serial?->PRCODE]
                    )
                ),
                Response::HTTP_OK
            ));
        }

        $serialCheck = (new OperatorPanelController())->validateSerial(
            new Request([
                'SNSERN' => $request->serial,
                'RNREPN' => $serial->RNREPN,
                'SNCOUN' => 1,
                'SNRDCN' => $serial->SNRDCN,
                'subProduct' => false,
                'parentSNSERN' => false,
                'PRCODE' => $waitings[0]->PRCODE,
            ]),
            $request->dashboard
        );

        $validate = json_decode($serialCheck, true);

        if ($validate['success'] !== true) {
            $error = $serialCheck;

            if (array_key_exists('errorCode', $validate)) {
                if ($validate['errorCode'] == -3) {
                    $error = 'A vonalkód még nem lett minősítve';
                }

                if ($validate['errorCode'] == -4) {
                    $error = 'A termék nem volt az előző műveletben';
                }
            }

            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    false,
                    [],
                    $error
                ),
                Response::HTTP_OK
            )
            );
        }

        $serialCheck =(new ScrapStationController())->findSerial(
            $request->dashboard,
            new Request([
                'serial' => $request->serial,
            ]),
            $workCenter
        );

        if (
            is_array($serialCheck) &&
            (
                $serialCheck['success'] == false ||
                (
                    array_key_exists('serials', $serialCheck) &&
                    $serialCheck['serials'][0]['isNew'] == false &&
                    $serialCheck['serials'][0]['isReproduced'] == false
                )
            )
        ) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    false,
                    [],
                    'A vonalkód már le lett minősítve, újraminősítés nem lehetséges'
                ),
                Response::HTTP_OK
            ));
        }

        publishMqtt(env('DEPLOYMENT_SUBDOMAIN') . '/api/SCAN_OK/', [
            [
                "to" => 'wc:' .  $workCenter->WCSHNA,
                "payload" => [
                    "status" => true,
                    "serial" => $request->serial,
                ],
            ]
        ]);

        $sequence = AWF_SEQUENCE::where('SEQUID', '=', $waitings[0]->SEQUID)->first();
        $sequenceWorkCenter = AWF_SEQUENCE_WORKCENTER::where('SEQUID', '=', $waitings[0]->SEQUID)->first();
        $orderCode = $sequence->ORCODE;

        if ($serial->RNREPN !== $sequenceWorkCenter->RNREPN) {
            //$orderCode = REPNO::where('RNREPN', '=', $serial->RNREPN)->with('orderhead')->first()->orderhead->PRCODE;
            $orderCode = REPNO::where('RNREPN', '=', $serial->RNREPN)->first()->ORCODE;
        }

        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                true,
                [
                    'orderCode' => $orderCode,
                    'name' => $waitings[0]?->PRNAME ?? null,
                    "serial" => $request->serial,
                ]
            ),
            Response::HTTP_OK
        ));
    }

    public function publis(OperatorPanelSaveSerialEvent $event)
    {
        publishMqtt(env('DEPLOYMENT_SUBDOMAIN') . '/api/SCAN_OK/', [
            [
                "to" => 'wc:' .  $event->WCSHNA,
                "payload" => [
                    "status" => true,
                ],
            ]
        ]);
    }
}
