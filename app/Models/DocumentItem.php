<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentItem extends Model
{
    use HasFactory;

    protected $fillable = ['document_id', 'item_name', 'quantity', 'price', 'subtotal'];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->subtotal = $item->quantity * $item->price;
        });
    }

}
