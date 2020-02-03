<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'description',
    ];

    protected $casts = [
        'finished_at' => 'datetime',
    ];

    protected $appends = ['is_closed'];

    public function project()
    {
        return $this->belongsTo('App\Models\Project');
    }

    public function getIsClosedAttribute()
    {
        return $this->isClosed();
    }

    public function isClosed()
    {
        return $this->finished_at != null;
    }

    public function scopeFromUser($query, \App\Models\User $user)
    {
        return $query->whereHas('project', function($query) use ($user) {
            $query->where('user_id', $user->id);
        });
    }
}
