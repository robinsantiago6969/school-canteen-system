<?php
namespace Database\Seeders;
use App\Models\Student;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;
class CanteenSeeder extends Seeder {
 public function run(): void {
  $items = [
   ['pastil','Pastil Rice',36,'🍚'], ['sinampalukan','Sinampalukan Rice',80,'🍲'], ['adobo','Adobo Toppings',60,'🍗'],
   ['egg','Boiled Egg',16,'🥚'], ['eggrice','Egg Rice',29,'🍳'], ['hatdog','Hatdog Rice',29,'🌭'], ['water','Water',12,'💧']
  ];
  foreach($items as [$key,$name,$price,$emoji]) MenuItem::updateOrCreate(['item_key'=>$key],['name'=>$name,'price'=>$price,'emoji'=>$emoji,'available'=>true]);
  if (!Student::where('student_id','202611')->exists()) Student::create(['student_id'=>'202611','name'=>'Demo Student','grade'=>'BSIS 1-1','password'=>'demo123']);
 }
}
