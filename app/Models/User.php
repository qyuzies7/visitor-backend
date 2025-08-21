<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'email',
        'password',
        'role',
        'station_id',
        'description',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function visitorCards()
    {
        return $this->hasMany(VisitorCard::class, 'last_updated_by_user_id');
    }

    public function performedTransactions()
    {
        return $this->hasMany(CardTransaction::class, 'performed_by_user_id');
    }

    public function performedStatusLogs()
    {
        return $this->hasMany(VisitorCardStatusLog::class, 'performed_by_user_id');
    }
}