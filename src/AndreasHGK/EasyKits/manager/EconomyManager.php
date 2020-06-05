<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\manager;

use AndreasHGK\EasyKits\EasyKits;
use onebone\economyapi\EconomyAPI;
use pocketmine\Player;
use pocketmine\Server;
use Twisted\MultiEconomy\MultiEconomy;

class EconomyManager {

    public static $instance;

    /**
     * @var null|EconomyAPI|MultiEconomy
     */
    public static $economy = null;

    public static function getMoney(Player $player) {
        switch(true) {
            case self::getEconomy() instanceof EconomyAPI:
                return self::getEconomy()->myMoney($player);
                break;
            case self::getEconomy() instanceof MultiEconomy:
                return self::getEconomy()->getAPI()->getBalance($player->getName(), DataManager::getKey(DataManager::CONFIG, "multieconomy-currency"));
                break;
        }
        return 0;
    }

    public static function setMoney(Player $player, $money, bool $force = false) {
        switch(true) {
            case self::getEconomy() instanceof EconomyAPI:
                self::getEconomy()->setMoney($player, $money);
                break;
            case self::getEconomy() instanceof MultiEconomy:
                self::getEconomy()->getAPI()->setBalance($player->getName(), DataManager::getKey(DataManager::CONFIG, "multieconomy-currency"), $money);
                break;
        }
    }

    public static function reduceMoney(Player $player, $money, bool $force = false) {
        switch(true) {
            case self::getEconomy() instanceof EconomyAPI:
                self::getEconomy()->reduceMoney($player, $money, $force);
                break;
            case self::getEconomy() instanceof MultiEconomy:
                self::getEconomy()->getAPI()->takeFromBalance($player->getName(), DataManager::getKey(DataManager::CONFIG, "multieconomy-currency"), $money);
                break;
        }
    }

    public static function addMoney(Player $player, $money, bool $force = false) {
        switch(true) {
            case self::getEconomy() instanceof EconomyAPI:
                self::getEconomy()->addMoney($player, $money, $force);
                break;
            case self::getEconomy() instanceof MultiEconomy:
                self::getEconomy()->getAPI()->addToBalance($player->getName(), DataManager::getKey(DataManager::CONFIG, "multieconomy-currency"), $money);
                break;
        }
    }

    public static function loadEconomy() : void {
        $plugins = Server::getInstance()->getPluginManager();
        $economyAPI = $plugins->getPlugin("EconomyAPI");
        if($economyAPI instanceof EconomyAPI) {
            self::$economy = $economyAPI;
            EasyKits::get()->getLogger()->info("loaded EconomyAPI");
            return;
        }
        $multiEconomy = $plugins->getPlugin("MultiEconomy");
        if($multiEconomy instanceof MultiEconomy) {
            self::$economy = $multiEconomy;
            EasyKits::get()->getLogger()->info("loaded MultiEconomy");
            return;
        }
    }

    public static function isEconomyLoaded() : bool {
        return self::getEconomy() !== null;
    }

    public static function getEconomy() {
        return self::$economy;
    }

    private function __construct() {
    }

}