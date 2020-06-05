<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\command;

use AndreasHGK\EasyKits\manager\KitManager;
use AndreasHGK\EasyKits\ui\EditkitSelectForm;
use AndreasHGK\EasyKits\utils\LangUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class EditkitCommand extends EKExecutor {

    public function __construct() {
        $this->setDataFromConfig("editkit");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
        if(!$sender instanceof Player) {
            $sender->sendMessage(LangUtils::getMessage("sender-not-player"));
            return true;
        }

        if(empty(KitManager::getAll())) {
            $sender->sendMessage(LangUtils::getMessage("editkit-none-available"));
            return true;
        }

        if(isset($args[0])) {
            $kitname = $args[0];
            if(!KitManager::exists($kitname)) {
                $sender->sendMessage(LangUtils::getMessage("editkit-not-found"));
                return true;
            }
        }

        EditkitSelectForm::sendTo($sender);
        return true;
    }

}