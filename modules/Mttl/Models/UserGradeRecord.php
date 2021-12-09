<?php

namespace Modules\Mttl\Models;

use Illuminate\Database\Eloquent\Model;

class UserGradeRecord extends Model
{
    protected $table = 'mttl_user_grade_record';

    protected $guarded = [];

    protected $casts = [
        'exclude_userid' => 'array'
    ];
}
