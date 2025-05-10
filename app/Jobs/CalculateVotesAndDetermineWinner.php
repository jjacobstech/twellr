<?php

namespace App\Jobs;

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class CalculateVotesAndDetermineWinner implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Example: Assume there's a "votes" table with `contestant_id`
        $winner = DB::table('votes')
            ->select('contestant_id', DB::raw('COUNT(*) as total_votes'))
            ->groupBy('contestant_id')
            ->orderByDesc('total_votes')
            ->first();

        if ($winner) {
            DB::table('winners')->insert([
                'contestant_id' => $winner->contestant_id,
                'votes' => $winner->total_votes,
                'won_at' => now(),
            ]);
        }
    }

}
