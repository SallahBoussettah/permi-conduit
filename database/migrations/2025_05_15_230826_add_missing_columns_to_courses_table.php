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
        Schema::table('courses', function (Blueprint $table) {
            $table->boolean('status')->default(true)->after('description');
            $table->foreignId('category_id')->nullable()->after('status')->constrained('course_categories')->nullOnDelete();
            $table->foreignId('inspector_id')->nullable()->after('category_id')->constrained('users')->nullOnDelete();
            $table->string('thumbnail')->nullable()->after('inspector_id');
            $table->foreignId('created_by')->nullable()->after('thumbnail')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->after('updated_by')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['inspector_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['deleted_by']);
            
            $table->dropColumn([
                'status',
                'category_id',
                'inspector_id',
                'thumbnail',
                'created_by',
                'updated_by',
                'deleted_by',
            ]);
        });
    }
};
