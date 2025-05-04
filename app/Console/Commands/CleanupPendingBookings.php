<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;

class CleanupPendingBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:cleanup-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Booking::where('status', 'pending')
            ->where('reserved_until', '<', now())
            ->delete();

        $this->info('Expired pending bookings cleaned up.');
    }
}
