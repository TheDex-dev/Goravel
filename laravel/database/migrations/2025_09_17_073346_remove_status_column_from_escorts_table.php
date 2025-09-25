<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveStatusColumnFromEscortsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('escorts', function (Blueprint $table) {
            // Check if status column exists before dropping it
            if (Schema::hasColumn('escorts', 'status')) {
                $table->dropColumn('status');
            }
            
            // Add new tracking columns if they don't exist
            if (!Schema::hasColumn('escorts', 'submission_id')) {
                $table->string('submission_id')->nullable();
            }
            if (!Schema::hasColumn('escorts', 'submitted_from_ip')) {
                $table->string('submitted_from_ip')->nullable();
            }
            if (!Schema::hasColumn('escorts', 'api_submission')) {
                $table->boolean('api_submission')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('escorts', function (Blueprint $table) {
            // Restore status column
            $table->enum('status', ['active', 'inactive'])->default('active');
            
            // Remove tracking columns
            if (Schema::hasColumn('escorts', 'submission_id')) {
                $table->dropColumn('submission_id');
            }
            if (Schema::hasColumn('escorts', 'submitted_from_ip')) {
                $table->dropColumn('submitted_from_ip');
            }
            if (Schema::hasColumn('escorts', 'api_submission')) {
                $table->dropColumn('api_submission');
            }
        });
    }
}
