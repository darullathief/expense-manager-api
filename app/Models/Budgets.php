<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budgets extends Model
{
    protected $fillable = [
        'user_id', 
        'category_id',
        'amount', 
        'date',
        'description,'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
