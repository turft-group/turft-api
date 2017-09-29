<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

class Group extends Model
{
    protected $hidden = array('created_at', 'updated_at');
    protected $fillable = ['name'];

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
        $insert = (!isset($this->id));
        parent::save($options);

        if ($insert) {
            $this->users()->attach(Auth::user()->id, ["role" => "owner"]);
        }
    }

    public function hasUser($user)
    {
        return $this->users()->where('user_id', $user)->exists();
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
        if ($role == "owner") {
            return false;
        }
        $this->users()->attach($user, ["role" => $role]);
        return true;
    }

    public function getUserRole($user)
    {
        if (!$this->hasUser($user)) {
            return null;
        }
        $row = $this->users()->where('user_id', $user)->first();
        return $row["pivot"]["role"];
    }

    public function updateUser($user, $role) {
        if ($role == "owner") {
            $owner = $this->users()->where('role', "owner")->first();
            if ($owner['pivot']['user_id'] != $user && $this->hasUser($user)) {
                $this->users()->updateExistingPivot($owner['pivot']['user_id'], ['role' => "admin"]);
                $this->users()->updateExistingPivot($user, ['role' => "owner"]);
            }
        } else {
            $this->users()->updateExistingPivot($user, ['role' => $role]);
        }
    }

}