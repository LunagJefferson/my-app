<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Notepad extends Model
{
    protected $fillable = [
        'title',
        'content',
        'user_id',
        'is_public',
        'share_token'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'notepad_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function userRole($user)
    {
        if ($this->user_id == $user->id) {
            return 'owner';
        }

        $relation = $this->users()->where('user_id', $user->id)->first();

        return $relation ? $relation->pivot->role : null;
    }

    protected static function booted()
    {
        static::creating(function ($notepad) {

            if (empty($notepad->share_token)) {

                do {
                    $token = Str::random(32);
                } while (self::where('share_token', $token)->exists());

                $notepad->share_token = $token;
            }

        });
    }
}