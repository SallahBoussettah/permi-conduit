<?php

namespace App\Console\Commands;

use App\Models\School;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncSchoolCandidateCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schools:sync-candidate-counts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronizes all school active candidate counts with the actual count of active candidates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $schools = School::all();
        $updatedCount = 0;
        
        $this->info("Starting to sync candidate counts for {$schools->count()} schools...");
        $this->line("Note: Only users with 'candidate' role are counted toward limits. Admins and inspectors are excluded.");
        
        foreach ($schools as $school) {
            $oldCount = $school->current_active_candidate_count;
            $newCount = $school->activeCandidatesCount();
            
            if ($oldCount !== $newCount) {
                $school->current_active_candidate_count = $newCount;
                $school->save();
                $updatedCount++;
                
                $this->line("Updated school ID {$school->id} ({$school->name}): {$oldCount} → {$newCount} active candidates");
                Log::info("School candidate count synced: School ID {$school->id}, {$oldCount} → {$newCount} active candidates");
            }
            
            // Show capacity information with buffer
            $baseCapacity = $school->candidate_limit;
            $actualCapacity = $baseCapacity + 1; // Add buffer slot
            $remaining = $school->getRemainingCapacity();
            $this->line("  - School {$school->name}: {$newCount}/{$baseCapacity} candidates ({$remaining} slots remaining, includes +1 buffer)");
        }
        
        if ($updatedCount > 0) {
            $this->info("Sync completed. Updated counts for {$updatedCount} schools.");
        } else {
            $this->info("Sync completed. All school candidate counts were already accurate.");
        }
        
        return 0;
    }
} 