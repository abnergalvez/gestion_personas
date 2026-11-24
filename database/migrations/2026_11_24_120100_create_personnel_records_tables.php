<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('internal_code', 30)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permit_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('contract_document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('training_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('duration', 80);
            $table->date('certified_on');
            $table->date('valid_until')->nullable();
            $table->string('document_path')->nullable();
            $table->timestamps();
        });

        Schema::create('medical_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->restrictOnDelete();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->unsignedSmallInteger('days_count');
            $table->string('document_path')->nullable();
            $table->string('medical_resolution_path')->nullable();
            $table->string('internal_resolution_path')->nullable();
            $table->timestamps();
            $table->index('starts_on');
            $table->index('ends_on');
        });

        Schema::create('family_dependents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->string('rut', 12);
            $table->date('birth_date');
            $table->string('education_level');
            $table->string('relationship');
            $table->string('birth_certificate_path')->nullable();
            $table->string('resolution_path')->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'rut']);
        });

        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('quality');
            $table->string('modality');
            $table->string('grade', 40);
            $table->string('position');
            $table->date('starts_on');
            $table->date('ends_on')->nullable()->index();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('contract_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_document_type_id')->constrained()->restrictOnDelete();
            $table->string('document_path');
            $table->timestamps();
        });

        Schema::create('permits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permit_type_id')->constrained()->restrictOnDelete();
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at');
            $table->string('document_path')->nullable();
            $table->timestamps();
        });

        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('period_start_year');
            $table->unsignedSmallInteger('period_end_year');
            $table->string('process');
            $table->decimal('score', 4, 2)->nullable();
            $table->text('notes')->nullable();
            $table->string('document_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
        Schema::dropIfExists('permits');
        Schema::dropIfExists('contract_documents');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('family_dependents');
        Schema::dropIfExists('medical_leaves');
        Schema::dropIfExists('training_courses');
        Schema::dropIfExists('contract_document_types');
        Schema::dropIfExists('permit_types');
        Schema::dropIfExists('leave_types');
    }
};
