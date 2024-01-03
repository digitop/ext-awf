<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use App\Http\Controllers\production\order\OrderController;
use App\Http\Requests\production\order\InsertRequest;
use App\Models\PARAMETERS;
use App\Models\PRWFDATA;
use AWF\Extension\Helpers\Checkers\SavedData;
use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_LOG;
use AWF\Extension\Models\AWF_SEQUENCE_WORKCENTER;
use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;
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

                $this->makeOrder($sequenceData);
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

    protected function makeOrder(Model $sequenceData): void
    {
        $workflow = PRWFDATA::where('PRCODE', $sequenceData->PRCODE)->first();

        $orderController = new OrderController;
        $orderDefaultNotification = PARAMETERS::find('ORDER_DEFAULT_NOTIFICATION');
        $orderNotificationEnabled = PARAMETERS::find('ORDER_NOTIFICATION_ENABLED');

        $orderRequest = new InsertRequest([
            "PRCODE" => $sequenceData->PRCODE, //TERMÉK KÓD
            "PRORCO" => null, //null mindig
            "ORCODE" => $sequenceData->SEPONR . '_' . $sequenceData->SEPSEQ . '_' . $sequenceData->SEARNU, //Megrendelés kód
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
        ]);

        $orderController->store($orderRequest);
    }
}
