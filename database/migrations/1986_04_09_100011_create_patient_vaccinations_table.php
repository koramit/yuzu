<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientVaccinationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_vaccinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->timestamp('vaccinated_at')->index();
            $table->unsignedSmallInteger('brand_id')->index();
            $table->string('label');
            $table->unsignedTinyInteger('dose_no')->index();
            $table->string('lot_no', 20);
            $table->string('serial_no', 30);
            $table->timestamp('expired_at')->nullable();
            $table->string('hospital_code', 10);
            $table->string('hospital_name');
            $table->string('hospital_province');
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
        Schema::dropIfExists('patient_vaccinations');
    }
}
