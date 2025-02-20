<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    public $table = 'user_activity';
    public $guarded = [];
    public $timestamps = false;
}
