<?php

namespace App\Console\Commands;

use App\Models\ExhibitionApplication;
use Illuminate\Console\Command;

class AutoRejectExpiredExhibitionApplications extends Command
{
    protected $signature = 'exhibitions:auto-reject';
    protected $description = 'Auto-reject pending exhibition applications for events that have started or ended.';

    public function handle()
    {
        $count = ExhibitionApplication::where('status', 'pending')
            ->whereHas('event', function ($query) {
                $query->where('start_time', '<=', now());
            })
            ->update(['status' => 'rejected']);

        $this->info("Auto-rejected {$count} expired exhibition applications.");
    }
}
