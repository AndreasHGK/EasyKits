<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\event;

use AndreasHGK\EasyKits\Kit;

class KitEditEvent extends KitEvent {

    /**
     * @var Kit
     */
    protected $originalKit;

    public function __construct(Kit $old, Kit $new) {
        parent::__construct($new);
        $this->originalKit = $old;
    }

    /**
     * @return Kit
     */
    public function getOriginalKit() : Kit {
        return $this->originalKit;
    }

}