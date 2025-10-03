<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\AnalyticsHelper;

class RecordDailyAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record daily analytics metrics';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Recording daily analytics...');
        
        AnalyticsHelper::recordDailyMetrics();
        
        $this->info('Daily analytics recorded successfully!');
        return 0;
    }
}