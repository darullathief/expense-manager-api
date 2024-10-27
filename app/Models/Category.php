<?php

namespace App\Models;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $table = 'category';

    protected $fillable = ['name'];
    public $timestamps = false;

    public function transaction(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

}
