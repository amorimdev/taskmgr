<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name'
    ];

    protected $hidden = [
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function tasks()
    {
        return $this->hasMany('App\Models\Task');
    }

    public function scopeFromUser($query, \App\Models\User $user)
    {
        return $query->where('user_id', $user->id);
    }
}
