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
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('updater_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('submitter_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('approver_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->datetime('submitted_at')->nullable()->index();
            $table->datetime('approved_at')->nullable()->index();
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
