<?php
namespace App\Http\Controllers;
use App\Models\Student;
use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class CanteenController extends Controller {
 public function home(){ return view('canteen'); }
 public function sessionState(Request $r){ $s=$r->session()->get('student_id'); $student=$s?Student::where('student_id',$s)->first():null; return response()->json(['student'=>$student?['id'=>$student->student_id,'name'=>$student->name,'grade'=>$student->grade]:null,'staff'=>!!$r->session()->get('staff_ok')]); }
 public function studentRegister(Request $r){
  $data=$r->validate(['name'=>'required|string|max:120','student_id'=>'required|string|max:40','grade'=>'required|string|max:60','password'=>'required|string|min:6|confirmed']);
  if(Student::where('student_id',$data['student_id'])->exists()) return response()->json(['message'=>'That Student ID already has an account. Try logging in.'],422);
  $s=Student::create($data); $r->session()->regenerate(); $r->session()->put('student_id',$s->student_id);
  return response()->json(['student'=>['id'=>$s->student_id,'name'=>$s->name,'grade'=>$s->grade]]);
 }
 public function studentLogin(Request $r){ $data=$r->validate(['student_id'=>'required|string','password'=>'required|string']); $s=Student::where('student_id',$data['student_id'])->first();
  if(!$s || !Hash::check($data['password'],$s->password)) return response()->json(['message'=>'Incorrect Student ID or password.'],422);
  $r->session()->regenerate(); $r->session()->put('student_id',$s->student_id); return response()->json(['student'=>['id'=>$s->student_id,'name'=>$s->name,'grade'=>$s->grade]]);
 }
 public function studentLogout(Request $r){ $r->session()->forget('student_id'); $r->session()->regenerateToken(); return response()->json(['ok'=>true]); }
 public function staffLogin(Request $r){ $r->validate(['pin'=>'required|string']); if(!hash_equals((string)env('CANTEEN_STAFF_PIN','staff123'),$r->pin)) return response()->json(['message'=>'Incorrect staff PIN.'],422); $r->session()->regenerate(); $r->session()->put('staff_ok',true); return response()->json(['ok'=>true]); }
 public function staffLogout(Request $r){ $r->session()->forget('staff_ok'); $r->session()->regenerateToken(); return response()->json(['ok'=>true]); }
 public function menu(){ return response()->json(MenuItem::orderBy('id')->get()); }
 public function myOrders(Request $r){ $sid=$r->session()->get('student_id'); if(!$sid) return response()->json(['message'=>'Please log in.'],401); return response()->json(Order::with('items')->where('student_id',$sid)->latest()->get()->map(fn($o)=>$this->formatOrder($o))); }
 public function placeOrder(Request $r){
  $sid=$r->session()->get('student_id'); if(!$sid) return response()->json(['message'=>'Please log in.'],401);
  $data=$r->validate(['items'=>'required|array|min:1','items.*.key'=>'required|string','items.*.quantity'=>'required|integer|min:1|max:50','pickup'=>'required|string|max:80','method'=>'required|in:gcash,cash','reference'=>'nullable|string|regex:/^[0-9]{10,15}$/','claimed_amount'=>'nullable|integer|min:1','proof'=>'required_if:method,gcash|nullable|image|mimes:jpg,jpeg,png,webp|max:2048']);
  $student=Student::where('student_id',$sid)->firstOrFail();
  $order=DB::transaction(function() use($data,$student){
   $menu=MenuItem::whereIn('item_key',array_column($data['items'],'key'))->get()->keyBy('item_key'); $total=0; $clean=[];
   foreach($data['items'] as $entry){ $m=$menu->get($entry['key']); if(!$m || !$m->available) throw ValidationException::withMessages(['items'=>'One or more selected foods are unavailable. Refresh the menu.']); $total+=$m->price*$entry['quantity']; $clean[]=['menu_item_key'=>$m->item_key,'name'=>$m->name,'quantity'=>$entry['quantity'],'unit_price'=>$m->price]; }
   if($data['method']==='gcash' && empty($data['reference'])) throw ValidationException::withMessages(['reference'=>'Enter the GCash reference number.']);
   if($data['method']==='gcash' && Order::where('reference',$data['reference'])->exists()) throw ValidationException::withMessages(['reference'=>'That reference number has already been used.']);
   $claimed=$data['method']==='gcash' ? min((int)$data['claimed_amount'],$total) : 0;
   $order=Order::create(['student_id'=>$student->student_id,'student_name'=>$student->name,'grade'=>$student->grade,'pickup'=>$data['pickup'],'status'=>'Pending','method'=>$data['method'],'reference'=>$data['method']==='gcash'?$data['reference']:null,'claimed_amount'=>$claimed,'paid_online'=>0,'paid_cash'=>0,'verified'=>$data['method']==='cash','total'=>$total]);
   $order->items()->createMany($clean); return $order;
  });
  if($data['method']==='gcash' && $r->hasFile('proof')){ $path=$r->file('proof')->store('payment-proofs','local'); $order->update(['proof_path'=>$path]); }
  return response()->json(['order'=>$this->formatOrder($order->fresh('items')),'message'=>'Order submitted successfully.']);
 }
 public function staffOrders(){ $orders=Order::with('items')->latest()->get(); return response()->json($orders->map(fn($o)=>$this->formatOrder($o,true))); }
 public function staffUpdate(Request $r, Order $order){ $data=$r->validate(['action'=>'required|in:advance,confirm_gcash,collect_cash','amount'=>'nullable|integer|min:0']);
  if($data['action']==='confirm_gcash'){ if(!$order->isChecking()) return response()->json(['message'=>'This payment is already checked.'],422); $amount=(int)($data['amount']??0); if($amount>$order->total) $amount=$order->total; $order->paid_online=$amount; $order->verified=true; $order->save(); }
  elseif($data['action']==='collect_cash'){ if($order->isChecking()) return response()->json(['message'=>'Verify GCash first.'],422); $order->paid_cash=min($order->total-$order->paid_online-$order->paid_cash, $order->total); $order->paid_cash=max(0,$order->paid_cash); $order->save(); }
  else { $next=['Pending'=>'Preparing','Preparing'=>'Ready','Ready'=>'Picked up']; if(!isset($next[$order->status])) return response()->json(['message'=>'Order is already picked up.'],422); if($next[$order->status]==='Picked up' && $order->balance()>0) return response()->json(['message'=>'Collect payment before marking picked up.'],422); $order->status=$next[$order->status]; $order->save(); }
  return response()->json(['order'=>$this->formatOrder($order->fresh('items'),true)]);
 }
 public function toggleMenu(Request $r, MenuItem $menuItem){ $data=$r->validate(['available'=>'required|boolean']); $menuItem->available=$data['available']; $menuItem->save(); return response()->json(['ok'=>true]); }
 public function proof(Request $r, Order $order){ if(!$r->session()->get('staff_ok')) abort(403); if(!$order->proof_path || !Storage::disk('local')->exists($order->proof_path)) abort(404); return Storage::disk('local')->response($order->proof_path); }
 private function formatOrder(Order $o,$staff=false){ $received=$o->paid_online+$o->paid_cash; $out=['id'=>$o->id,'student_id'=>$o->student_id,'student_name'=>$o->student_name,'grade'=>$o->grade,'pickup'=>$o->pickup,'status'=>$o->status,'method'=>$o->method,'reference'=>$o->reference,'claimed_amount'=>$o->claimed_amount,'paid_online'=>$o->paid_online,'paid_cash'=>$o->paid_cash,'verified'=>$o->verified,'total'=>$o->total,'balance'=>max(0,$o->total-$received),'checking'=>$o->isChecking(),'created_at'=>$o->created_at?->toIso8601String(),'items'=>$o->items->map(fn($i)=>['name'=>$i->name,'quantity'=>$i->quantity,'unit_price'=>$i->unit_price,'subtotal'=>$i->quantity*$i->unit_price])]; if($staff) $out['proof_url']=$o->proof_path?route('staff.proof',$o):null; return $out; }
}
