<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_LOG;
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
            $path = storage_path('sequence-data') . DIRECTORY_SEPARATOR . (new \DateTime())->format('Ymd');
            File::makeDirectory($path);

            foreach (Storage::disk('awfSequenceFtp')->files() as $filePath) {
                if (str_contains($filePath, 'P992')) {
                    $this->generateData($path, $filePath);
                }
            }
        }
        catch (\Exception $exception) {
            return new JsonResponse(
                ['success' => false, 'message' => __('response.unprocessable_entity')],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }



        return new JsonResponse(
            ['success' => true, 'message' => ''],
            Response::HTTP_OK
        );
    }

    protected function generateData(string $path, string $filePath): void
    {
        $file = Storage::disk('awfSequenceFtp')->get($filePath);

        foreach ($file as $data) {
            $i = 1;

            $start = new \DateTime();
            $year = substr($data[5], 0, 4);
            $month = substr($data[5], 4, 2);
            $day = substr($data[5], 6, 2);
            $expiration = new \DateTime($year . '-' . $month . '-' . $day);
            $prcode = $data[1] . '_' . $data[2];
            $orcode = $prcode . '_' . substr($year, -2) . '_' . $month;

            if (!empty(PRODUCT::where('PRCODE', 'like', $prcode . '%')->first())) {
                $i++;
            }

            $prcode .=  '_' . $i;

            PRODUCT::create([
                'PRCODE' => $prcode,
                'PRNAME' => mb_convert_encoding($data[3], 'UTF-8'),
                'PRSHNA' => $data[1] . '_' . $data[2],
                'PRACTV' => 1,
                'PRSNEN' => 1,
            ]);

            ORDERHEAD::create([
                'ORCODE' => $orcode,
                'PRCODE' => $prcode,
                'ORQUAN' => 1,
                'ORSTAT' => 0,
                'PFIDEN' => null,
                'ORAACT' => 1,
                'ORSCTY' => 2,
                'ORSCVA' => 1,
                'ORNOSC' =>100 ,
                'ORNOID' => 0,
            ]);

            $sequenceData = AWF_SEQUENCE::create([
                'SEPONR' => $data[0],
                'SEPSEQ' => $data[1],
                'SEARNU' => $data[2],
                'SEARDE' => mb_convert_encoding($data[3], 'UTF-8'),
                'SESIDE' => $data[4],
                'SEEXPI' => $expiration,
                'SEPILL' => 'A',
                'PRCODE' => $prcode,
                'ORCODE' => $orcode,
            ]);

            AWF_SEQUENCE_LOG::craete([
                'SEQUID' => $sequenceData->id,
                'WCSHNA' => null,
                'LSTIME' => $start,
                'LETIME' => new \DateTime(),
            ]);
        }

        Storage::put($path . DIRECTORY_SEPARATOR . $file, $file);
    }
}
