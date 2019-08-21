<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\event;

use pocketmine\Player;

trait PlayerEventTrait {

    /**
     * @var Player
     */
    protected $player;

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