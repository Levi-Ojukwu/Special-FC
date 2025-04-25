<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getAll()
    {
        return $this->user->all();
    }

    public function getById($id)
    {
        return $this->user->find($id);
    }

    public function getByUsername($username)
    {
        return $this->user->where('username', $username)->first();
    }

    public function create(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        return $this->user->create($data);
    }

    public function update($id, array $data)
    {
        $user = $this->getById($id);
        if ($user) {
            $user->update($data);
            return $user;
        }
        return null;
    }

    public function delete($id)
    {
        $user = $this->getById($id);
        if ($user) {
            return $user->delete();
        }
        return false;
    }

    public function getPlayers()
    {
        return $this->user->where('role', 'player')->get();
    }

    public function getAdmins()
    {
        return $this->user->where('role', 'admin')->get();
    }

    public function verifyUser($id)
    {
        $user = $this->getById($id);
        if ($user) {
            $user->is_verified = true;
            $user->save();
            return $user;
        }
        return null;
    }

    public function unverifyUser($id)
    {
        $user = $this->getById($id);
        if ($user) {
            $user->is_verified = false;
            $user->save();
            return $user;
        }
        return null;
    }
}