<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mock_test_sessions', function (Blueprint $table) {
            $table->datetime('started_at')->nullable()->after('scheduled_time');
            $table->datetime('ended_at')->nullable()->after('started_at');
            $table->string('recording_filename')->nullable()->after('recording_url');
            $table->bigInteger('recording_size')->nullable()->after('recording_filename');
            $table->integer('recording_duration')->nullable()->after('recording_size'); // in seconds
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mock_test_sessions', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'ended_at', 'recording_filename', 'recording_size', 'recording_duration']);
        });
    }
};
