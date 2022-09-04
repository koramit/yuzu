<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLabResultStoredColumnToVisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->string('lab_result_stored')
                ->storedAs('json_unquote(json_extract(form, \'$."management"."np_swab_result"\'))')
                ->nullable()
                ->after('swabbed')
                ->index();
            $table->string('asymptomatic_stored')
                ->storedAs('json_unquote(json_extract(`form`, \'$."symptoms"."asymptomatic_symptom"\'))')
                ->nullable()
                ->after('lab_result_stored')
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn('lab_result_stored');
            $table->dropColumn('asymptomatic_stored');
        });
    }
}
