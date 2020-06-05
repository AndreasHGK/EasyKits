<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\event;

use AndreasHGK\EasyKits\Category;
use pocketmine\event\Cancellable;
use pocketmine\event\Event;

abstract class CategoryEvent extends Event implements Cancellable {

    protected $category;

    public function __construct(Category $category) {
        $this->category = $category;
    }

    /**
     * @return Category
     */
    public function getCategory() : Category {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory(Category $category) : void {
        $this->category = $category;
    }

}
