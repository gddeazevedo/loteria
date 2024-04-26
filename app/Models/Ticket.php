<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $primaryKey = 'code';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['code', 'name', 'numbers', 'machine_numbers'];

    protected function numbers(): Attribute
    {
        return Attribute::make(
            get: fn (string $val) => json_decode($val, true),
            set: fn(array $val) => json_encode($val)
        );
    }

    // protected function machine_numbers(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn (string $val) => json_decode($val, true),
    //         set: fn(array $val) => json_encode($val)
    //     );
    // }
}
