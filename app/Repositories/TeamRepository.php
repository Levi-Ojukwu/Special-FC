<?php

namespace App\Repositories;

use App\Models\Team;

class TeamRepository
{
    protected $team;

    public function __construct(Team $team)
    {
        $this->team = $team;
    }

    public function getAll()
    {
        return $this->team->all();
    }

    public function getById($id)
    {
        return $this->team->find($id);
    }

    public function create(array $data)
    {
        return $this->team->create($data);
    }

    public function update($id, array $data)
    {
        $team = $this->getById($id);
        if ($team) {
            $team->update($data);
            return $team;
        }
        return null;
    }

    public function delete($id)
    {
        $team = $this->getById($id);
        if ($team) {
            return $team->delete();
        }
        return false;
    }

    public function getTeamsByRanking()
    {
        return $this->team->orderBy('points', 'desc')
            ->orderBy('goal_difference', 'desc')
            ->get();
    }

    public function updateTeamStats($id)
    {
        $team = $this->getById($id);
        if ($team) {
            $team->updateStats();
            return $team;
        }
        return null;
    }
}