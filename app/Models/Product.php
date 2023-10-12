<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable=[
        'name',
        'price',
        'discount'
    ];

    public function details()
    {
        return $this->hasMany(OrderDetails::class);
    }
}
