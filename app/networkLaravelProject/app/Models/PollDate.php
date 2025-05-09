<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PollDate extends Model
{
    protected $table = 'poll_dates';
    protected $primaryKey = 'id';
    protected $fillable = ['poll_id', 'date'];

    public function poll()
    {
        return $this->belongsTo(Poll::class, "poll_id", "id");
    }

    public function votes()
    {
        return $this->hasMany(Vote::class, "poll_date_id", "id");
    }
}
