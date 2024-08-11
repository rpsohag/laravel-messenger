<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favourite extends Model
{
    use HasFactory;

    function users(){
        return $this->belongsTo(User::class, 'favourite_id', 'id');
    }
}
