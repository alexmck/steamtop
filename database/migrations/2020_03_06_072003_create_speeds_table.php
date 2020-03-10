<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpeedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('speeds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asn_id');
            $table->unsignedDecimal('average_speed', 16, 12);
            $table->unsignedDecimal('data', 16, 5)->nullable();
            $table->timestamps();

            $table->index(['asn_id', 'average_speed', 'data']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('speeds');
    }
}
