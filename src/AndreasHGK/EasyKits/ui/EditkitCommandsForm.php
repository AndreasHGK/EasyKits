<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\ui;

use AndreasHGK\EasyKits\Kit;
use AndreasHGK\EasyKits\manager\KitManager;
use AndreasHGK\EasyKits\utils\LangUtils;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\Player;

class EditkitCommandsForm {

    public static function sendTo(Player $player, Kit $kit) : void {

        $ui = new CustomForm(function (Player $player, $data) use ($kit) {
            if($data === null) {
                EditkitMainForm::sendTo($player, $kit);
                return;
            }

            $commands = [];
            foreach($data as $command) {
                if($command !== "" && $command !== null) $commands[] = $command;
            }

            $new = clone $kit;
            $new->setCommands($commands);

            if(KitManager::update($kit, $new)) {
                KitManager::saveAll();

                $player->sendMessage(LangUtils::getMessage("editkit-commands-success", true, [
                    "{COUNT}" => count($commands),
                    "{NAME}" => $kit->getName(),
                ]));
            }
            EditkitCommandsForm::sendTo($player, KitManager::get($kit->getName()));
            return;
        });
        $ui->setTitle(LangUtils::getMessage("editkit-title"));
        $ui->addLabel(LangUtils::getMessage("editkit-commands-text", true, [
            "{NAME}" => $kit->getName(),
        ]));

        $int = 0;
        foreach($kit->getCommands() as $int => $command) {
            $ui->addInput(LangUtils::getMessage("editkit-commands-input", true, ["{NUMBER}" => $int + 1]), "", $command);
        }
        $ui->addInput(LangUtils::getMessage("editkit-commands-input", true, ["{NUMBER}" => $int + 1]));

        $player->sendForm($ui);
    }

}