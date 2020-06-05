<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\ui;

use AndreasHGK\EasyKits\Category;
use AndreasHGK\EasyKits\manager\CategoryManager;
use AndreasHGK\EasyKits\utils\LangUtils;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\Player;

class CreatecategoryForm {

    public static function sendTo(Player $player) : void {
        $ui = new CustomForm(function (Player $player, $data) {
            if($data === null) {
                $player->sendMessage(LangUtils::getMessage("createcategory-cancelled"));
                return;
            }
            if(!isset($data["name"])) {
                $player->sendMessage(LangUtils::getMessage("createcategory-no-name"));
                return;
            }

            $name = (string)$data["name"];

            if(CategoryManager::exists($name)) {
                $player->sendMessage(LangUtils::getMessage("createcategory-duplicate"));
                return;
            }

            $locked = $data["locked"];
            $category = new Category($name);
            $category->setLocked($locked);
            if(CategoryManager::add($category)) {
                $player->sendMessage(LangUtils::getMessage("createcategory-success", true, ["{NAME}" => $name]));
                CategoryManager::saveAll();
            }
            return;
        });
        $ui->setTitle(LangUtils::getMessage("createcategory-title"));
        $ui->addLabel(LangUtils::getMessage("createcategory-text"));
        $ui->addInput(LangUtils::getMessage("createcategory-name"), "", null, "name");
        $ui->addLabel(LangUtils::getMessage("createcategory-flags"));
        $ui->addToggle(LangUtils::getMessage("createcategory-lockedToggle"), false, "locked");
        $player->sendForm($ui);
    }

}