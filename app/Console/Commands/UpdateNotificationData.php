<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateNotificationData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:update-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the data column in the notifications table to remove offer_details wrapper';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get all notifications
        $notifications = DB::table('notifications')->get();

        foreach ($notifications as $notification) {
            $data = json_decode($notification->data, true);

            // Check if 'offer_details' exists and transform data
            if (isset($data['offer_details'])) {
                $newData = $data['offer_details']; // Extract the inner data

                // Update the notification with the new structure
                DB::table('notifications')
                    ->where('id', $notification->id)
                    ->update(['data' => json_encode($newData)]);
            }
        }

        $this->info('Notification data updated successfully.');
    }
}
