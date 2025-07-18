<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('saldo_awals', function (Blueprint $table) {
            $table->decimal('saldo', 15, 2)->default(0)->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('saldo_awals', function (Blueprint $table) {
            $table->dropColumn('saldo');
        });
    }
};
