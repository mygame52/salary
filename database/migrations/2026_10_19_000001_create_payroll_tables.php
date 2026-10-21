<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('payroll_batches', function (Blueprint $table) {
            $table->id();
            $table->string('employee_type', 50); // ข้าราชการ, พนักงานราชการ
            $table->unsignedTinyInteger('month'); // 1..12
            $table->string('month_name', 50);
            $table->unsignedSmallInteger('year_be'); // พ.ศ. เช่น 2568, 2569
            $table->string('period_text', 150); // เช่น 01 - 31 ธันวาคม 2568
            $table->string('pay_date_text', 150); // เช่น 25 ธันวาคม 2568
            $table->string('source_filename')->nullable();
            $table->unsignedInteger('total_employees')->default(0);
            $table->decimal('total_income', 15, 2)->default(0);
            $table->decimal('total_deduction', 15, 2)->default(0);
            $table->decimal('total_net_pay', 15, 2)->default(0);
            $table->boolean('is_published')->default(true);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_batch_id')->constrained('payroll_batches')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('seq_no')->nullable();
            $table->string('position_number', 50)->nullable();
            $table->string('full_name');
            $table->string('employee_type', 50); // ข้าราชการ, พนักงานราชการ
            $table->string('position', 150)->nullable();
            $table->string('level_or_district', 150)->nullable();
            $table->string('citizen_id', 20)->nullable();
            $table->string('bank_account', 50)->nullable();
            $table->string('bank_name', 100)->default('ธ.กรุงไทย');
            $table->json('income_items')->nullable();
            $table->json('deduction_items')->nullable();
            $table->decimal('total_income', 15, 2)->default(0);
            $table->decimal('total_deduction', 15, 2)->default(0);
            $table->decimal('net_pay', 15, 2)->default(0);
            $table->text('remark')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('payroll_batches');
        Schema::dropIfExists('settings');
    }
};
