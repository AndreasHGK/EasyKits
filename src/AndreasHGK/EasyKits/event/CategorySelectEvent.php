<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\event;

use AndreasHGK\EasyKits\Category;
use pocketmine\Player;

class CategorySelectEvent extends CategoryEvent {

    use PlayerEventTrait;

    public function __construct(Player $player, Category $category) {
        parent::__construct($category);
        $this->player = $player;
    }

}