<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pen_name')->nullable()->after('name');
            $table->text('bio')->nullable()->after('pen_name');
            $table->string('avatar_path')->nullable();
            $table->string('website')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['pen_name', 'bio', 'avatar_path', 'website']);
        });
    }
};
