<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $table = 'participants';
    protected $primaryKey = 'id';
    protected $fillable = ['email', 'poll_id', 'has_voted', 'vote_token'];

    protected $casts = [
        'has_voted' => 'boolean',
    ];

    public function poll()
    {
        return $this->belongsTo(Poll::class, 'poll_id', 'id');
    }

    public function votes()
    {
        return $this->hasMany(Vote::class, 'participant_id', 'id');
    }
}
