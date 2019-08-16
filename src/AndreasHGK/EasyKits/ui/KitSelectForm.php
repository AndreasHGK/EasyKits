<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\ui;

use AndreasHGK\EasyKits\command\KitCommand;
use AndreasHGK\EasyKits\manager\DataManager;
use AndreasHGK\EasyKits\manager\KitManager;
use AndreasHGK\EasyKits\utils\LangUtils;
use AndreasHGK\EasyKits\utils\TryClaim;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;

class KitSelectForm {

    public static function sendTo(Player $player): void
    {
        $ui = new SimpleForm(function (Player $player, $data){
            if($data === null){
                return;
            }
            if(!KitManager::exists($data)){
                $player->sendMessage(LangUtils::getMessage("kit-not-found"));
                return;
            }
            TryClaim::tryClaim(KitManager::get($data), $player);
        });
        $ui->setTitle(LangUtils::getMessage("kit-title"));
        $ui->setContent(LangUtils::getMessage("kit-text"));

        foreach(KitManager::getPermittedKitsFor($player) as $kit) {
            if ($kit->getPrice() > 0) {
                $ui->addButton(LangUtils::getMessage("kit-available-priced-format", true, ["{NAME}" => $kit->getName(), "{PRICE}" => $kit->getPrice()]), -1, "", $kit->getName());
            } else {
                $ui->addButton(LangUtils::getMessage("kit-available-free-format", true, ["{NAME}" => $kit->getName()]), -1, "", $kit->getName());
            }
        }
        if(DataManager::getKey(DataManager::CONFIG, "show-locked")){

            foreach(array_diff_key(KitManager::getAll(), KitManager::getPermittedKitsFor($player)) as $kit) {
                $ui->addButton(LangUtils::getMessage("kit-locked-format", true, ["{NAME}" => $kit->getName(), "{PRICE}" => $kit->getPrice()]), -1, "", $kit->getName());
            }
        }
        $player->sendForm($ui);
    }

}