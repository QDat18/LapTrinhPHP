<?php
// app/Traits/ClearAnalyticsCache.php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait ClearAnalyticsCache
{
    /**
     * Clear analytics cache after creating/updating/deleting
     */
    protected static function bootClearAnalyticsCache()
    {
        static::created(function () {
            self::clearCache();
        });

        static::updated(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });
    }

    /**
     * Clear all analytics related caches
     */
    public static function clearCache()
    {
        $keys = [
            'admin_analytics_7days',
            'admin_analytics_30days',
            'admin_analytics_90days',
            'admin_analytics_year',
            'chart_data_7days',
            'chart_data_30days',
            'chart_data_90days',
            'chart_data_year',
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
        
        // Clear impact reports
        Cache::flush(); // Or use tags if available
    }
}