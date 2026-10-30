<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('reader')->after('email')->index();
        });

        // Whoever already owns books is the site author; everyone else reads.
        $authorIds = DB::table('books')->distinct()->pluck('user_id');

        if ($authorIds->isNotEmpty()) {
            DB::table('users')->whereIn('id', $authorIds)->update(['role' => 'author']);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
