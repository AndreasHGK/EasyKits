<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\manager;

use onebone\economyapi\EconomyAPI;
use pocketmine\Player;
use pocketmine\Server;

class EconomyManager{

    public static $instance;

    /**
     * @var null|EconomyAPI
     */
    public $economy = null;

    public static function getMoney(Player $player) {
        switch (true){
            case self::getEconomy() instanceof EconomyAPI:
                return self::getEconomy()->myMoney($player);
                break;
        }
        return 0;
    }

    public static function setMoney(Player $player, $money, bool $force = false) {
        switch (true){
            case self::getEconomy() instanceof EconomyAPI:
                self::getEconomy()->setMoney($player, $money);
                break;
        }
    }

    public static function reduceMoney(Player $player, $money, bool $force = false) {
        switch (true){
            case self::getEconomy() instanceof EconomyAPI:
                self::getEconomy()->reduceMoney($player, $money, $force);
                break;
        }
    }

    public static function addMoney(Player $player, $money, bool $force = false) {
        switch (true){
            case self::getEconomy() instanceof EconomyAPI:
                self::getEconomy()->addMoney($player, $money, $force);
                break;
        }
    }

    public static function loadEconomy() : void {
        $plugins = Server::getInstance()->getPluginManager();
        $economyAPI = $plugins->getPlugin("EconomyAPI");
        if($economyAPI instanceof EconomyAPI) {
            self::getInstance()->economy = $economyAPI;
            return;
        }
    }

    public static function isEconomyLoaded() : bool {
        return self::getEconomy() !== null;
    }

    public static function getEconomy() {
        return self::getInstance()->economy;
    }

    public static function getInstance() : self {
        if(!isset(self::$instance)) self::$instance = new self();
        return self::$instance;
    }

    private function __construct()
    {
    }

}