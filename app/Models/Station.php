<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Station extends Model
{
    use HasFactory;

    protected $fillable = [
        'station_name',
        'station_code',
        'address',
        'is_active',
    ];

    public function users(){
        return $this->hasMany(User::class);
    }

    public function VisitorCards(){
        return $this->hasMany(VisitorCard::class);
    }
}
