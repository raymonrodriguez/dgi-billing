<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    use LogsActivity;
}
