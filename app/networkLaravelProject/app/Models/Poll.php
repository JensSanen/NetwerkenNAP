<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    protected $table = 'polls';
    protected $primaryKey = 'id';

    protected $fillable = [
        'email_creator',
        'title',
        'description',
        'location',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function pollDates()
    {
        return $this->hasMany(PollDate::class, 'poll_id', 'id');
    }
    public function participants()
    {
        return $this->hasMany(Participant::class, 'poll_id', 'id');
    }

    public function isEnded()
    {
        return $this->active === false;
    }
}
