<?php

namespace App\EventListener;

use App\Entity\Player;
use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class PlayerTeamIdListener
{
	/**
	 * @var EntityManagerInterface
	 */
	private $manager;

	public function __construct(EntityManagerInterface $manager) {
		$this->manager = $manager;
	}
    
    // the entity listener methods receive two arguments:
    // the entity instance and the lifecycle event
    public function prePersist(Player $player, LifecycleEventArgs $event): void
    {
        if ($player->getTeamId()) {
            $team = $this
                ->manager
                ->getRepository(Team::class)
                ->find($player->getTeamId());
            if ($team) {
                $player->setTeam($team);
            }
        }
    }
}