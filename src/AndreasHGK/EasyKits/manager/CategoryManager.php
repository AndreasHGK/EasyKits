<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\manager;

use AndreasHGK\EasyKits\Category;
use AndreasHGK\EasyKits\EasyKits;
use AndreasHGK\EasyKits\event\CategoryCreateEvent;
use AndreasHGK\EasyKits\event\CategoryDeleteEvent;
use AndreasHGK\EasyKits\event\CategoryEditEvent;
use pocketmine\utils\Config;

class CategoryManager {

    public const CATEGORY_FORMAT = [
        "kits" => [],
        "locked" => true,
    ];

    public static $categories = [];

    /**
     * @return Category[]
     */
    public static function getAll() : array {
        return self::$categories;
    }

    /**
     * @param string $name
     * @return Category|null
     */
    public static function get(string $name) : ?Category {
        return clone self::$categories[$name] ?? null;
    }

    public static function update(Category $old, Category $new, bool $silent = false) : bool {
        $event = new CategoryEditEvent($old, $new);
        if(!$silent) $event->call();

        if($event->isCancelled()) return false;

        self::remove($old, true);
        self::$categories[$event->getCategory()->getName()] = $event->getCategory();
        return true;
    }

    public static function add(Category $category, bool $silent = false) : bool {
        $event = new CategoryCreateEvent($category);
        if(!$silent) $event->call();

        if($event->isCancelled()) return false;

        self::$categories[$event->getCategory()->getName()] = $event->getCategory();
        return true;
    }

    public static function remove(Category $kit, bool $silent = false) : bool {
        $event = new CategoryDeleteEvent($kit);
        if(!$silent) $event->call();

        if($event->isCancelled()) return false;

        $kits = self::getCategoryFile();
        $kits->remove($event->getCategory()->getName());
        DataManager::save(DataManager::CATEGORIES);
        self::unload($event->getCategory()->getName());
        return true;
    }

    public static function loadAll() : void {
        $file = self::getCategoryFile()->getAll();
        foreach ($file as $name => $category){
            self::load((string)$name);
        }
    }

    public static function saveAll() : void {
        foreach(self::getAll() as $name => $category){
            self::save((string)$name);
        }
        DataManager::save(DataManager::CATEGORIES);
    }

    public static function reloadAll() : void {
        DataManager::reload(DataManager::CATEGORIES);
        self::unloadAll();
        self::loadAll();
    }

    public static function unloadAll() : void {
        self::$categories = [];
    }

    public static function unload(string $kit) : void {
        unset(self::$categories[$kit]);
    }

    public static function load(string $name) : void {
        $file = self::getCategoryFile()->getAll();
        $categorydata = $file[$name];
        try{
            $category = new Category($name);
            $kits = [];
            foreach($categorydata["kits"] as $kitname){
                $kits[$kitname] = KitManager::get($kitname);
            }
            $category->setLocked($categorydata["locked"]);
            $category->setKits($kits);
            self::$categories[$name] = $category;
        }catch (\Throwable $e) {
            EasyKits::get()->getLogger()->error("failed to load category '" . $name . "'");
        }
    }

    public static function save(string $name) : void {
        $file = self::getCategoryFile();
        $category = self::get($name);
        $categoryData = self::CATEGORY_FORMAT;
        foreach($category->getKits() as $kit){
            $categoryData["kits"][] = $kit->getName();
        }
        $categoryData["locked"] = $category->isLocked();
        $file->set($category->getName(), $categoryData);
    }

    public static function getCategoryFile() : Config{
        return DataManager::get(DataManager::CATEGORIES);
    }


    private function __construct(){}

}