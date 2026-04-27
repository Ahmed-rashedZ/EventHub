<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PruneRejectedPartners extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:prune-rejected-partners';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes rejected partner accounts that have been rejected for more than 7 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoffDate = Carbon::now()->subDays(7);
        $usersToPrune = User::where('verification_status', 'rejected')
                            ->where('updated_at', '<', $cutoffDate)
                            ->get();
        
        $count = $usersToPrune->count();

        foreach ($usersToPrune as $user) {
            $docTypes = [
                'doc_commercial_register',
                'doc_tax_number',
                'doc_articles_of_association',
                'doc_practice_license',
            ];
            foreach ($docTypes as $docType) {
                if ($user->{$docType} && Storage::exists($user->{$docType})) {
                    Storage::delete($user->{$docType});
                }
            }
            $user->delete();
        }

        $this->info("Pruned {$count} rejected partners.");
    }
}
