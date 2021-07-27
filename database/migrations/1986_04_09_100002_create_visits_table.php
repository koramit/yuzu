<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->uuid('slug');
            $table->date('date_visit')->index();
            $table->unsignedTinyInteger('patient_type')->nullable()->index();
            $table->unsignedTinyInteger('screen_type')->nullable()->index();
            $table->unsignedTinyInteger('status')->default(1)->index();
            $table->json('form');
            $table->foreignId('patient_id')->nullable()->constrained('patients')->onDelete('cascade');
            $table->timestamp('enlisted_screen_at')->nullable()->index();
            $table->timestamp('enqueued_at')->nullable()->index();
            $table->timestamp('enlisted_exam_at')->nullable()->index();
            $table->timestamp('enlisted_swab_at')->nullable()->index();
            $table->timestamp('discharged_at')->nullable()->index();
            $table->timestamp('authorized_at')->nullable()->index();
            $table->timestamp('attached_opd_card_at')->nullable()->index();
            $table->softDeletes();
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
        Schema::dropIfExists('visits');
    }
}
