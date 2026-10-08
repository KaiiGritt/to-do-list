<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRequest extends Model
{
    protected $table = 'requests';

    protected $fillable = [
        'requester_name',
        'requester_email',
        'item_name',
        'quantity',
        'purpose',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
