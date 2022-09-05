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
                ->storedAs('CASE WHEN json_unquote(json_extract(form, \'$."management"."np_swab_result"\')) = "null" THEN null ELSE json_unquote(json_extract(form, \'$."management"."np_swab_result"\')) END' )
                ->nullable()
                ->after('swabbed')
                ->index();
            $table->boolean('asymptomatic_stored')
                ->storedAs('CASE WHEN json_extract(`form`, \'$."symptoms"."asymptomatic_symptom"\') = "true" THEN 1 ELSE 0 END')
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
