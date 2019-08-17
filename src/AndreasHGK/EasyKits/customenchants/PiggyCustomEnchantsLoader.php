<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\customenchants;

use AndreasHGK\EasyKits\EasyKits;
use DaPigGuy\PiggyCustomEnchants\Main;
use pocketmine\Server;

final class PiggyCustomEnchantsLoader {

    /**
     * @var Main
     */
    private static $customEnchants;

    public static function getPlugin() : Main {
        return self::$customEnchants;
    }

    public static function load() : void {
        $ce = Server::getInstance()->getPluginManager()->getPlugin("PiggyCustomEnchants");
        if($ce instanceof Main){
            self::$customEnchants = $ce;
            EasyKits::get()->getLogger()->info("loaded PiggyCustomEnchants");
        }
    }

    public static function isPluginLoaded() : bool {
        return isset(self::$customEnchants);
    }

    private function __construct()
    {
    }

}