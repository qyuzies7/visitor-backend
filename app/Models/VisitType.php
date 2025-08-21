<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VisitType extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_name',
        'max_duration_days',
        'description',
        'is_active',
    ];

    public function VisitorCards(){
        return $this->hasMany(VisitorCard::class);
    }

}
