<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVote extends Model
{
    protected $fillable = [
        'user_id',
        'contestant_id',
        'contest_id'
    ];
}
