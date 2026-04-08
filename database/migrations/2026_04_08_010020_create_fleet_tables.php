<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->decimal('standard_fuel_ratio', 8, 2)->default(0);
            $table->decimal('current_hm', 10, 2)->default(0);
            $table->decimal('last_service_hm', 10, 2)->default(0);
            $table->timestamp('last_service_at')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('hour_meter_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('work_area_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('input_date')->index();
            $table->string('shift')->default('day');
            $table->decimal('hm_start', 10, 2);
            $table->decimal('hm_end', 10, 2);
            $table->decimal('fuel_liters', 10, 2)->default(0);
            $table->decimal('calculated_lph', 8, 2)->default(0);
            $table->boolean('is_fuel_flagged')->default(false);
            $table->string('fuel_flag_reason')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamp('photo_taken_at')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('sync_status')->default('server');
            $table->timestamp('synced_at')->nullable();
            $table->string('service_alert_level')->default('normal');
            $table->decimal('service_due_hm', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('service_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->decimal('service_hm', 10, 2);
            $table->date('service_date');
            $table->string('service_type')->default('preventive');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_logs');
        Schema::dropIfExists('hour_meter_logs');
        Schema::dropIfExists('units');
    }
};
