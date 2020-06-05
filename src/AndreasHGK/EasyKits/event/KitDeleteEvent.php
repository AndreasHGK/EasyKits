<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\event;

use AndreasHGK\EasyKits\Kit;

class KitDeleteEvent extends KitEvent {

    public function __construct(Kit $kit) {
        parent::__construct($kit);
    }

}