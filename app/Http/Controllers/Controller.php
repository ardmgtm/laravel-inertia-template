<?php

namespace App\Http\Controllers;

use App\Traits\UserActivityTrait;

abstract class Controller
{
    use UserActivityTrait;
}
