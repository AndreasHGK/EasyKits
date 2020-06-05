<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\event;

use AndreasHGK\EasyKits\Kit;
use pocketmine\event\Cancellable;
use pocketmine\event\Event;

abstract class KitEvent extends Event implements Cancellable {

    protected $kit;

    public function __construct(Kit $kit) {
        $this->kit = $kit;
    }

    /**
     * @return Kit
     */
    public function getKit() : Kit {
        return $this->kit;
    }

    /**
     * @param Kit $kit
     */
    public function setKit(Kit $kit) : void {
        $this->kit = $kit;
    }

}
