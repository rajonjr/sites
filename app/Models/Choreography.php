<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Choreography extends Model
{
    protected $fillable = ['title', 'category', 'level', 'duration', 'verse', 'img', 'desc'];
}
