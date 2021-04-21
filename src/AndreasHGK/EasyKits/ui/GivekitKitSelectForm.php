<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\ui;

use AndreasHGK\EasyKits\manager\KitManager;
use AndreasHGK\EasyKits\utils\KitException;
use AndreasHGK\EasyKits\utils\LangUtils;
use AndreasHGK\EasyKits\utils\TryClaim;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;

class GivekitKitSelectForm {

    public static function sendTo(Player $player, Player $target) : void {

        $ui = new SimpleForm(function (Player $player, $data) use ($target) {
            if($data === null) {
                $player->sendMessage(LangUtils::getMessage("givekit-cancelled"));
                return;
            }
            if(!KitManager::exists($data)) {
                $player->sendMessage(LangUtils::getMessage("givekit-kit-not-found"));
                return;
            }
            try {
                $kit = KitManager::get($data);
                TryClaim::ForceClaim($target, $kit);
                $player->sendMessage(LangUtils::getMessage("givekit-success", true, ["{KIT}" => $kit->getName(), "{PLAYER}" => $target->getName()]));
            } catch(KitException $e) {
                switch($e->getCode()) {
                    case 3:
                        $player->sendMessage(LangUtils::getMessage("givekit-insufficient-space"));
                        break;
                    default:
                        $player->sendMessage(LangUtils::getMessage("unknown-exception"));
                        break;
                }
            }
        });

        $ui->setTitle(LangUtils::getMessage("givekit-title"));
        $ui->setContent(LangUtils::getMessage("givekit-kitselect-text"));

        foreach(KitManager::getAll() as $kit) {
            $ui->addButton(LangUtils::getMessage("givekit-kitselect-format", true, ["{NAME}" => $kit->getName()]), -1, "", $kit->getName());
        }


        $player->sendForm($ui);
    }

}
