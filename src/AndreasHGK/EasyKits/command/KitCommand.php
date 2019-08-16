<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\command;

use AndreasHGK\EasyKits\EasyKits;
use AndreasHGK\EasyKits\Kit;
use AndreasHGK\EasyKits\manager\CooldownManager;
use AndreasHGK\EasyKits\manager\DataManager;
use AndreasHGK\EasyKits\manager\KitManager;
use AndreasHGK\EasyKits\ui\KitSelectForm;
use AndreasHGK\EasyKits\utils\KitException;
use AndreasHGK\EasyKits\utils\LangUtils;
use AndreasHGK\EasyKits\utils\TryClaim;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;

class KitCommand extends EKExecutor {

    public function __construct()
    {
        $commandData = DataManager::getKey(DataManager::COMMANDS, "kit");
        $this->name = array_shift($commandData["labels"]);
        if(isset($commandData["labels"])) $this->aliases = $commandData["labels"];
        $this->desc = $commandData["description"];
        $this->usage = $commandData["usage"];
        $this->permission = EasyKits::PERM_ROOT."command.kit";
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if(!$sender instanceof Player){
            $sender->sendMessage(LangUtils::getMessage("sender-not-player"));
            return true;
        }

        if(!isset($args[0])){
            if(empty(KitManager::getPermittedKitsFor($sender)) && (empty(KitManager::getAll()) && !DataManager::getKey(DataManager::CONFIG, "show-locked"))){
                $sender->sendMessage(LangUtils::getMessage("kit-none-available"));
                return true;
            }
            if(!DataManager::getKey(DataManager::CONFIG, "use-forms")){
                $list = implode("§7, §f", KitManager::getPermittedKitsFor($sender));
                $sender->sendMessage(LangUtils::getMessage("kit-list", true, ["{KITS}" => $list]));
                return true;
            }

            KitSelectForm::sendTo($sender);
            return true;
        }

        if(!KitManager::exists($args[0])){
            $sender->sendMessage(LangUtils::getMessage("kit-not-found"));
            return true;
        }
        TryClaim::tryClaim(KitManager::get($args[0]), $sender);
        return true;
    }

}
