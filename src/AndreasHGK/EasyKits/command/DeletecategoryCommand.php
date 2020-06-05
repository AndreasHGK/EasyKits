<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\command;

use AndreasHGK\EasyKits\ui\DeletecategoryForm;
use AndreasHGK\EasyKits\utils\LangUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class DeletecategoryCommand extends EKExecutor {

    public function __construct() {
        $this->setDataFromConfig("deletecategory");

    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
        if(!$sender instanceof Player) {
            $sender->sendMessage(LangUtils::getMessage("sender-not-player"));
            return true;
        }
        DeletecategoryForm::sendTo($sender);
        return true;
    }

}
