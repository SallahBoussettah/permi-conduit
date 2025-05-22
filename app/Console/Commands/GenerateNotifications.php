<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class GenerateNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:generate {--type=all : The type of notifications to generate (all, incomplete-courses, upcoming-exams)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate notifications for users based on their activity and upcoming events';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService)
    {
        $type = $this->option('type');

        $this->info("Generating notifications of type: {$type}");

        switch ($type) {
            case 'incomplete-courses':
                $notificationService->notifyIncompleteCourses();
                $this->info('Incomplete course notifications generated.');
                break;

            case 'upcoming-exams':
                $notificationService->notifyUpcomingExams();
                $this->info('Upcoming exam notifications generated.');
                break;

            case 'all':
            default:
                $notificationService->notifyIncompleteCourses();
                $notificationService->notifyUpcomingExams();
                $this->info('All notifications generated.');
                break;
        }

        return Command::SUCCESS;
    }
} 