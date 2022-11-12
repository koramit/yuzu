<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHnVirtualNameVirtualColumnsToVisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->unsignedInteger('hn_virtual')
                ->virtualAs('CASE WHEN json_unquote(json_extract(form, \'$."patient"."hn"\')) = "null" THEN null ELSE json_unquote(json_extract(form, \'$."patient"."hn"\')) END' )
                ->nullable()
                ->after('asymptomatic_stored')
                ->index();
            $table->string('name_virtual', 60)
            ->virtualAs('CASE WHEN json_unquote(json_extract(form, \'$."patient"."name"\')) = "null" THEN null ELSE json_unquote(json_extract(form, \'$."patient"."name"\')) END' )
                ->after('hn_virtual')
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
            //
        });
    }
}
