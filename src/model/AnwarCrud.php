<?php

namespace Anwar\CrudGenerator\Model;

use Illuminate\Database\Eloquent\Model;

class AnwarCrud extends Model
{
    protected $table = TABLE_NAME;

    protected $fillable = ['name', 'controllers', 'uri'];
}
