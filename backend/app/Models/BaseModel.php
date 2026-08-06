<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

abstract class BaseModel extends Model
{
    use HasUuids;

    /**
     * Los UUID no son autoincrementales.
     */
    public $incrementing = false;

    /**
     * La clave primaria es un string.
     */
    protected $keyType = 'string';
}
