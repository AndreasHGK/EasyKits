<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\utils;

use AndreasHGK\EasyKits\customenchants\PiggyCustomEnchantsLoader;
use AndreasHGK\EasyKits\Kit;
use AndreasHGK\EasyKits\manager\CooldownManager;
use AndreasHGK\EasyKits\manager\DataManager;
use DaPigGuy\PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

abstract class ItemUtils {

    public const ITEM_FORMAT = [
        "id" => 1,
        "damage" => 0,
        "count" => 1,
        "display_name" => "",
        "lore" => [

        ],
        "enchants" => [

        ],
    ];

    /**
     * @param array $itemData
     * @return Item
     */
    public static function dataToItem(array $itemData) : Item {
        $item = ItemFactory::get($itemData["id"], $itemData["damage"] ?? 0, $itemData["count"] ?? 1);
        if(isset($itemData["enchants"])){
            foreach($itemData["enchants"] as $ename => $level){
                $ench = Enchantment::getEnchantment((int)$ename);
                if(PiggyCustomEnchantsLoader::isPluginLoaded() && $ench === null){
                    $ench = CustomEnchants::getEnchantment((int)$ename);
                }
                if($ench === null) continue;
                if($ench instanceof CustomEnchants){
                    PiggyCustomEnchantsLoader::getPlugin()->addEnchantment($item, $ench->getName(), $level);
                }else{
                    $item->addEnchantment(new EnchantmentInstance($ench, $level));
                }
            }
        }
        if(isset($itemData["display_name"])) $item->setCustomName(TextFormat::colorize($itemData["display_name"]));
        if(isset($itemData["lore"])) {
            $lore = [];
            foreach($itemData["lore"] as $key=> $ilore){
                $lore[$key] = TextFormat::colorize($ilore);
            }
            $item->setLore($lore);
        }

        return $item;
    }

    /**
     * @param Item $item
     * @return array
     */
    public static function itemToData(Item $item) : array {
        $itemData = self::ITEM_FORMAT;
        $itemData["id"] = $item->getId();
        $itemData["damage"] = $item->getDamage();
        $itemData["count"] = $item->getCount();
        if($item->hasCustomName()){
            $itemData["display_name"] = $item->getCustomName();
        }else{
            unset($itemData["display_name"]);
        }
        if($item->getLore() !== []){
            $itemData["lore"] = $item->getLore();
        }else{
            unset($itemData["lore"]);
        }
        if($item->hasEnchantments()){
            foreach($item->getEnchantments() as $enchantment){
                $itemData["enchants"][(string)$enchantment->getId()] = $enchantment->getLevel();
            }
        }else{
            unset($itemData["enchants"]);
        }

        return $itemData;
    }

}
