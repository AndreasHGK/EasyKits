<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\command;

use AndreasHGK\EasyKits\DataManager;
use AndreasHGK\EasyKits\EasyKits;
use AndreasHGK\EasyKits\Kit;
use AndreasHGK\EasyKits\KitManager;
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

        $ui = new CustomForm(function(Player $player, $data) use($kits){
            if($data === null){
                $player->sendMessage(LangUtils::getMessage("deletekit-cancelled"));
                return;
            }
            if(!isset($data["kit"])){
                $player->sendMessage(LangUtils::getMessage("deletekit-empty"));
                return;
            }
            if(!KitManager::exists($kits[$data["kit"]])){
                $player->sendMessage(LangUtils::getMessage("deletekit-not-found"));
                return;
            }
            KitManager::remove($kits[$data["kit"]]);
            $player->sendMessage(LangUtils::getMessage("deletekit-success", true, ["{NAME}" => $kits[$data["kit"]]]));
            return;
        });
        $ui->setTitle(LangUtils::getMessage("deletekit-title"));
        $ui->addLabel(LangUtils::getMessage("deletekit-text"));
        $ui->addDropdown(LangUtils::getMessage("deletekit-select"), $kits, null, "kit");
        $sender->sendForm($ui);
        return true;
    }

}
