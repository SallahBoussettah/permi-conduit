<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Skip this migration if the column doesn't exist
        if (!Schema::hasColumn('users', 'permit_category_id')) {
            return;
        }

        // First, migrate existing permit category data to the pivot table
        $users = DB::table('users')->whereNotNull('permit_category_id')->get();
        foreach ($users as $user) {
            // Insert into the pivot table
            DB::table('user_permit_categories')->insert([
                'user_id' => $user->id,
                'permit_category_id' => $user->permit_category_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Then remove the permit_category_id column
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['permit_category_id']);
            $table->dropColumn('permit_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the permit_category_id column
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('permit_category_id')->nullable()->after('role_id')
                ->constrained('permit_categories')->nullOnDelete();
        });
        
        // Try to migrate data back - only the first permit category will be migrated back
        $userPermitCategories = DB::table('user_permit_categories')
            ->select('user_id', 'permit_category_id')
            ->groupBy('user_id')
            ->get();
            
        foreach ($userPermitCategories as $userPermitCategory) {
            DB::table('users')
                ->where('id', $userPermitCategory->user_id)
                ->update(['permit_category_id' => $userPermitCategory->permit_category_id]);
        }
    }
};
