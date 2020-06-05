<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\importer;

use AndreasHGK\EasyKits\Kit;
use AndreasHGK\EasyKits\manager\DataManager;
use AndreasHGK\EasyKits\manager\KitManager;
use pocketmine\item\Item;
use pocketmine\Server;
use SaltyPixelDevz\ChestKits\Main;

class KitsPlusImporter {

    /**
     * @var Main
     */
    public static $kitPlugin;

    public static function ImportAll() : array {
        $kp = self::getKitPlugin();
        if(!self::isPluginLoaded()) return [];
        $return = [];
        if(!isset($kp::$c)) {
            $kp::$c = yaml_parse_file($kp->getDataFolder() . "config.yml");
        }
        foreach($kp::$c["Kits"] as $name => $kit) {
            $return[$kit["KitFormName"]] = self::Import($kit);
        }
        KitManager::saveAll();
        return $return;
    }

    public static function Import(array $kitData) : bool {
        $name = $kitData["KitFormName"];
        if(KitManager::exists($name)) return false;

        $permission = $kitData["Permission"];

        $price = $kitData["Cost"];
        $cooldown = $kitData["CooldownTime"];

        $armor = [];
        $armorImport = [
            "Helmet" => 0,
            "Chestplate" => 1,
            "Leggings" => 2,
            "Boots" => 3,
        ];
        foreach($armorImport as $piece => $slot) {
            if(!isset($kitData["piece"])) continue;
            $armorPiece = $kitData[$piece];
            $h = explode(":", $armorPiece);
            $armorPiece = Item::get((int)$h[0], (int)$h[1], (int)$h[2]);
            if($h[3] !== "default") {
                $armorPiece->setCustomName($h[3]);
            }
            $armorPiece = self::getKitPlugin()->EnchantTest($h, $armorPiece);
            $armor[$slot] = $armorPiece;
        }

        $items = [];

        $i = $kitData["Items"];
        foreach($i as $all) {
            $item = explode(":", $all);
            $in = Item::get((int)$item[0], (int)$item[1], (int)$item[2]);
            if($item[3] !== "default") {
                $in->setCustomName($item[3]);
            }
            $in = self::getKitPlugin()->EnchantTest($item, $in);
            $items[] = $in;
        }

        $kit = new Kit($name, $permission, (float)$price, (int)$cooldown, $items, $armor);

        $default = DataManager::getKey(DataManager::CONFIG, "default-flags");
        $kit->setLocked($default["locked"]);
        $kit->setEmptyOnClaim($default["emptyOnClaim"]);
        $kit->setDoOverride($default["doOverride"]);
        $kit->setDoOverrideArmor($default["doOverrideArmor"]);
        $kit->setAlwaysClaim($default["alwaysClaim"]);

        KitManager::add($kit, true);
        return true;
    }

    public static function getKitPlugin() : ?Main {
        if(!isset(self::$kitPlugin)) self::$kitPlugin = Server::getInstance()->getPluginManager()->getPlugin("KitsPlus");
        $pl = self::$kitPlugin;
        return $pl instanceof Main ? $pl : null;
    }

    public static function isPluginLoaded() : bool {
        return self::getKitPlugin() !== null;
    }

    private function __construct() {
    }

}