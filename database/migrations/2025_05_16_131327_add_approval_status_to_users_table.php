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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('role_id');
            $table->text('rejection_reason')->nullable()->after('approval_status');
            $table->boolean('is_active')->default(true)->after('rejection_reason');
            $table->timestamp('approved_at')->nullable()->after('is_active');
            $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'approval_status',
                'rejection_reason',
                'is_active',
                'approved_at',
                'approved_by'
            ]);
        });
    }
};
