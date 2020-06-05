<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\ui;

use AndreasHGK\EasyKits\Kit;
use AndreasHGK\EasyKits\manager\CategoryManager;
use AndreasHGK\EasyKits\utils\LangUtils;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\Player;

class EditkitCategoryForm {

    public static function sendTo(Player $player, Kit $kit) : void {

        $ui = new CustomForm(function (Player $player, $data) use ($kit) {
            if($data === null) {
                EditkitMainForm::sendTo($player, $kit);
                return;
            }
            $success = 0;
            $removed = 0;
            foreach($data as $name => $enabled) {
                if(!is_string($name)) continue;
                $old = CategoryManager::get($name);
                if($enabled) {
                    $category = clone $old;
                    $category->addKit($kit);
                    if(CategoryManager::update($old, $category)) $success++;
                } else {
                    $category = clone $old;
                    if($category->hasKit($kit)) {
                        $category->removeKit($kit);
                        if(CategoryManager::update($old, $category)) $removed++;
                    }
                }
            }
            $player->sendMessage(LangUtils::getMessage("editkit-category-success", true, ["{NAME}" => $kit->getName(), "{ADDED}" => $success, "{REMOVED}" => $removed]));
            return;
        });
        $ui->setTitle(LangUtils::getMessage("editkit-title"));
        $ui->addLabel(LangUtils::getMessage("editkit-category-text"));

        $ui->addLabel(LangUtils::getMessage("editkit-general-categories"));

        foreach(CategoryManager::getAll() as $category) {
            $ui->addToggle(LangUtils::getMessage("editkit-category-format", true, ["{NAME}" => $category->getName()]), $category->hasKit($kit), $category->getName());
        }

        $player->sendForm($ui);
    }

}