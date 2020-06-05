<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\ui;

use AndreasHGK\EasyKits\Kit;
use AndreasHGK\EasyKits\manager\KitManager;
use AndreasHGK\EasyKits\utils\LangUtils;
use muqsit\invmenu\inventories\BaseFakeInventory;
use muqsit\invmenu\inventories\DoubleChestInventory;
use muqsit\invmenu\InvMenu;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\Player;

class EditKitItemInventory {

    public static function sendTo(Player $player, Kit $kit) : void {
        $menu = InvMenu::create(DoubleChestInventory::class);
        $menu->readonly(false);
        $menu->setName(LangUtils::getMessage("editkit-items-title", true, ["{NAME}" => $kit->getName()]));
        $menu->setInventoryCloseListener(function (Player $player, BaseFakeInventory $inventory) use ($kit) {
            $items = [];
            for($i = 0; $i < 36; $i++) {
                $item = $inventory->getItem($i);
                if($item->getId() !== Item::AIR) $items[$i] = $item;
            }
            $armor = [];
            $armorPiece = $inventory->getItem(47);
            if($armorPiece->getId() !== Item::AIR) {
                $armor[3] = $armorPiece;
            }
            $armorPiece = $inventory->getItem(48);
            if($armorPiece->getId() !== Item::AIR) {
                $armor[2] = $armorPiece;
            }
            $armorPiece = $inventory->getItem(50);
            if($armorPiece->getId() !== Item::AIR) {
                $armor[1] = $armorPiece;
            }
            $armorPiece = $inventory->getItem(51);
            if($armorPiece->getId() !== Item::AIR) {
                $armor[0] = $armorPiece;
            }
            $new = clone $kit;

            $new->setItems($items);
            $new->setArmor($armor);

            if($kit->getItems() === $items && $kit->getArmor() === $armor) {
                EditkitMainForm::sendTo($player, $kit);
            }
            if(KitManager::update($kit, $new)) {
                $player->sendMessage(LangUtils::getMessage("editkit-items-succes", true, ["{COUNT}" => count($items) + count($armor), "{NAME}" => $kit->getName()]));
                EditkitMainForm::sendTo($player, KitManager::get($kit->getName()));
            }
        });
        $menu->setListener(function (Player $player, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action) {
            if($itemClicked->getNamedTag()->hasTag("immovable", ByteTag::class)) {
                return false;
            }
            return true;
        });
        $menu->getInventory()->setContents($kit->getItems());
        for($i = 36; $i < 54; $i++) {
            switch($i) {
                case 42:
                    $item = ItemFactory::get(Item::STAINED_GLASS, 14, 1);
                    $item->setCustomName(LangUtils::getMessage("editkit-items-lockedname"));
                    $item->setNamedTagEntry(new ByteTag("immovable", 1));
                    $item->setLore([LangUtils::getMessage("editkit-items-helmet")]);
                    $menu->getInventory()->setItem($i, $item);
                    break;
                case 41:
                    $item = ItemFactory::get(Item::STAINED_GLASS, 14, 1);
                    $item->setCustomName(LangUtils::getMessage("editkit-items-lockedname"));
                    $item->setNamedTagEntry(new ByteTag("immovable", 1));
                    $item->setLore([LangUtils::getMessage("editkit-items-chestplate")]);
                    $menu->getInventory()->setItem($i, $item);
                    break;
                case 39:
                    $item = ItemFactory::get(Item::STAINED_GLASS, 14, 1);
                    $item->setCustomName(LangUtils::getMessage("editkit-items-lockedname"));
                    $item->setNamedTagEntry(new ByteTag("immovable", 1));
                    $item->setLore([LangUtils::getMessage("editkit-items-leggings")]);
                    $menu->getInventory()->setItem($i, $item);
                    break;
                case 38:
                    $item = ItemFactory::get(Item::STAINED_GLASS, 14, 1);
                    $item->setCustomName(LangUtils::getMessage("editkit-items-lockedname"));
                    $item->setNamedTagEntry(new ByteTag("immovable", 1));
                    $item->setLore([LangUtils::getMessage("editkit-items-boots")]);
                    $menu->getInventory()->setItem($i, $item);
                    break;
                case 51:
                    $menu->getInventory()->setItem($i, $kit->getArmor()[0] ?? ItemFactory::get(Item::AIR));
                    break;
                case 50:
                    $menu->getInventory()->setItem($i, $kit->getArmor()[1] ?? ItemFactory::get(Item::AIR));
                    break;
                case 48:
                    $menu->getInventory()->setItem($i, $kit->getArmor()[2] ?? ItemFactory::get(Item::AIR));
                    break;
                case 47:
                    $menu->getInventory()->setItem($i, $kit->getArmor()[3] ?? ItemFactory::get(Item::AIR));
                    break;
                default:
                    $item = ItemFactory::get(Item::STAINED_GLASS, 14, 1);
                    $item->setCustomName(LangUtils::getMessage("editkit-items-lockedname"));
                    $item->setNamedTagEntry(new ByteTag("immovable", 1));
                    $menu->getInventory()->setItem($i, $item);
                    break;
            }
        }

        $menu->send($player);
    }

}