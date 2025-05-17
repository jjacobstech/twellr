<?php

namespace App\Console\Commands;

use App\Models\AdminSetting;
use App\Models\ContestWinner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Winners extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'winners';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate Votes and Determine Winners';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        info('Calculating votes and determining winners...');
        // Example: Assume there's a "votes" table with `contestant_id`

        $setting =  AdminSetting::first();
        $setting->voting = 0;
        $saved = $setting->save();

        if($saved){
            $designContestWinner = DB::table('votes')
                ->select('contestant_id', DB::raw('COUNT(*) as total_votes'))
                ->groupBy('contestant_id')
                ->orderByDesc('total_votes')
                ->first();

            if ($designContestWinner) {
                ContestWinner::where('contest_type', 'design_fest')->delete();
                // Delete previous winners for the contest type
                ContestWinner::create([
                    'user_id' => $designContestWinner->contestant_id,
                    'votes' => $designContestWinner->total_votes,
                    'contest_type' => 'design_fest',
                    'won_at' => now(),
                ]);
            }


            $userContestWinner = DB::table('user_votes')
                ->select('contestant_id', DB::raw('COUNT(*) as total_votes'))
                ->groupBy('contestant_id')
                ->orderByDesc('total_votes')
                ->first();

            if ($userContestWinner) {
                ContestWinner::where('contest_type', 'who_rocked_it_best')->delete();
                // Delete previous winners for the contest type
                ContestWinner::create([
                    'user_id' => $userContestWinner->contestant_id,
                    'votes' => $userContestWinner->total_votes,
                    'contest_type' => 'who_rocked_it_best',
                    'won_at' => now(),
                ]);
            }

            info('Votes calculated and winners determined successfully.');
            $this->info('Votes calculated and winners determined successfully.');

        } else {
            info('Failed to save settings.');
            $this->error('Failed to save settings.');
        }
    }
}
