<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_LOG;
use AWF\Extension\Models\AWF_SEQUENCE_WORKCENTER;
use AWF\Extension\Responses\CustomJsonResponse;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use App\Models\PRODUCT;
use Illuminate\Support\Facades\File;

class GenerateDataFacade extends Facade
{
    protected const DATEFORMAT = 'Ymd_H';

    public function create(Request|FormRequest|null $request = null, Model|string|null $model = null): JsonResponse|null
    {
        $success = false;
        $details = [];

        try {
            $this->generatePath();
            $i = 0;
            $deleteSuccess = false;

            while ($deleteSuccess === false && $i < 3) {
                try {
                    $this->deleteAllSequenceThatNotInProduction();

                    $deleteSuccess = true;
                }
                catch (\Exception $e) {
                    $i++;
                }
            }

            foreach (Storage::disk('local')->files('/sequence-data/20240209_13') as $filePath) {
                if (str_contains($filePath, 'P992')) {
                    $this->generateData($filePath);
                }
            }

            $success = true;
        }
        catch (\Exception $exception) {
            $endTime = microtime(true);
            $dataToLog = 'Type: ' . __CLASS__ . "\n";
            $dataToLog .= 'Method: GenerateAwfPorscheData' . "\n";
            $dataToLog .= 'Time: ' . date("Y m d H:i:s") . "\n";
            $dataToLog .= 'Duration: ' . number_format($endTime - LARAVEL_START, 3) . "\n";
            $dataToLog .= 'Output: ' . $exception->getMessage() . "\n";

            Storage::disk('local')->append(
                'logs/awf_generate_porsche_data_' . Carbon::now()->format('Ymd') . '.log',
                $dataToLog . "\n" . str_repeat("=", 20) . "\n\n"
            );

            $details = ['message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()];
        }

//        try {
//            SavedData::check();
//        }
//        catch (\Exception $exception) {
//            $endTime = microtime(true);
//            $dataToLog = 'Type: ' . __CLASS__ . "\n";
//            $dataToLog .= 'Method: SendAwfMail' . "\n";
//            $dataToLog .= 'Time: ' . date("Y m d H:i:s") . "\n";
//            $dataToLog .= 'Duration: ' . number_format($endTime - LARAVEL_START, 3) . "\n";
//            $dataToLog .= 'Output: ' . $exception->getMessage() . "\n";
//
//            Storage::disk('local')->append(
//                'logs/awf_send_porsche_mail_' . Carbon::now()->format('Ymd') . '.log',
//                $dataToLog . "\n" . str_repeat("=", 20) . "\n\n"
//            );
//
//            $message = $exception->getMessage();
//        }

        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                $success,
                [],
                json_encode($details)
            ),
            Response::HTTP_OK
            )
        );
    }

    protected function generateData(string $filePath): void
    {
        $file = Storage::disk('local')->get($filePath);

        $insertQuery = 'insert into AWF_SEQUENCE (SEPONR, SEPSEQ, SEARNU, SEARDE, SESIDE, SEEXPI, SEPILL, PRCODE) values';
        $rows = explode(PHP_EOL, $file);
        $iMax = count($rows);
        $i = 0;
        $pillar = '';

        foreach ($rows as $row) {
            $data = explode(';', $row);

            if (!empty($data) && !empty($data[0])) {

                if (empty($pillar)) {
                    $pillar = $data[3][6];
                }

                $hasProduct = false;

                $expiration = (new \DateTime(
                    substr($data[5], 0, 4) . '-' .
                    substr($data[5], 4, 2) . '-' .
                    substr($data[5], 6, 2)
                ))->format('Y-m-d');

                if (!empty(PRODUCT::where('PRCODE', '=', $data[2])->first())) {
                    $hasProduct = true;
                }

                $insertQuery .= sprintf('("%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s")',
                    $data[0],
                    $data[1],
                    $data[2],
                    mb_convert_encoding($data[3], 'UTF-8'),
                    $data[4],
                    $expiration,
                    $data[3][6],
                    $hasProduct ? $data[2] : null,
                );

                if ($i < $iMax - 2) {
                    $insertQuery .= ',';
                }

                $i++;
            }
        }

        DB::connection('custom_mysql')->insert($insertQuery);

        $sequences = DB::connection('custom_mysql'
        )->select('select SEQUID from AWF_SEQUENCE where SEINPR = 0 and SEPILL = "' . $pillar . '"');

        if (!empty($sequences[0])) {
            $insertLog = 'insert into AWF_SEQUENCE_LOG (SEQUID, WCSHNA) values';
            $insertWorkCenter = 'insert into AWF_SEQUENCE_WORKCENTER (SEQUID, WCSHNA) values';


            for ($i = 0, $iMax = count($sequences); $i < $iMax; $i++) {
                $insertLog .= sprintf('("%s", "%s")', $sequences[$i]->SEQUID, 'EL01');
                $insertWorkCenter .= sprintf('("%s", "%s")', $sequences[$i]->SEQUID, 'EL01');

                if ($i < $iMax - 1) {
                    $insertLog .= ',';
                    $insertWorkCenter .= ',';
                }
            }

            DB::connection('custom_mysql')->insert($insertLog);
            DB::connection('custom_mysql')->insert($insertWorkCenter);
        }

        $savePath = 'sequence-data' . DIRECTORY_SEPARATOR . (new \DateTime())->format(static::DATEFORMAT);
        Storage::put($savePath . DIRECTORY_SEPARATOR . $filePath, $file);
    }

    protected function generatePath(): void
    {
        $path = storage_path('app' . DIRECTORY_SEPARATOR . 'sequence-data');

        if (!is_dir($path)) {
            File::makeDirectory($path);
        }

        $path .= DIRECTORY_SEPARATOR . (new \DateTime())->format(static::DATEFORMAT);

        if (!is_dir($path)) {
            File::makeDirectory($path);
        }
    }

    protected function deleteAllSequenceThatNotInProduction(): void
    {
        $sequences = AWF_SEQUENCE::whereIn('SEINPR', [0, 1])->get();
        $now = (new \DateTime())->format('Y-m-d H:i:s');

        $deleteRepno = '';
        $deleteOrder = '';
        $deleteSeq = '';

        $iMax = count($sequences);
        $i = 0;

        foreach ($sequences as $sequence) {
            if (!empty($sequence->ORCODE)) {
                $deleteOrder .= '"' . $sequence->ORCODE . '"';
                if ($i < $iMax - 1) {
                    $deleteOrder .= ',';
                }
            }

            $sequenceWorkCenter = AWF_SEQUENCE_WORKCENTER::where('SEQUID', '=', $sequence->SEQUID)->first();

            if (!empty($sequenceWorkCenter)) {
                $deleteRepno .= '"' . $sequenceWorkCenter->RNREPN . '"';
                if ($i < $iMax - 1) {
                    $deleteRepno .= ',';
                }
            }

            $deleteSeq .= '"' . $sequence->SEQUID . '"';

            if ($i < $iMax - 1) {
                $deleteSeq .= ',';
            }

            $i++;
        }

        if (!empty($deleteSeq)) {
            DB::connection('custom_mysql')->delete('delete from AWF_SEQUENCE_LOG where SEQUID in (' . $deleteSeq . ')');
            DB::connection('custom_mysql'
            )->delete('delete from AWF_SEQUENCE_WORKCENTER where SEQUID in (' . $deleteSeq . ')');
            DB::connection('custom_mysql')->delete('delete from AWF_SEQUENCE where SEQUID in (' . $deleteSeq . ')');
        }

        if (!empty($deleteRepno)) {
            DB::delete('delete from CACHE_LOG_CYCLE_REPNO where RNREPN in (' . $deleteRepno . ')');
            DB::delete('delete from LOG_CYCLE_REPNO where RNREPN in (' . $deleteRepno . ')');
            DB::delete('delete from REPNO_ACTIVITY_LOG where RNREPN in (' . $deleteRepno . ')');
            DB::delete('delete from OEE_REPNO where RNREPN in (' . $deleteRepno . ')');
        }
        if (!empty($deleteOrder)) {
            DB::delete('delete from CACHE_LOG_ANALOG_REPNO where ORCODE in (' . $deleteOrder . ')');
            DB::delete('delete from LOG_ANALOG_REPNO where ORCODE in (' . $deleteOrder . ')');
            DB::delete('delete from ORDERHEAD where ORCODE in (' . $deleteOrder . ')');
        }

        AWF_SEQUENCE::where('SEINPR', '<', 4)->update(['SEINPR' => 5]);

        AWF_SEQUENCE_LOG::whereNull('LSTIME')->update([
            'LSTIME' => $now,
        ]);

        AWF_SEQUENCE_LOG::whereNull('LETIME')->update([
            'LETIME' => $now,
        ]);
    }
}
