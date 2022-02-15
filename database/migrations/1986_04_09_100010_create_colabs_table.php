<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateColabsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('colabs', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary(); // lab_no
            $table->foreignId('visit_id')->constrained('visits')->onDelete('cascade');
            $table->string('hn');
            $table->string('specimen')->nullable();
            $table->string('result')->index();
            $table->string('remark')->nullable()->index();
            $table->string('reporter')->nullable();
            $table->string('approver')->nullable();
            $table->timestamp('received_at')->nullable()->index();
            $table->timestamp('approved_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('colabs');
    }
}
