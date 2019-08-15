<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\command;

use AndreasHGK\EasyKits\EasyKits;
use AndreasHGK\EasyKits\Kit;
use AndreasHGK\EasyKits\manager\DataManager;
use AndreasHGK\EasyKits\manager\KitManager;
use AndreasHGK\EasyKits\ui\DeletekitForm;
use AndreasHGK\EasyKits\utils\LangUtils;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\command\CommandExecutor;
use pocketmine\Player;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;

class DeletekitCommand extends EKExecutor {

    public function __construct()
    {
        $commandData = DataManager::getKey(DataManager::COMMANDS, "deletekit");
        $this->name = array_shift($commandData["labels"]);
        if(isset($commandData["labels"])) $this->aliases = $commandData["labels"];
        $this->desc = $commandData["description"];
        $this->usage = $commandData["usage"];
        $this->permission = EasyKits::PERM_ROOT."command.deletekit";
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if(!$sender instanceof Player){
            $sender->sendMessage(LangUtils::getMessage("sender-not-player"));
            return true;
        }

        if(isset($args[0])){
            if(!KitManager::exists((string)$args[0])){
                $sender->sendMessage(LangUtils::getMessage("deletekit-not-found"));
                return true;
            }
            if(KitManager::remove(KitManager::get((string)$args[0]))) $sender->sendMessage(LangUtils::getMessage("deletekit-success", true, ["{NAME}" => (string)$args[0]]));
            return true;
        }

        $kits = [];
        foreach(KitManager::getAll() as $kit){
            $kits[] = $kit->getName();
        }

        if(empty($kits)){
            $sender->sendMessage(LangUtils::getMessage("deletekit-none-available"));
            return true;
        }

        DeletekitForm::sendTo($sender);
        return true;
    }

}
