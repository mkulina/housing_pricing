<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prediction extends Model
{
    protected $fillable = [
        'square_footage',
        'bedrooms',
        'predicted_price',
    ];
}
