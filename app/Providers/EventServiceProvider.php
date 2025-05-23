<?php

namespace App\Providers;

use App\Events\NewNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        NewNotification::class => [
            // Define a custom listener if needed
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Register a listener to log when a notification is broadcast
        Event::listen(NewNotification::class, function (NewNotification $event) {
            Log::info('Broadcasting notification event', [
                'notification_id' => $event->notification->id,
                'user_id' => $event->notification->user_id,
                'message' => $event->notification->message
            ]);
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
} 