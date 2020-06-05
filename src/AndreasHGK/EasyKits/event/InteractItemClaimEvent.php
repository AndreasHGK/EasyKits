<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\event;

use AndreasHGK\EasyKits\Kit;
use pocketmine\Player;

class InteractItemClaimEvent extends KitEvent {

    use PlayerEventTrait;

    public function __construct(Kit $kit, Player $player) {
        parent::__construct($kit);
        $this->player = $player;
    }

}