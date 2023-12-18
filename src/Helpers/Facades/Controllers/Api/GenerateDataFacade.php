<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Helpers\Checkers\SavedData;
use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_LOG;
use AWF\Extension\Models\AWF_SEQUENCE_WORKCENTER;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use App\Models\ORDERHEAD;
use App\Models\PRODUCT;
use Illuminate\Support\Facades\File;

class GenerateDataFacade extends Facade
{
    public function create(Request|null $request = null, Model|string|null $model = null): JsonResponse|null
    {
        try {
            $this->generatePath();
            $this->deleteAllSequenceThatNotInProduction();

            foreach (Storage::disk('awfSequenceFtp')->files() as $filePath) {
                if (str_contains($filePath, 'P992')) {
                    $this->generateData($filePath);
                }
            }
        }
        catch (\Exception $exception) {
            return new JsonResponse(
                ['success' => false, 'message' => __('response.unprocessable_entity')],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        SavedData::check();

        return new JsonResponse(
            ['success' => true, 'message' => ''],
            Response::HTTP_OK
        );
    }

    protected function generateData(string $filePath): void
    {
        $file = Storage::disk('awfSequenceFtp')->get($filePath);

        foreach (explode(PHP_EOL, $file) as $row) {
            $data = explode(';', $row);

            if (!empty($data) && !empty($data[0])) {
                 $start = new \DateTime();
                $year = substr($data[5], 0, 4);
                $month = substr($data[5], 4, 2);
                $day = substr($data[5], 6, 2);
                $expiration = new \DateTime($year . '-' . $month . '-' . $day);

                $sequenceData = AWF_SEQUENCE::create([
                    'SEPONR' => $data[0],
                    'SEPSEQ' => $data[1],
                    'SEARNU' => $data[2],
                    'SEARDE' => mb_convert_encoding($data[3], 'UTF-8'),
                    'SESIDE' => $data[4],
                    'SEEXPI' => $expiration,
                    'SEPILL' => $data[3][6],
                ]);

                AWF_SEQUENCE_LOG::create([
                    'SEQUID' => $sequenceData->SEQUID,
                    'WCSHNA' => 'EL01',
                    'LSTIME' => $start,
                ]);

                AWF_SEQUENCE_WORKCENTER::create([
                    'SEQUID' => $sequenceData->SEQUID,
                    'WCSHNA' => 'EL01',
                ]);
            }
        }

        $savePath = 'sequence-data' . DIRECTORY_SEPARATOR . (new \DateTime())->format('Ymd');
        Storage::put($savePath . DIRECTORY_SEPARATOR . $filePath, $file);
    }

    protected function generatePath(): void
    {
        $path = storage_path('app' . DIRECTORY_SEPARATOR . 'sequence-data');

        if (!is_dir($path)) {
            File::makeDirectory($path);
        }

        $path .= DIRECTORY_SEPARATOR . (new \DateTime())->format('Ymd');

        if (!is_dir($path)) {
            File::makeDirectory($path);
        }
    }

    protected function deleteAllSequenceThatNotInProduction(): void
    {
        $sequences = AWF_SEQUENCE::where('SEINPR', '=', 0)->get();

        foreach ($sequences as $sequence) {
            AWF_SEQUENCE_LOG::where('SEQUID', '=', $sequence->SEQUID)->delete();
            AWF_SEQUENCE_WORKCENTER::where('SEQUID', '=', $sequence->SEQUID)->delete();
        }

        AWF_SEQUENCE::where('SEINPR', '=', 0)->delete();
    }
}
