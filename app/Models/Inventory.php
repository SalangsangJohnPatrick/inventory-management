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

        // Calculate inventory and sales values when creating the record
        static::creating(function ($inventory) {
            $inventory->inventory_value = $inventory->price * $inventory->quantity_on_hand;
            $inventory->sales_value = $inventory->price * $inventory->products_sold;
        });

        // Calculate sales value when updating the record
        static::updating(function ($inventory) {
            if ($inventory->isDirty('products_sold') || $inventory->isDirty('price')) {
                // Recalculate the sales value when products_sold or price is updated
                $inventory->sales_value = $inventory->price * $inventory->products_sold;
            } else if ($inventory->isDirty('quantity_on_hand')) {
                // Recalculate the inventory value when quantity_on_hand is updated
                $inventory->inventory_value = $inventory->price * $inventory->quantity_on_hand;
            }
        });
    }
}

