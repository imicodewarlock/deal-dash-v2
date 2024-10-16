<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConvertStoreIdInNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:convert-store-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert store_id in the data column of notifications from string to integer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get all notifications
        $notifications = DB::table('notifications')->get();

        foreach ($notifications as $notification) {
            $data = json_decode($notification->data, true);

            // Check if store_id exists and convert it to integer
            if (isset($data['store_id'])) {
                // Convert store_id to integer
                $data['store_id'] = (int) $data['store_id'];

                // Update the notification with the modified data
                DB::table('notifications')
                    ->where('id', $notification->id)
                    ->update(['data' => json_encode($data)]);
            }
        }

        $this->info('Store IDs converted to integers successfully.');
    }
}
