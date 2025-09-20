<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

class ResetStudentPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * php artisan students:reset-passwords
     */
    protected $signature = 'students:reset-passwords';

    /**
     * The console command description.
     */
    protected $description = 'Reset all student passwords to equal their mobile number';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Resetting all student passwords...");

        $count = 0;

        Student::chunk(100, function ($students) use (&$count) {
            foreach ($students as $student) {
                if (!empty($student->mobile)) {
                    $student->password = Hash::make($student->mobile);
                    $student->save();
                    $count++;
                }
            }
        });

        $this->info("Done! Reset {$count} student passwords.");
    }
}
