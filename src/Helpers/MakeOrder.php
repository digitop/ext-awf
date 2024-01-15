<?php

namespace AWF\Extension\Helpers;

use App\Http\Controllers\production\order\OrderController;
use App\Http\Requests\production\order\InsertRequest;
use App\Models\REPNO;
use AWF\Extension\Models\AWF_SEQUENCE_WORKCENTER;
use Illuminate\Database\Eloquent\Model;
use App\Models\PARAMETERS;
use App\Models\ORDERHEAD;
use App\Models\PRWFDATA;

class MakeOrder
{
    public static function makeOrder(Model $sequenceData): void
    {
        $i = 1;
        $orcode = $sequenceData->SEPONR . '_' . $sequenceData->SEPSEQ . '_' . $sequenceData->SEARNU;

        while (!empty(ORDERHEAD::where('ORCODE', '=', $orcode . '_' . $i)->first())) {
            $i++;
        }

        $orcode .= '_' . $i;

        $workflow = PRWFDATA::where('PRCODE', $sequenceData->PRCODE)->first();

        $orderController = new OrderController;
        $orderDefaultNotification = PARAMETERS::find('ORDER_DEFAULT_NOTIFICATION');
        $orderNotificationEnabled = PARAMETERS::find('ORDER_NOTIFICATION_ENABLED');

        $orderController->store(
            new InsertRequest([
                "PRCODE" => $sequenceData->PRCODE, //TERMÉK KÓD
                "PRORCO" => null, //null mindig
                "ORCODE" => $orcode, //Megrendelés kód
                "PFIDEN" => $workflow->PFIDEN, //Termék alapértelmezett gyártási folyamata
                "ORSTAT" => 1, // MINDIG 1
                "ORQUAN" => 1, // MINDIG 1
                "ORQYEN" => 1,
                "ORRQEN" => 1,
                "ORNOID" => $orderDefaultNotification->PAVALU ?? null, //PARAMTER tábla ORDER_DEFAULT_NOTIFICATION
                "ORNOTC" => $orderNotificationEnabled->PAVALU ?? 0, //PARAMTER tábla ORDER_NOTIFICATION_ENABLED
                "ORNOSC" => 60, // DEFAULT 60
                "ORSCTY" => 2, // MINDIG 2, Gyártási terv típus 2 = kézi
                "ORSCVA" => 1, // MINDIG 1, gyártási terv kezdő érték
                "ORSCW1" => 5, // mindig 5, gyártási terv 1. figyelmeztetés
                "ORSCW2" => 10, // mindig 10, gyártási terv 2. figyelmeztetés
                "ORSCSW" => null, // null mindig, gyártási terv dátum alapú váltása
            ])
        );

        $sequenceData->update([
            'ORCODE' => $orcode,
        ]);

        $sequenceWorkCenter = AWF_SEQUENCE_WORKCENTER::where('SEQUID', '=', $sequenceData->SEQUID)->first();

        $repno = REPNO::where([['WCSHNA', $sequenceWorkCenter->WCSHNA], ['ORCODE', $orcode]])->first();

        $repno->update([
            'RNACTV' => 1,
        ]);

        $sequenceWorkCenter->update([
            'RNREPN' => $repno->RNREPN,
        ]);
    }
}
