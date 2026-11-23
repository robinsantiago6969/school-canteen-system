<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Order extends Model {
    protected $fillable = ['student_id','student_name','grade','pickup','status','method','reference','claimed_amount','paid_online','paid_cash','verified','total','proof_path'];
    protected $casts = ['claimed_amount'=>'integer','paid_online'=>'integer','paid_cash'=>'integer','verified'=>'boolean','total'=>'integer'];
    public function items() { return $this->hasMany(OrderItem::class); }
    public function student() { return $this->belongsTo(Student::class, 'student_id', 'student_id'); }
    public function balance() { return max(0, $this->total - $this->paid_online - $this->paid_cash); }
    public function isChecking() { return $this->method === 'gcash' && !$this->verified; }
}
