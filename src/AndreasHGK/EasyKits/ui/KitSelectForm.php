<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\ui;

use AndreasHGK\EasyKits\Category;
use AndreasHGK\EasyKits\manager\CooldownManager;
use AndreasHGK\EasyKits\manager\DataManager;
use AndreasHGK\EasyKits\manager\KitManager;
use AndreasHGK\EasyKits\utils\LangUtils;
use AndreasHGK\EasyKits\utils\TimeUtils;
use AndreasHGK\EasyKits\utils\TryClaim;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;

class KitSelectForm {

    public static function sendTo(Player $player, Category $category = null) : void {

        $ui = new SimpleForm(function (Player $player, $data) use ($category) {
            if($data === null) {
                if($category !== null) CategorySelectForm::sendTo($player);
                return;
            }
            if(!KitManager::exists($data)) {
                $player->sendMessage(LangUtils::getMessage("kit-not-found"));
                return;
            }
            TryClaim::tryClaim(KitManager::get($data), $player);
        });

        if($category !== null) {
            $ui->setTitle(LangUtils::getMessage("category-title", true, ["{NAME}" => $category->getName()]));
            $ui->setContent(LangUtils::getMessage("category-text"));

            foreach($category->getPermittedKitsFor($player) as $kit) {
                if(CooldownManager::hasKitCooldown($kit, $player)) {
                    $ui->addButton(LangUtils::getMessage("kit-cooldown-format", true, ["{NAME}" => $kit->getName(), "{PRICE}" => $kit->getPrice(), "{COOLDOWN}" => $timeString = TimeUtils::intToTimeString(CooldownManager::getKitCooldown($kit, $player))]), -1, "", $kit->getName());
                } elseif($kit->getPrice() > 0) {
                    $ui->addButton(LangUtils::getMessage("kit-available-priced-format", true, ["{NAME}" => $kit->getName(), "{PRICE}" => $kit->getPrice()]), -1, "", $kit->getName());
                } else {
                    $ui->addButton(LangUtils::getMessage("kit-available-free-format", true, ["{NAME}" => $kit->getName()]), -1, "", $kit->getName());
                }
            }
            if(DataManager::getKey(DataManager::CONFIG, "show-locked")) {
                foreach($category->getKits() as $kit) {
                    if($kit->hasPermission($player)) continue;
                    $ui->addButton(LangUtils::getMessage("kit-locked-format", true, ["{NAME}" => $kit->getName(), "{PRICE}" => $kit->getPrice()]), -1, "", $kit->getName());
                }
            }
        } else {
            $ui->setTitle(LangUtils::getMessage("kit-title"));
            $ui->setContent(LangUtils::getMessage("kit-text"));

            foreach(KitManager::getPermittedKitsFor($player) as $kit) {
                if(CooldownManager::hasKitCooldown($kit, $player)) {
                    $ui->addButton(LangUtils::getMessage("kit-cooldown-format", true, ["{NAME}" => $kit->getName(), "{PRICE}" => $kit->getPrice(), "{COOLDOWN}" => $timeString = TimeUtils::intToTimeString(CooldownManager::getKitCooldown($kit, $player))]), -1, "", $kit->getName());
                } elseif($kit->getPrice() > 0) {
                    $ui->addButton(LangUtils::getMessage("kit-available-priced-format", true, ["{NAME}" => $kit->getName(), "{PRICE}" => $kit->getPrice()]), -1, "", $kit->getName());
                } else {
                    $ui->addButton(LangUtils::getMessage("kit-available-free-format", true, ["{NAME}" => $kit->getName()]), -1, "", $kit->getName());
                }
            }
            // locked kits should be displayed below unlocked ones
            if(DataManager::getKey(DataManager::CONFIG, "show-locked")) {

                foreach(KitManager::getAll() as $kit) {
                    if($kit->hasPermission($player)) continue;
                    $ui->addButton(LangUtils::getMessage("kit-locked-format", true, ["{NAME}" => $kit->getName(), "{PRICE}" => $kit->getPrice()]), -1, "", $kit->getName());
                }
            }
        }

        $player->sendForm($ui);
    }

}