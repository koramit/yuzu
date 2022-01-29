<?php

use App\Models\NotificationEvent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_events', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('name')->unique();
            $table->string('label')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_event_user', function (Blueprint $table) {
            $table->primary(['user_id', 'notification_event_id']);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedSmallInteger('notification_event_id')->constrained('notification_events')->onDelete('cascade');
            $table->timestamps();
        });

        $timestamps = ['created_at' => now(), 'updated_at' => now()];

        NotificationEvent::insert([
            ['name' => 'read'] + $timestamps,
            ['name' => 'reply'] + $timestamps,
            ['name' => 'push'] + $timestamps,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_event_user');
        Schema::dropIfExists('notification_events');
    }
}
