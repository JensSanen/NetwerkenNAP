<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    protected $table = 'votes';
    protected $primaryKey = 'id';

    protected $fillable = ['participant_id', 'poll_date_id'];

    public function participant()
    {
        return $this->belongsTo(Participant::class, 'participant_id', 'id');
    }

    public function pollDate()
    {
        return $this->belongsTo(PollDate::class, 'poll_date_id', 'id');
    }
}
