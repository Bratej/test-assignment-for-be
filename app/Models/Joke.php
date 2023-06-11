<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Joke extends Model
{
    protected $fillable = [
        'joke_api_id',
        'type',
        'setup',
        'punchline',
    ];
}
