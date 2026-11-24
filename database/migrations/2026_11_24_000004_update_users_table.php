<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['name', 'email_verified_at', 'remember_token']);
            $table->string('fio', 150)->after('id');
            $table->date('birthday')->nullable()->after('fio');
            $table->foreignId('gender_id')->after('birthday')->constrained('genders');
            $table->string('email', 50)->change();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['gender_id']);
            $table->dropColumn(['fio', 'birthday', 'gender_id', 'deleted_at']);
            $table->string('name')->after('id');
            $table->string('email', 255)->change();
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->rememberToken()->after('password');
        });
    }
};
