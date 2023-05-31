<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //roleカラムを追加する
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')
                ->after('password')
                ->default('user')
                ->nullable(false)
                ->comment('権限');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //指定したテーブルが存在する場合にはテーブルを削除。テーブルが存在しない場合には何もしません。
            Schema::dropIfExists('users');
        });
    }
};
