<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('sex')->nullable()->after('birth_date');
            $table->string('staff_level')->nullable()->after('profession');
            $table->date('hired_on')->nullable()->after('grade');
            $table->string('email')->nullable()->unique()->after('hired_on');
            $table->string('marital_status')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropColumn(['sex', 'staff_level', 'hired_on', 'email']);
            $table->string('marital_status')->nullable(false)->change();
        });
    }
};
