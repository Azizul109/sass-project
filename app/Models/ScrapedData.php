<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ScrapedData extends Model
{
    use HasFactory;

    protected $table = 'scraped_data';

    protected $fillable = [
        'company_id',
        'source',
        'title',
        'url',
        'description',
        'price',
        'published_at',
        'metadata',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'published_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function booted()
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('company_id', auth()->user()->company_id);
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}