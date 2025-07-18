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
        Schema::table('jurnal_umums', function (Blueprint $table) {
    if (!Schema::hasColumn('jurnal_umums', 'akun')) {
        $table->string('akun')->nullable();
    }
});


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jurnal_umums', function (Blueprint $table) {
            //
        });
    }
};
