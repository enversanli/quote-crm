<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    protected $guarded = [];

    protected $casts = [
        'default_hourly_rate' => 'decimal:2',
        'capacity'            => 'integer',
    ];

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }
}