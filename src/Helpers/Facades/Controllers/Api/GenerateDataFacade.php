<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Helpers\Checkers\SavedData;
use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_LOG;
use AWF\Extension\Models\AWF_SEQUENCE_WORKCENTER;
use App\Models\ORDERHEAD;
use AWF\Extension\Responses\CustomJsonResponse;
use AWF\Extension\Responses\SequenceFacadeResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use App\Models\PRODUCT;
use Illuminate\Support\Facades\File;

class GenerateDataFacade extends Facade
{
    public function create(Request|FormRequest|null $request = null, Model|string|null $model = null): JsonResponse|null
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
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    true,
                    [],
                    __('response.unprocessable_entity')
                ),
                Response::HTTP_OK
            ));
        }

        try {
            SavedData::check();
        }
        catch (\Exception $exception) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    true,
                    [],
                    __('response.email_error')
                ),
                Response::HTTP_OK
            ));
        }

        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                true
            ),
            Response::HTTP_OK
        ));
    }

    protected function generateData(string $filePath): void
    {
        $file = Storage::disk('awfSequenceFtp')->get($filePath);

        $sequences = new Collection();

        foreach (explode(PHP_EOL, $file) as $row) {
            $data = explode(';', $row);

            if (!empty($data) && !empty($data[0])) {
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
                ]);

                AWF_SEQUENCE_WORKCENTER::create([
                    'SEQUID' => $sequenceData->SEQUID,
                    'WCSHNA' => 'EL01',
                ]);

                if (!empty(PRODUCT::where('PRCODE', '=', $sequenceData->SEARNU)->first())) {
                    $sequenceData->update([
                        'PRCODE' => $sequenceData->SEARNU,
                    ]);
                }

                $sequences->add($sequenceData);
            }
        }

        $savePath = 'sequence-data' . DIRECTORY_SEPARATOR . (new \DateTime())->format('Ymd_His');
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
            AWF_SEQUENCE_LOG::where('SEQUID', '=', $sequence->SEQUID)?->delete();
            AWF_SEQUENCE_WORKCENTER::where('SEQUID', '=', $sequence->SEQUID)->delete();

            $orderCode = $sequence->ORCODE;
            $sequence->delete();

            ORDERHEAD::where('ORCODE', '=', $orderCode)->delete();
        }
    }
}
