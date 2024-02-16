<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use App\Http\Controllers\api\dashboard\operatorPanel\OperatorPanelController;
use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Responses\CustomJsonResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\SERIALNUMBER;
use App\Models\WORKCENTER;
use App\Models\DASHBOARD;
use App\Models\REPNO;

class OrderFacade extends Facade
{
    public function create(Request|FormRequest|null $request = null, Model|string|null $model = null): JsonResponse|null
    {
        $success = false;

        if ($model === null || empty($model)) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    $success,
                    [],
                    'Nem található munkaállomás'
                ),
                Response::HTTP_OK
            ));
        }

        $database = config('database.connections.mysql.database');
        $sequence = null;

        $waitings = DB::connection('custom_mysql')->select('
            select a.ORCODE
            from AWF_SEQUENCE a
                join ' . $database . '.PRWFDATA pfd on pfd.PRCODE = a.PRCODE
                join ' . $database . '.PRWCDATA pcd on pfd.PFIDEN = pcd.PFIDEN and pcd.WCSHNA = "' . $model->WCSHNA . '"
                join ' . $database . '.PROPDATA ppd on ppd.PFIDEN = pcd.PFIDEN and ppd.OPSHNA = pcd.OPSHNA
                left join AWF_SEQUENCE_LOG asl on a.SEQUID = asl.SEQUID and asl.WCSHNA = pcd.WCSHNA
            where a.SEINPR < ppd.PORANK
                and (asl.LSTIME is null and asl.LSTIME is null)
            order by asl.LSTIME DESC, a.SEQUID limit 1
        ');

        if (array_key_exists(0, $waitings) && !empty($waitings[0])) {
            $sequence = $waitings[0];
            $success = true;
        }

        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                $success,
                [
                    'orderCode' => $sequence?->ORCODE ?? null
                ],
                $success ? '' : 'Nem áll rendelkezésre szekvencia adat'
            ),
            Response::HTTP_OK
        ));
    }

    public function store(FormRequest|Request $request, Model|string|null ...$model): JsonResponse|null
    {
        $success = false;
        $database = config('database.connections.mysql.database');
        $start = (new \DateTime())->format('Y-m-d') . ' 00:00:00';

        if (!$request->has('serial')) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    $success,
                    [
                        'orderCode' => null,
                        'side' => null,
                    ],
                    $success ? '' : 'Nem található serial number'
                ),
                Response::HTTP_OK
            ));
        }

        $workCenter = WORKCENTER::where('WCSHNA', '=', $model[0]->operatorPanels[0]->WCSHNA)->first();

        $waiting = DB::connection('custom_mysql')->select('
            select a.SEQUID, a.SESIDE, a.SEPILL, a.SEINPR, a.PRCODE, a.ORCODE, ppd.PFIDEN, ppd.PORANK, ppd.OPSHNA, asw.RNREPN
            from AWF_SEQUENCE a
                join ' . $database . '.PRWFDATA pfd on pfd.PRCODE = a.PRCODE
                join ' . $database . '.PRWCDATA pcd on pfd.PFIDEN = pcd.PFIDEN and pcd.WCSHNA = "' . $workCenter->WCSHNA . '"
                join ' . $database . '.PROPDATA ppd on ppd.PFIDEN = pcd.PFIDEN and ppd.OPSHNA = pcd.OPSHNA
                left join AWF_SEQUENCE_LOG asl on a.SEQUID = asl.SEQUID and asl.WCSHNA = pcd.WCSHNA
                left join AWF_SEQUENCE_WORKCENTER asw on asw.SEQUID = a.SEQUID and asw.WCSHNA = pcd.WCSHNA
            where a.SEINPR < ppd.PORANK
                and (asl.LSTIME >= "' . $start . '" or asl.LSTIME is null)
            order by asl.LSTIME DESC, a.SEQUID limit 1
        ');

        if (!empty($waiting[0])) {
            $waiting = $waiting[0];
        }
        else {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    $success,
                    [
                        'orderCode' => null,
                        'side' => null,
                    ],
                    $success ? '' : 'Nincs a gépnél következő darab'
                ),
                Response::HTTP_OK
            ));
        }

        $checkSerial = (new OperatorPanelController())->checkAndSaveSerial(
            new Request([
                'SNSERN' => $request->serial,
                'RNREPN' => $waiting->RNREPN,
                'SNCOUN' => 1,
                'SNRDCN' => 1,
                'subProduct' => false,
                'parentSNSERN' => false,
                'PRCODE' => $waiting->PRCODE,
            ]),
            $workCenter->operatorPanels[0]->dashboard->DHIDEN
        );

        if ($checkSerial['success'] == false) {
            if (array_key_exists('error', $checkSerial) && !empty($checkSerial['error'])) {
                $error = $checkSerial['error'];
            }
            else {
                $error = 'Hiba lépett fel az adatok mentése során!';
            }

            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    $success,
                    [
                        'orderCode' => null,
                        'side' => null,
                    ],
                    $success ? '' : $error
                ),
                Response::HTTP_OK
            ));
        }

        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                $success,
                [
                    'orderCode' => $repno?->ORCODE ?? null,
                    'side' => $waiting->SESIDE,
                ],
                $success ? '' : 'Hiba az adatok mentése során'
            ),
            Response::HTTP_OK
        ));
    }
}
