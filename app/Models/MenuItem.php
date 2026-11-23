<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MenuItem extends Model {
    protected $fillable = ['item_key','name','price','emoji','available'];
    protected $casts = ['price'=>'integer','available'=>'boolean'];
}
