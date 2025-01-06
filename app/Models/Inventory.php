<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "inventory";

    protected $fillable = [
        'brand_name',
        'type',
        'quantity_on_hand',
        'price',
        'products_sold',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($inventory) {
            $inventory->inventory_value = $inventory->price * $inventory->quantity_on_hand;
            $inventory->sales_value = $inventory->price * $inventory->products_sold;
        });
    }
}
