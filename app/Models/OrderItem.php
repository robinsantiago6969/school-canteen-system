<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OrderItem extends Model {
    protected $fillable = ['order_id','menu_item_key','name','quantity','unit_price'];
    protected $casts = ['quantity'=>'integer','unit_price'=>'integer'];
    public function order() { return $this->belongsTo(Order::class); }
}
