<?php

namespace App\Providers;

use App\Models\ExamTool;
use App\Models\Student;
use App\Observers\ExamToolObserver;
use App\Observers\StudentObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //

        ExamTool::observe(ExamToolObserver::class);
        Student::observe(StudentObserver::class);
    }
}
