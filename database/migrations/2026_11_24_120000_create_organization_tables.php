<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('rut', 12)->unique();
            $table->string('first_name');
            $table->string('paternal_last_name');
            $table->string('maternal_last_name');
            $table->date('birth_date');
            $table->string('profession')->nullable();
            $table->string('marital_status');
            $table->string('grade', 40)->nullable();
            $table->string('employment_quality');
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->foreignId('head_employee_id')->nullable()->after('name')->constrained('employees')->nullOnDelete();
            $table->foreignId('deputy_employee_id')->nullable()->after('head_employee_id')->constrained('employees')->nullOnDelete();
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->foreignId('head_employee_id')->nullable()->after('department_id')->constrained('employees')->nullOnDelete();
            $table->foreignId('deputy_employee_id')->nullable()->after('head_employee_id')->constrained('employees')->nullOnDelete();
        });

        Schema::create('department_analysts', function (Blueprint $table) {
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['department_id', 'employee_id']);
        });

        Schema::create('section_analysts', function (Blueprint $table) {
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['section_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_analysts');
        Schema::dropIfExists('department_analysts');
        Schema::table('sections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('deputy_employee_id');
            $table->dropConstrainedForeignId('head_employee_id');
        });
        Schema::table('departments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('deputy_employee_id');
            $table->dropConstrainedForeignId('head_employee_id');
        });
        Schema::dropIfExists('employees');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('departments');
    }
};
