<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'title',
        'notes',
        'due_date',
        'priority',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function getIsCompleteAttribute(): bool
    {
        return $this->completed_at !== null;
    }
}