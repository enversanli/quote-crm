<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $guarded = [];

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getDisplayNameAttribute(): string
    {
        $name = $this->full_name;
        if ($this->company_name) {
            $name .= " ({$this->company_name})";
        }
        return $name;
    }
}