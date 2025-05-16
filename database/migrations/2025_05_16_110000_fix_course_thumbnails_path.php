<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Models\Course;

class FixCourseThumbnailsPath extends Migration
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        // Get all courses with thumbnails
        $courses = Course::whereNotNull('thumbnail')->get();
        
        foreach ($courses as $course) {
            // Check if the file exists in the private directory
            $privateFilePath = storage_path('app/private/public/' . $course->thumbnail);
            $publicFilePath = storage_path('app/public/' . $course->thumbnail);
            
            // If the file exists in the private directory but not in the public directory
            if (File::exists($privateFilePath) && !File::exists($publicFilePath)) {
                // Ensure directory exists
                $directory = dirname($publicFilePath);
                if (!File::exists($directory)) {
                    File::makeDirectory($directory, 0755, true);
                }
                
                // Copy the file to the public directory
                File::copy($privateFilePath, $publicFilePath);
                
                // You can optionally delete the file from private directory
                // File::delete($privateFilePath);
                
                echo "Fixed thumbnail path for course ID: " . $course->id . "\n";
            }
        }
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        // This migration is one-way and cannot be reversed
    }
} 