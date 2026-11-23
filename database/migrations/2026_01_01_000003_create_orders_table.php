<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('orders', function(Blueprint $table){ $table->id(); $table->string('student_id')->index(); $table->string('student_name'); $table->string('grade'); $table->string('pickup'); $table->string('status')->default('Pending'); $table->string('method'); $table->string('reference')->nullable()->unique(); $table->unsignedInteger('claimed_amount')->default(0); $table->unsignedInteger('paid_online')->default(0); $table->unsignedInteger('paid_cash')->default(0); $table->boolean('verified')->default(false); $table->unsignedInteger('total'); $table->string('proof_path')->nullable(); $table->timestamps(); }); }
 public function down(): void { Schema::dropIfExists('orders'); }
};
