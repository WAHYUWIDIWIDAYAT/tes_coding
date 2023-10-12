<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice',
        'customer_name',
        'total'
    ];

    public function details()
    {
        return $this->hasMany(OrderDetails::class);
    }
}
