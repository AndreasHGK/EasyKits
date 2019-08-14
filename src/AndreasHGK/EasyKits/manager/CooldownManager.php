<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\manager;

use AndreasHGK\EasyKits\Kit;
use pocketmine\Player;

class CooldownManager {

    /**
     * @var self
     */
    public static $instance = null;

    public $cooldowns = [];

    /**
     * returns if the player has a cooldown for a kit
     * @param Kit $kit
     * @param Player $player
     * @return bool
     */
    public static function hasKitCooldown(Kit $kit, Player $player) : bool {
        $kitCooldowns = self::getCooldowns();
        if(isset($kitCooldowns[$kit->getName()][$player->getName()])){
            if($kitCooldowns[$kit->getName()][$player->getName()] + $kit->getCooldown() > time()){
                return true;
            }
            self::unsetKitCooldown($kit, $player);
            self::saveCooldowns();
        }
        return false;
    }

    /**
     * returns the cooldown time left for a kit
     * @param Kit $kit
     * @param Player $player
     * @return int
     */
    public static function getKitCooldown(Kit $kit, Player $player) : int {
        $kitCooldowns = self::getCooldowns();
        if(!self::hasKitCooldown($kit, $player)) return 0;
        return ($kitCooldowns[$kit->getName()][$player->getName()] + $kit->getCooldown()) - time();
    }

    /**
     * @param Kit $kit
     * @param Player $player
     */
    public static function setKitCooldown(Kit $kit, Player $player) : void {
        self::getInstance()->cooldowns[$kit->getName()][$player->getName()] = time();
        self::saveCooldowns();
    }

    /**
     * @param Kit $kit
     * @param Player $player
     */
    public static function unsetKitCooldown(Kit $kit, Player $player) : void {
        unset(self::getInstance()->cooldowns[$kit->getName()][$player->getName()]);
        self::saveCooldowns();
    }



    /**
     * @internal
     * @return array
     */
    public static function getCooldowns() : array {
        return self::getInstance()->cooldowns;
    }

    /**
     * @internal
     */
    public static function loadCooldowns() : void {
        self::getInstance()->cooldowns = DataManager::get(DataManager::COOLDOWN)->getAll();
    }

    /**
     * @internal
     */
    public static function saveCooldowns() : void {
        DataManager::get(DataManager::COOLDOWN)->setAll(self::getCooldowns());
        DataManager::save(DataManager::COOLDOWN);
    }

    private function __construct(){}

    public static function getInstance() : self {
        if(self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

}