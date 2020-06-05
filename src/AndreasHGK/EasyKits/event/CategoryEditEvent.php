<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\event;

use AndreasHGK\EasyKits\Category;

class CategoryEditEvent extends CategoryEvent {

    /**
     * @var Category
     */
    protected $originalCategory;

    public function __construct(Category $old, Category $new) {
        parent::__construct($new);
        $this->originalCategory = $old;
    }

    /**
     * @return Category
     */
    public function getOriginalCategory() : Category {
        return $this->originalCategory;
    }

}