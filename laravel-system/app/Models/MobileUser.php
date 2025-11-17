<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileUser extends Model
{
    use HasFactory;

    protected $table = 'mobile_users';

    protected $fillable = [
        'first_name',
        'last_name',
        'birth_date',
        'gender',
        'is_active',
        'phone',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_members');
    }
}
