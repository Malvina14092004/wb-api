<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'payload'
    ];

    protected $hidden = [
        'updated_at',
        'created_at',
        'id'
    ];
}
