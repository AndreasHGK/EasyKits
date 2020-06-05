<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\event;

use AndreasHGK\EasyKits\Category;

class CategoryDeleteEvent extends CategoryEvent {

    public function __construct(Category $category) {
        parent::__construct($category);
    }

}