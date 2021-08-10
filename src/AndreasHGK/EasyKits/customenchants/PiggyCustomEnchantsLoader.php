<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\customenchants;

use AndreasHGK\EasyKits\EasyKits;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use pocketmine\Server;

final class PiggyCustomEnchantsLoader {

    /** @var PiggyCustomEnchants */
    private static $customEnchants;

    /**
     * @return PiggyCustomEnchants
     */
    public static function getPlugin() : PiggyCustomEnchants {
        return self::$customEnchants;
    }

    public static function load() : void {
        $ce = Server::getInstance()->getPluginManager()->getPlugin("PiggyCustomEnchants");
        if($ce instanceof PiggyCustomEnchants) {
            self::$customEnchants = $ce;
            EasyKits::get()->getLogger()->debug("loaded PiggyCustomEnchants");
        }
    }

    public static function isPluginLoaded() : bool {
        return isset(self::$customEnchants);
    }

    private function __construct() {
    }

}
