<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemAnalytics extends Model
{
    use HasFactory;

    protected $primaryKey = 'analytics_id';

    public $timestamps = false;

    protected $fillable = [
        'metric_name',
        'metric_value',
        'record_date',
        'category',
        'metadata',
    ];

    protected $casts = [
        'metric_value' => 'integer',
        'record_date' => 'date',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    // Scopes
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByMetric($query, string $metricName)
    {
        return $query->where('metric_name', $metricName);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('record_date', [$startDate, $endDate]);
    }

    public function scopeLastMonth($query)
    {
        return $query->whereBetween('record_date', [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth()
        ]);
    }

    // Methods
    public function updateValue(int $newValue): void
    {
        $this->update(['metric_value' => $newValue]);
    }

    public function addMetadata(string $key, $value): void
    {
        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        $this->update(['metadata' => $metadata]);
    }

    public function getMetadata(string $key)
    {
        return $this->metadata[$key] ?? null;
    }
}
