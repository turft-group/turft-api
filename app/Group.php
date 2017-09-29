<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

class Group extends Model
{
    protected $hidden = array('created_at', 'updated_at');

    /**
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany('App\User')
            ->withPivot('role');
    }

    public function save(array $options = [])
    {
        $insert = (isset($this->id));
        parent::save($options);

        if ($insert) {
            $this->addUser(Auth::user()->id, "owner");
        }
    }

    public function hasUser($user)
    {
        return $this->users()->contains($user->id);
    }

    public function isOwner($user)
    {
        $member = $this->users()->get($user->id);
        return ($member->pivot->role == "owner");
    }

    public function isAdmin($user)
    {
        $member = $this->users()->get($user->id);
        return ($member->pivot->role == "admin");
    }

    public function addUser($user, $role = "member")
    {
        $this->users()->attach($user->id, ['role' => $role]);
    }

    public function getUserRole($user)
    {
        if (!$this->hasUser($user)) {
            return null;
        }
        $row = $this->users()->where('user_id', $user)->select();
        return $row['role'];
    }

    public function updateUser($user, $role) {
        if ($role == "owner") {
            $owner = $this->users()->where('role', "owner")->select();
            if ($owner['user_id'] != $user && $this->hasUser($user)) {
                $this->users()->updateExistingPivot($owner['user_id'], ['role' => "admin"]);
                $this->users()->updateExistingPivot($user, ['role' => "owner"]);
            }
        } else {
            $this->users()->updateExistingPivot($user, ['role' => $role]);
        }
    }

}