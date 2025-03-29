<?php

namespace App\Service;

use App\Repository\TeamRepository;

readonly class TruncateTeams
{

    public function __construct(private TeamRepository $teamRepository)
    {
    }

    public function clearExistingTeams(): void
    {
        $teams = $this->teamRepository->findAll();

        foreach ($teams as $team) {
            $this->teamRepository->getEntityManager()->remove($team);
        }
    }
}
