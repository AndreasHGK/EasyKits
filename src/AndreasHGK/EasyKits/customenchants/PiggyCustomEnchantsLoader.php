<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\customenchants;

use AndreasHGK\EasyKits\EasyKits;
use DaPigGuy\PiggyCustomEnchants\Main;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

final class PiggyCustomEnchantsLoader {

    /**
     * @var Main|PiggyCustomEnchants
     */
    private static $customEnchants;

    private static $isNewVersion;

    /**
     * @return Main|PiggyCustomEnchants
     */
    public static function getPlugin() : PluginBase {
        return self::$customEnchants;
    }

    public static function load() : void {
        $ce = Server::getInstance()->getPluginManager()->getPlugin("PiggyCustomEnchants");
        if($ce instanceof Main || $ce instanceof PiggyCustomEnchants) {
            self::$customEnchants = $ce;

            if($ce instanceof PiggyCustomEnchants) self::$isNewVersion = true;
            else self::$isNewVersion = false;

            EasyKits::get()->getLogger()->info("loaded PiggyCustomEnchants");
        }
    }

    public static function isNewVersion() : bool {
        return self::$isNewVersion ?? true;
    }

    public static function isPluginLoaded() : bool {
        return isset(self::$customEnchants);
    }

    private function __construct() {
    }

}