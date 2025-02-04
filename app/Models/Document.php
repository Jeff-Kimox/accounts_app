<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model
{
    use HasFactory;

    protected $fillable = ['document_type', 'customer_id', 'total_amount'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(DocumentItem::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($document) {
            $document->total_amount = $document->items->sum('subtotal');
        });
    }

}
