<?php

namespace AWF\Extension\Commands;

use AWF\Extension\Events\AllWorkCenterIsAliveEvent;
use AWF\Extension\Helpers\Facades\Controllers\Web\ShiftManagement\ShiftManagementShiftStartPanelFacade;
use Illuminate\Console\Command;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;
use App\Models\WORKCENTER;

class MqttListenerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'awf:mqtt-listener';

    /**
     * The console command description.
     *
     * @var string|null
     */
    protected $description = 'Listen mqtt messages';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $mqtt = new MqttClient(env('MQTT_HOST'));
        $mqtt->connect(
            (new ConnectionSettings())
                ->setConnectTimeout(10)
                ->setUsername(env('MQTT_AUTH_USERNAME'))
                ->setPassword(env('MQTT_AUTH_PASSWORD'))
                ->setUseTls(false)
                ->setKeepAliveInterval(60)
        );

        $count = count(WORKCENTER::where('WCCDPA', '=', 1)->get());
        $workCenters = [];

        $mqtt->subscribe('oeem/keepalive/+', function ($topic, $message) use ($count, &$workCenters) {
            $data = [
                'alive' => true,
                'workCenter' => substr($topic, strripos($topic, 'wc:') + 3),
            ];

            printf("Received message on topic [%s]: %s\n", $topic, $message);

            $message = json_decode($message);

            if (
                $message->ping == false ||
                $message->readStatus == false ||
                $message->writeStatus == false ||
                $message->oeemReady == false
            ) {
                $data['alive'] = false;
            }

            if (!in_array($data['workCenter'], $workCenters, true)) {
                $workCenters[] = $data['workCenter'];
            }

            if ($count <= count($workCenters) || $data['alive'] === false) {
                (new ShiftManagementShiftStartPanelFacade())->startOfShift($data);

                $workCenters = [];
            }
        }, 0);

        $mqtt->loop();
    }
}
