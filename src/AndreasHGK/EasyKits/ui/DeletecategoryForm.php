<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\ui;

use AndreasHGK\EasyKits\manager\CategoryManager;
use AndreasHGK\EasyKits\utils\LangUtils;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\Player;

class DeletecategoryForm {

    public static function sendTo(Player $player) : void {
        $categories = [];
        foreach(CategoryManager::getAll() as $category) {
            $categories[] = $category->getName();
        }

        $ui = new CustomForm(function (Player $player, $data) use ($categories) {
            if($data === null) {
                $player->sendMessage(LangUtils::getMessage("deletecategory-cancelled"));
                return;
            }
            if(!isset($data["category"])) {
                $player->sendMessage(LangUtils::getMessage("deletecategory-empty"));
                return;
            }
            if(!CategoryManager::exists($categories[$data["category"]])) {
                $player->sendMessage(LangUtils::getMessage("deletecategory-not-found"));
                return;
            }
            if(CategoryManager::remove(CategoryManager::get($categories[$data["category"]]))) {
                $player->sendMessage(LangUtils::getMessage("deletecategory-success", true, ["{NAME}" => $categories[$data["category"]]]));
                CategoryManager::saveAll();
            }
            return;
        });
        $ui->setTitle(LangUtils::getMessage("deletecategory-title"));
        $ui->addLabel(LangUtils::getMessage("deletecategory-text"));
        $ui->addDropdown(LangUtils::getMessage("deletecategory-select"), $categories, null, "category");
        $player->sendForm($ui);
    }

}