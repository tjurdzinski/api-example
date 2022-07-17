<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservation_vacancy', function (Blueprint $table) {
            $table->uuid('reservation_id');
            $table->uuid('vacancy_id');
            $table->timestamps();

            $table->foreign('reservation_id')
                ->references('id')->on('reservations')->onDelete('cascade');
            $table->foreign('vacancy_id')
                ->references('id')->on('vacancies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reservation_vacancy');
    }
};
