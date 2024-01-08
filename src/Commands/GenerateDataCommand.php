<?php

namespace AWF\Extension\Commands;

use AWF\Extension\Helpers\Facades\Controllers\Api\GenerateDataFacade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'awf:generate-data';

    /**
     * The console command description.
     *
     * @var string|null
     */
    protected $description = 'Generate default orders data';

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
     * @return mixed
     */
    public function handle(): mixed
    {
        try {
            (new GenerateDataFacade())->create();

            return true;
        }
        catch (\Exception $exception) {
            $endTime = microtime(true);
            $dataToLog = 'Type: ' . $this->description . "\n";
            $dataToLog .= 'Time: ' . date("Y m d H:i:s") . "\n";
            $dataToLog .= 'Duration: ' . number_format($endTime - LARAVEL_START, 3) . "\n";
            $dataToLog .= 'Output: ' . $exception->getMessage() . "\n";

            Storage::disk('local')->append('logs/redis_' . Carbon::now()->format('Ymd') . '.log', $dataToLog . "\n" . str_repeat("=", 20) . "\n\n");
        }

        return false;
    }
}
