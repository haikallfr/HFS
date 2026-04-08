<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $fillable = [
        'code',
        'name',
        'region',
        'is_remote',
        'timezone',
    ];

    protected function casts(): array
    {
        return [
            'is_remote' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function workAreas(): HasMany
    {
        return $this->hasMany(WorkArea::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }
}
