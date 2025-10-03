<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesForAdmin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add additional indexes for admin queries
        Schema::table('users', function (Blueprint $table) {
            $table->index(['user_type', 'is_verified', 'is_active']);
            $table->index(['created_at']);
            $table->index(['last_login_at']);
        });

        Schema::table('volunteer_activities', function (Blueprint $table) {
            $table->index(['status', 'activity_date']);
            $table->index(['verified_date']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->index(['is_approved', 'created_at']);
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->index(['status', 'applied_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['user_type', 'is_verified', 'is_active']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['last_login_at']);
        });

        Schema::table('volunteer_activities', function (Blueprint $table) {
            $table->dropIndex(['status', 'activity_date']);
            $table->dropIndex(['verified_date']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['is_approved', 'created_at']);
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndex(['status', 'applied_date']);
        });
    }
}