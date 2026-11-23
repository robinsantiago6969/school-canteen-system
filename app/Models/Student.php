<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
class Student extends Model {
    protected $fillable = ['student_id','name','grade','password'];
    protected $hidden = ['password'];
    public function setPasswordAttribute($value) { $this->attributes['password'] = Hash::make($value); }
    public function orders() { return $this->hasMany(Order::class, 'student_id', 'student_id'); }
}
