<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\event;

use AndreasHGK\EasyKits\Kit;
use pocketmine\Player;

class KitClaimEvent extends KitEvent {

    protected $player;

    public function __construct(Kit $kit, Player $player)
    {
        parent::__construct($kit);
        $this->player = $player;
    }

    /**
     * @return Player
     */
    public function getPlayer() : Player {
        return $this->player;
    }

    /**
     * @param Player $player
     */
    public function setPlayer(Player $player) : void {
        $this->player = $player;
    }

}