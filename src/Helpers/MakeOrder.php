<?php

namespace AWF\Extension\Helpers;

use App\Models\PRPR_OPDATA;
use App\Models\PRWCDATA;
use App\Models\PRWCSENSOR_DATA;
use AWF\Extension\Models\AWF_SEQUENCE_WORKCENTER;
use App\Models\PARAMETERS;
use App\Models\ORDERHEAD;
use App\Models\PRWFDATA;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class MakeOrder
{
    public static function makeOrder(Collection $sequences): void
    {
        self::generateOrders($sequences);
        self::generateRepnos($sequences);
    }

    protected static function generateOrders(Collection $sequences): void
    {
        $insertOrdersQuery = 'insert into ORDERHEAD (PRCODE, PRORCO, ORCODE, PFIDEN, ORSTAT, ORSTDA, ORQUAN, ORQYEN, ORRQEN, ORNOID, ORNOTC, ORNOSC, ORSCTY, ORSCVA, ORSCW1, ORSCW2, ORSCSW) values ';
        $insertOrderScheduleQuery = 'insert into ORDER_SCHEDULE (ORCODE, OSNAME, OSSTRD, OSQUAN, OSACTV) values ';
        $updateSequenceQuery = [];

        $orderCounter = 1;
        $previousPillar = $sequences[0]->SEPILL;
        $start = (new \DateTime())->add(new \DateInterval('PT2H'));
        $time = clone $start;

        for ($i = 0, $iMax = count($sequences); $i < $iMax; $i++) {
            if ($previousPillar !== $sequences[$i]->SEPILL) {
                $previousPillar = $sequences[$i]->SEPILL;
                $time = clone $start;
            }

            $orderCode = $sequences[$i]->SEPONR . '_' . $sequences[$i]->SEPSEQ . '_' . $sequences[$i]->SEARNU;

            $workflow = PRWFDATA::where('PRCODE', $sequences[$i]->PRCODE)->first();
            $orderDefaultNotification = PARAMETERS::find('ORDER_DEFAULT_NOTIFICATION');
            $orderNotificationEnabled = PARAMETERS::find('ORDER_NOTIFICATION_ENABLED');

            while (!empty(ORDERHEAD::where('ORCODE', '=', $orderCode . '_' . $orderCounter)->first())) {
                $orderCounter++;
            }

            $orderCode .= '_' . $orderCounter;

            $updateSequenceQuery[] = 'update AWF_SEQUENCE set ORCODE = "' . $orderCode . '" where SEQUID = ' . $sequences[$i]->SEQUID . ';';
            
            $insertOrdersQuery .= sprintf(
                '("%s",null,"%s","%s",1,"%s",1,1,1,%s,%s,60,2,1,5,10,null)',
                $sequences[$i]->PRCODE,
                $orderCode,
                $workflow->PFIDEN,
                $time->format('Y-m-d H:i:s'),
                $orderDefaultNotification->PAVALU ?? null,
                $orderNotificationEnabled->PAVALU ?? 0
            );

            $insertOrderScheduleQuery .= sprintf(
                '("%s","","%s",1,1)',
                $orderCode,
                $sequences[$i]->SEEXPI
            );

            if ($i < $iMax - 1) {
                $insertOrdersQuery .= ',';
                $insertOrderScheduleQuery .= ',';
            }

            $time->add(new \DateInterval('PT15M'));
        }
        
        DB::insert($insertOrdersQuery);
        DB::insert($insertOrderScheduleQuery);

        foreach ($updateSequenceQuery as $query) {
            DB::connection('custom_mysql')->update($query);
        }
    }

    protected static function generateRepnos(Collection $sequences): void
    {
        $insertRepnoQuery = 'insert into REPNO (RNREPN, WCSHNA, ORCODE, OPSHNA, PORANK, RNOPNA, RNORQY, RNPRQY, RNPGQY,
                   RNSCQY, RNMUQY, RNMULK, RNCYTI, RNDYPK, RNHUTI, RNMAOT, RNMMIN, RNMMAX, RNTOID, RNSTAT, RNACTV, 
                   RNOLAC, RNOLMU, RNBTCH, RNPLBL, RNCYCL, RNSNFC, PRSFCO, RNSNAU, RNSNUQ, RNSNNS, STORIN, STORMA, 
                   STOROU, STOROS, STOROC, PODESC, SGIDEN, WCSITY, WCSOTY, STSTAT, RNOVPR, PUIDEN) values ';
        $insertSensorQuery = 'insert into REPNO_SENSOR (RNREPN, CVIDEN, RSMIVA, RSMAVA, RSSETV) values ';
        $insertSensorQueryData = [];
        $insertMaterialQuery = 'insert into PRPR_OPDATA (RNREPN, PRCODE, RMQUAN) values ';
        $insertMaterialQueryData = [];

        $updateSequenceWorkCenterQuery = [];

        $hasSensor = false;
        $hasMaterial = false;

        $maxIndex = count($sequences);
        $index = 0;
        $defaultOverProductionFlag = PARAMETERS::find('ORDER_OVERPRODUCTION_WARNING_DEFAULT_VALUE');

        foreach ($sequences as $sequence) {
            $workflow = PRWFDATA::where('PRCODE', $sequence->PRCODE)->first();
            $prWcData = PRWCDATA::select('PRWCDATA.*', 'PO.POIDEN', 'PO.PORANK', 'PO.STSTAT', 'PO.POSNFC', 'PO.POSNAU', 'PO.POSNUQ', 'PO.POSNNS', 'PO.PODESC', 'OP.OPNAME', 'PO.POTYPE', 'PO.SGIDEN','PO.PUIDEN')
                ->join('PROPDATA as PO', function ($join) {
                    $join->on('PO.PFIDEN', '=', 'PRWCDATA.PFIDEN')->on('PRWCDATA.OPSHNA', '=', 'PO.OPSHNA');
                })
                ->join('OPERATION as OP', 'PO.OPSHNA', '=', 'OP.OPSHNA')
                ->where('PO.PFIDEN', '=', $workflow->PFIDEN)
                ->orderBy('PO.PORANK')
                ->orderBy('PRWCDATA.WCSHNA')
                ->get();

            $orderCode = DB::connection('custom_mysql')->select('select ORCODE from AWF_SEQUENCE where SEQUID = ' . $sequence->SEQUID);
            
            if (!empty($orderCode[0])) {
                $orderCode = $orderCode[0];
            }

            for ($i = 0, $iMax = count($prWcData); $i < $iMax; $i++) {
                $repno = $orderCode->ORCODE . '-' . $prWcData[$i]->WCSHNA . '-' . $prWcData[$i]->OPSHNA;

                if (!empty(
                    AWF_SEQUENCE_WORKCENTER::where('SEQUID', '=', $sequence->SEQUID)->where('WCSHNA', '=', $prWcData[$i]->WCSHNA)->first()
                )) {
                    $updateSequenceWorkCenterQuery[] = 'update AWF_SEQUENCE_WORKCENTER set RNREPN = "' . $repno . '" where WCSHNA = "' . $prWcData[$i]->WCSHNA . '" and SEQUID =  ' . $sequence->SEQUID . ';';
                }

                $insertRepnoQuery .= sprintf(
                    '("%s","%s","%s","%s","%s","%s",0,0,0,0,"%s",0,"%s","%s","%s","%s","%s","%s","%s",1,1,0,1,0,0,-1,"%s",%s,"%s","%s","%s",%s,%s,%s,%s,%s,"%s","%s",%s,%s,"%s","%s",%s)',
                    $repno,
                    $prWcData[$i]->WCSHNA,
                    $orderCode->ORCODE,
                    $prWcData[$i]->OPSHNA,
                    $prWcData[$i]->PORANK,
                    $prWcData[$i]->OPNAME,
                    $prWcData[$i]->PWMUQY,
                    $prWcData[$i]->PWCYTI,
                    $prWcData[$i]->PWDYPK,
                    $prWcData[$i]->PWHUTI,
                    $prWcData[$i]->PWMAOT,
                    $prWcData[$i]->PWMMIN,
                    $prWcData[$i]->PWMMAX,
                    $prWcData[$i]->PWTOID,
                    $prWcData[$i]->POSNFC,
                    !empty($prWcData[$i]->PRSFCO) ? '"' . $prWcData[$i]->PRSFCO . '"' : 'null',
                    $prWcData[$i]->POSNAU,
                    $prWcData[$i]->POSNUQ,
                    $prWcData[$i]->POSNNS,
                    !empty($prWcData[$i]->STORIN) ? '"' . $prWcData[$i]->STORIN . '"' : 'null',
                    !empty($prWcData[$i]->STORMA) ? '"' . $prWcData[$i]->STORMA . '"' : 'null',
                    !empty($prWcData[$i]->STOROU) ? '"' . $prWcData[$i]->STOROU . '"' : 'null',
                    !empty($prWcData[$i]->STOROS) ? '"' . $prWcData[$i]->STOROS . '"' : 'null',
                    !empty($prWcData[$i]->STOROC) ? '"' . $prWcData[$i]->STOROC . '"' : 'null',
                    $prWcData[$i]->PODESC,
                    $prWcData[$i]->SGIDEN,
                    !empty($prWcData[$i]->WCSITY) ? $prWcData[$i]->WCSITY : 'null',
                    !empty($prWcData[$i]->WCSOTY) ? $prWcData[$i]->WCSOTY : 'null',
                    $prWcData[$i]->STSTAT,
                    $defaultOverProductionFlag->PAVALU ?? 0,
                    !empty($prWcData[$i]->PUIDEN) ? $prWcData[$i]->PUIDEN : 'null',
                );
                
                $data = PRWCSENSOR_DATA::where('PWIDEN', $prWcData[$i]->PWIDEN)->get();

                if (count($data) > 0) {
                    if ($hasSensor === false) {
                        $hasSensor = true;
                    }

                    for ($j = 0, $jMax = count($data); $j < $jMax; $j++) {
                        if (!empty($data[$j])) {
                            $insertSensorQueryData[] = sprintf(
                                '("%s","%s",%s,%s,%s)',
                                $repno,
                                $data[$j]->CVIDEN,
                                !empty($data[$j]->CVMIVA) ? '"' . $data[$j]->CVMIVA . '"' : 'null',
                                !empty($data[$j]->CVMAVA) ? '"' . $data[$j]->CVMAVA . '"' : 'null',
                                !empty($data[$j]->CVSETV) ? '"' . $data[$j]->CVSETV . '"' : 'null'
                            );
                        }
                    }
                }

                $data = PRPR_OPDATA::with('part')->where('POIDEN', $prWcData[$i]->POIDEN)->get();;

                if (count($data) > 0) {
                    if ($hasMaterial === false) {
                        $hasMaterial = true;
                    }

                    for ($j = 0, $jMax = count($data); $j < $jMax; $j++) {
                        if (!empty($data[$j])) {
                            $insertMaterialQueryData[] = sprintf(
                                '("%s","%s","%s")',
                                $repno,
                                $data[$j]->part->SUPRCO,
                                $data[$j]->PPMULT,
                            );
                        }
                    }
                }

                if ($i < $iMax - 1) {
                    $insertRepnoQuery .= ',';
                }
            }

            if ($index < $maxIndex - 1) {
                $insertRepnoQuery .= ',';
            }

            $index++;
        }

        DB::insert($insertRepnoQuery);

        if ($hasSensor) {
            $insertSensorQuery .= implode(',', $insertSensorQueryData);
            DB::insert($insertSensorQuery);
        }

        if ($hasMaterial) {
            $insertMaterialQuery .= implode(',', $insertMaterialQueryData);
            DB::insert($insertMaterialQuery);
        }

        foreach ($updateSequenceWorkCenterQuery as $query) {
            DB::connection('custom_mysql')->update($query);
        }
    }
}
