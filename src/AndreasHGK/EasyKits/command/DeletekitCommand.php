<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\command;

use AndreasHGK\EasyKits\DataManager;
use AndreasHGK\EasyKits\EasyKits;
use AndreasHGK\EasyKits\Kit;
use AndreasHGK\EasyKits\KitManager;
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
            $sender->sendMessage(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "sender-not-player")));
            return true;
        }

        if(isset($args[0])){
            if(!KitManager::exists((string)$args[0])){
                $sender->sendMessage(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "deletekit-not-found")));
                return true;
            }
            KitManager::remove((string)$args[0]);
            $sender->sendMessage(TextFormat::colorize(str_replace("{NAME}", (string)$args[0], DataManager::getKey(DataManager::LANG, "deletekit-success"))));
            return true;
        }

        $kits = [];
        foreach(KitManager::getAll() as $kit){
            $kits[] = $kit->getName();
        }

        $ui = new CustomForm(function(Player $player, $data) use($kits){
            if($data === null){
                $player->sendMessage(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "deletekit-cancelled")));
                return;
            }
            if(!isset($data["kit"])){
                $player->sendMessage(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "deletekit-empty")));
                return;
            }
            if(!KitManager::exists($kits[$data["kit"]])){
                $player->sendMessage(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "deletekit-not-found")));
                return;
            }
            KitManager::remove($kits[$data["kit"]]);
            $player->sendMessage(TextFormat::colorize(str_replace("{NAME}", $kits[$data["kit"]], DataManager::getKey(DataManager::LANG, "deletekit-success"))));
            return;
        });
        $ui->setTitle(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "deletekit-title")));
        $ui->addLabel(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "deletekit-text")));
        $ui->addDropdown(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "deletekit-text")), $kits, null, "kit");
        $sender->sendForm($ui);
        return true;
    }

}
