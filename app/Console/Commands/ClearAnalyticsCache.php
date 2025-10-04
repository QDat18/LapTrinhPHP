<?php
// app/Console/Commands/ClearAnalyticsCache.php
// Tạo bằng: php artisan make:command ClearAnalyticsCache

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearAnalyticsCache extends Command
{
    protected $signature = 'analytics:clear-cache';
    protected $description = 'Clear all analytics related caches';

    public function handle()
    {
        $this->info('Clearing analytics cache...');
        
        Cache::flush();
        
        $this->info('✓ Analytics cache cleared successfully!');
        
        return 0;
    }
}