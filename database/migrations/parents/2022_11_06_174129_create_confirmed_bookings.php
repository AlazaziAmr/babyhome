<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfirmedBookings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('confirmed_bookings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('nursery_id')->constrained('nursery_accounts')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('booking_id')->constrained('bookings')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('payment_method_id')->constrained('payment_methods')->onUpdate('cascade')->onDelete('cascade');
            $table->date('confirm_date');
            $table->double('total_payment');
            $table->double('price_per_hour');
            $table->double('total_services_price');
            $table->bigInteger('created_by');
            $table->tinyInteger('status');
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
        Schema::table('confirmed_bookings', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
