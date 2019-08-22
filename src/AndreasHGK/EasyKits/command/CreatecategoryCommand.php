<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\command;

use AndreasHGK\EasyKits\EasyKits;
use AndreasHGK\EasyKits\Kit;
use AndreasHGK\EasyKits\manager\DataManager;
use AndreasHGK\EasyKits\manager\KitManager;
use AndreasHGK\EasyKits\ui\CreatekitForm;
use AndreasHGK\EasyKits\utils\LangUtils;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\Player;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

class CreatecategoryCommand extends EKExecutor {

    public function __construct()
    {
        $this->setDataFromConfig("createcategory");

    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if(!$sender instanceof Player){
            $sender->sendMessage(LangUtils::getMessage("sender-not-player"));
            return true;
        }
        CreatekitForm::sendTo($sender);
        return true;
    }

}
