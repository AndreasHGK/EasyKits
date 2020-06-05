<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\command;

use AndreasHGK\EasyKits\manager\CategoryManager;
use AndreasHGK\EasyKits\manager\DataManager;
use AndreasHGK\EasyKits\manager\KitManager;
use AndreasHGK\EasyKits\ui\CategorySelectForm;
use AndreasHGK\EasyKits\ui\KitSelectForm;
use AndreasHGK\EasyKits\utils\LangUtils;
use AndreasHGK\EasyKits\utils\TryClaim;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class KitCommand extends EKExecutor {

    public function __construct() {
        $this->setDataFromConfig("kit");

    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
        if(!$sender instanceof Player) {
            $sender->sendMessage(LangUtils::getMessage("sender-not-player"));
            return true;
        }

        if(!isset($args[0])) {
            if(empty(KitManager::getPermittedKitsFor($sender)) && (empty(KitManager::getAll()) && !DataManager::getKey(DataManager::CONFIG, "show-locked"))) {
                $sender->sendMessage(LangUtils::getMessage("kit-none-available"));
                return true;
            }
            if(!DataManager::getKey(DataManager::CONFIG, "use-forms")) {
                $list = implode("§7, §f", KitManager::getPermittedKitsFor($sender));
                $sender->sendMessage(LangUtils::getMessage("kit-list", true, ["{KITS}" => $list]));
                return true;
            }

            if(!empty(CategoryManager::getAll())) {
                CategorySelectForm::sendTo($sender);
            } else {
                KitSelectForm::sendTo($sender);
            }
            return true;
        }

        if(!KitManager::exists($args[0])) {
            $sender->sendMessage(LangUtils::getMessage("kit-not-found"));
            return true;
        }
        TryClaim::tryClaim(KitManager::get($args[0]), $sender);
        return true;
    }
}
