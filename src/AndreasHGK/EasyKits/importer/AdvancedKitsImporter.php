<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\importer;

use AdvancedKits\Main;
use AndreasHGK\EasyKits\EasyKits;
use AndreasHGK\EasyKits\Kit;
use AndreasHGK\EasyKits\manager\DataManager;
use AndreasHGK\EasyKits\manager\KitManager;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class AdvancedKitsImporter{

    public static $kitPlugin;

    public static function ImportAll(): array
    {
        $kp = self::getKitPlugin();
        if(!self::isPluginLoaded()) return [];
        $return = [];
        foreach($kp->kits as $name => $kit){
            $return[$name] = self::Import($kit);
        }
        KitManager::saveAll();
        return $return;
    }

    //a big thanks to AdvancedKits for no $kit->getItems() method
    public static function Import(\AdvancedKits\Kit $akit): bool
    {
        $reflect = new \ReflectionProperty(\AdvancedKits\Kit::class, "name");
        $reflect->setAccessible(true);
        $name = $reflect->getValue($akit);
        if(KitManager::exists($name)) return false;

        $reflect = new \ReflectionProperty(\AdvancedKits\Kit::class, "items");
        $reflect->setAccessible(true);
        $items = $reflect->getValue($akit);

        $reflect = new \ReflectionProperty(\AdvancedKits\Kit::class, "armor");
        $reflect->setAccessible(true);
        $akArmor = $items = $reflect->getValue($akit);
        $armor = [];
        foreach($akArmor as $slot => $akArmorItem){
            if($akArmorItem === null) continue;
            switch ($slot){
                case "helmet":
                    $key = 3;
                    break;
                case "chestplate":
                    $key = 2;
                    break;
                case "leggings":
                    $key = 1;
                    break;
                case "boots":
                    $key = 0;
                    break;
                default:
                    continue;
            }
            $armor[$key] = $akArmorItem;
        }
        $reflect = new \ReflectionProperty(\AdvancedKits\Kit::class, "coolDown");
        $reflect->setAccessible(true);
        $cooldown = $reflect->getValue($akit)*60;

        $reflect = new \ReflectionProperty(\AdvancedKits\Kit::class, "cost");
        $reflect->setAccessible(true);
        $price = $reflect->getValue($akit);

        $kit = new Kit($name, $price, $cooldown, $items, $armor);

        $default = DataManager::getKey(DataManager::CONFIG, "default-flags");
        $kit->setLocked($default["locked"]);
        $kit->setEmptyOnClaim($default["emptyOnClaim"]);
        $kit->setDoOverride($default["doOverride"]);
        $kit->setDoOverrideArmor($default["doOverrideArmor"]);
        $kit->setAlwaysClaim($default["alwaysClaim"]);

        KitManager::add($kit, true);
        return true;
    }

    public static function getKitPlugin(): ?Main
    {
        if(!isset(self::$kitPlugin)) self::$kitPlugin = Server::getInstance()->getPluginManager()->getPlugin("AdvancedKits");
        $pl = self::$kitPlugin;
        return $pl instanceof Main ? $pl : null;
    }

    public static function isPluginLoaded() : bool {
        return self::getKitPlugin() !== null;
    }

    private function __construct()
    {
    }

}