<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\ui;

use AndreasHGK\EasyKits\event\CategorySelectEvent;
use AndreasHGK\EasyKits\manager\CategoryManager;
use AndreasHGK\EasyKits\manager\DataManager;
use AndreasHGK\EasyKits\utils\LangUtils;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;

class CategorySelectForm {

    public static function sendTo(Player $player) : void {
        if(empty(CategoryManager::getPermittedCategoriesFor($player))) {
            $player->sendMessage(LangUtils::getMessage("no-categories"));
            return;
        }

        $ui = new SimpleForm(function (Player $player, $data) {
            if($data === null) {
                return;
            }
            $category = CategoryManager::get($data);
            if(!isset($category)) {
                $player->sendMessage(LangUtils::getMessage("category-not-found"));
                return;
            }
            if(!$category->hasPermission($player)) {
                $player->sendMessage(LangUtils::getMessage("category-no-permission"));
                return;
            }

            $event = new CategorySelectEvent($player, $category);
            $event->call();

            if($event->isCancelled()) return;

            if(empty($event->getCategory()->getPermittedKitsFor($player))) {
                $player->sendMessage(LangUtils::getMessage("category-empty"));
                return;
            }

            KitSelectForm::sendTo($event->getPlayer(), $event->getCategory());
        });
        $ui->setTitle(LangUtils::getMessage("category-select-title"));
        $ui->setContent(LangUtils::getMessage("category-select-text"));

        foreach(CategoryManager::getAll() as $category) {
            if($category->hasPermission($player)) {
                $ui->addButton(LangUtils::getMessage("category-unlocked-format", true, ["{NAME}" => $category->getName()]), -1, "", $category->getName());
            } elseif(DataManager::getKey(DataManager::CONFIG, "show-locked-categories")) {
                $ui->addButton(LangUtils::getMessage("category-locked-format", true, ["{NAME}" => $category->getName()]), -1, "", $category->getName());
            }
        }

        $player->sendForm($ui);
    }

}