<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\ui;

use AndreasHGK\EasyKits\utils\LangUtils;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use pocketmine\Server;

class GivekitPlayerSelectForm {

    public static function sendTo(Player $player) : void {

        $ui = new SimpleForm(function (Player $player, $data) {
            if($data === null) {
                $player->sendMessage(LangUtils::getMessage("givekit-cancelled"));
                return;
            }
            $target = Server::getInstance()->getPlayer($data);
            if($target === null) {
                $player->sendMessage(LangUtils::getMessage("givekit-player-not-found"));
                return;
            }
            GivekitKitSelectForm::sendTo($player, $target);
        });

        $ui->setTitle(LangUtils::getMessage("givekit-title"));
        $ui->setContent(LangUtils::getMessage("givekit-playerselect-text"));

        foreach(Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
            $ui->addButton(LangUtils::getMessage("givekit-playerselect-format", true, ["{PLAYER}" => $onlinePlayer->getName()]), -1, "", $onlinePlayer->getName());
        }


        $player->sendForm($ui);
    }

}