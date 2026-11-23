<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CanteenController;
Route::get('/',[CanteenController::class,'home']);
Route::post('/student/register',[CanteenController::class,'studentRegister']);
Route::post('/student/login',[CanteenController::class,'studentLogin']);
Route::post('/student/logout',[CanteenController::class,'studentLogout']);
Route::get('/menu',[CanteenController::class,'menu']);
Route::get('/session',[CanteenController::class,'sessionState']);
Route::get('/student/orders',[CanteenController::class,'myOrders']);
Route::post('/orders',[CanteenController::class,'placeOrder']);
Route::post('/staff/login',[CanteenController::class,'staffLogin']);
Route::post('/staff/logout',[CanteenController::class,'staffLogout']);
Route::get('/staff/orders',[CanteenController::class,'staffOrders']);
Route::post('/staff/orders/{order}',[CanteenController::class,'staffUpdate']);
Route::post('/staff/menu/{menuItem}',[CanteenController::class,'toggleMenu']);
Route::get('/staff/proof/{order}',[CanteenController::class,'proof'])->name('staff.proof');
