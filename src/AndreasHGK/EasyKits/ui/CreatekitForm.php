<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\ui;

use AndreasHGK\EasyKits\Kit;
use AndreasHGK\EasyKits\manager\CategoryManager;
use AndreasHGK\EasyKits\manager\DataManager;
use AndreasHGK\EasyKits\manager\KitManager;
use AndreasHGK\EasyKits\utils\LangUtils;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class CreatekitForm {

    public static function sendTo(Player $player) : void {
        $categories = [
            TextFormat::colorize("/"),
        ];
        foreach(CategoryManager::getAll() as $category) {
            $categories[] = TextFormat::colorize($category->getName());
        }

        $ui = new CustomForm(function (Player $player, $data) use ($categories) {
            if($data === null) {
                $player->sendMessage(LangUtils::getMessage("createkit-cancelled"));
                return;
            }
            if(!isset($data["name"])) {
                $player->sendMessage(LangUtils::getMessage("createkit-no-name"));
                return;
            }

            $name = (string)$data["name"];

            if(KitManager::exists($name)) {
                $player->sendMessage(LangUtils::getMessage("createkit-duplicate"));
                return;
            }

            if(!isset($data["price"])) {
                $price = 0;
            } elseif(!is_numeric((float)$data["price"])) {
                $player->sendMessage(LangUtils::getMessage("createkit-invalid-price"));
                return;
            } else {
                $price = (float)$data["price"];
            }

            if(!isset($data["cooldown"])) {
                $cooldown = 0;
            } elseif(!is_numeric((float)$data["cooldown"])) {
                //todo: date format to time
                $player->sendMessage(LangUtils::getMessage("createkit-invalid-cooldown"));
                return;
            } else {
                $cooldown = (int)$data["cooldown"];
            }

            $locked = $data["locked"];
            $emptyOnClaim = $data["emptyOnClaim"];
            $doOverride = $data["doOverride"];
            $doOverrideArmor = $data["doOverrideArmor"];
            $alwaysClaim = $data["alwaysClaim"];
            $chestKit = $data["chestKit"];

            $items = $player->getInventory()->getContents();
            $armor = $player->getArmorInventory()->getContents();

            $permission = $data["permission"] ?? $name;

            if(empty($items) && empty($armor)) {
                $player->sendMessage(LangUtils::getMessage("createkit-empty-inventory"));
                return;
            }

            $kit = new Kit($name, $permission, $price, $cooldown, $items, $armor);
            $kit->setLocked($locked);
            $kit->setEmptyOnClaim($emptyOnClaim);
            $kit->setDoOverride($doOverride);
            $kit->setDoOverrideArmor($doOverrideArmor);
            $kit->setAlwaysClaim($alwaysClaim);
            $kit->setChestKit($chestKit);

            if(isset($data["category"]) && $data["category"] !== 0) {
                $categoryName = $categories[$data["category"]];
                if(!CategoryManager::exists($categoryName)) {
                    $player->sendMessage(LangUtils::getMessage("createkit-invalid-category"));
                    return;
                }
                $old = CategoryManager::get($categoryName);
                $category = clone $old;
                $category->addKit($kit);
                CategoryManager::update($old, $category);
                CategoryManager::saveAll();
            }

            if(KitManager::add($kit)) {
                $player->sendMessage(LangUtils::getMessage("createkit-success", true, ["{NAME}" => $name]));
                KitManager::saveAll();
            }
            return;
        });
        $ui->setTitle(LangUtils::getMessage("createkit-title"));
        $ui->addLabel(LangUtils::getMessage("createkit-text"));
        $ui->addInput(LangUtils::getMessage("createkit-kitname"), "", null, "name");
        $ui->addInput(LangUtils::getMessage("createkit-permission"), LangUtils::getMessage("createkit-permission-tip"), null, "permission");
        $ui->addInput(LangUtils::getMessage("createkit-price"), "", "0", "price");
        $ui->addInput(LangUtils::getMessage("createkit-cooldown"), "", "60", "cooldown");
        if(!empty(CategoryManager::getAll())) {
            $ui->addDropdown(LangUtils::getMessage("createkit-category"), $categories, 0, "category");
        }
        $ui->addLabel(LangUtils::getMessage("createkit-flags"));
        $ui->addToggle(LangUtils::getMessage("createkit-lockedToggle"), DataManager::getKey(DataManager::CONFIG, "default-flags")["locked"], "locked");
        $ui->addToggle(LangUtils::getMessage("createkit-emptyOnClaimToggle"), DataManager::getKey(DataManager::CONFIG, "default-flags")["emptyOnClaim"], "emptyOnClaim");
        $ui->addToggle(LangUtils::getMessage("createkit-doOverrideToggle"), DataManager::getKey(DataManager::CONFIG, "default-flags")["doOverride"], "doOverride");
        $ui->addToggle(LangUtils::getMessage("createkit-doOverrideArmorToggle"), DataManager::getKey(DataManager::CONFIG, "default-flags")["doOverrideArmor"], "doOverrideArmor");
        $ui->addToggle(LangUtils::getMessage("createkit-alwaysClaimToggle"), DataManager::getKey(DataManager::CONFIG, "default-flags")["alwaysClaim"], "alwaysClaim");
        $ui->addToggle(LangUtils::getMessage("createkit-chestKitToggle"), DataManager::getKey(DataManager::CONFIG, "default-flags")["chestKit"], "chestKit");
        $player->sendForm($ui);
    }

}