<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    protected $table = 'polls'; 
    protected $primaryKey = 'id';

    protected $fillable = [
        'title',
        'description',
        'location'
    ];
}
