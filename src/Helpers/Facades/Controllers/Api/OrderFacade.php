<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

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
        $start = (new \DateTime())->format('Y-m-d') . ' 00:00:00';
        $sequence = null;

        $waitings = DB::connection('custom_mysql')->select('
            select a.ORCODE
            from AWF_SEQUENCE a
                join ' . $database . '.PRWFDATA pfd on pfd.PRCODE = a.PRCODE
                join ' . $database . '.PRWCDATA pcd on pfd.PFIDEN = pcd.PFIDEN and pcd.WCSHNA = "' . $model->WCSHNA . '"
                join ' . $database . '.PROPDATA ppd on ppd.PFIDEN = pcd.PFIDEN and ppd.OPSHNA = pcd.OPSHNA
                left join AWF_SEQUENCE_LOG asl on a.SEQUID = asl.SEQUID and asl.WCSHNA = pcd.WCSHNA
            where a.SEINPR < ppd.PORANK
                and (asl.LSTIME >= "' . $start . '" or asl.LSTIME is null)
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
}