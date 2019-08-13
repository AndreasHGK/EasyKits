<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\command;

use AndreasHGK\EasyKits\CooldownManager;
use AndreasHGK\EasyKits\DataManager;
use AndreasHGK\EasyKits\EasyKits;
use AndreasHGK\EasyKits\KitManager;
use AndreasHGK\EasyKits\utils\KitException;
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
            $sender->sendMessage(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "sender-not-player")));
            return true;
        }


        if(!isset($args[0])){
            $list = implode("§7, §f", KitManager::getPermittedKitsFor($sender));
            $sender->sendMessage(TextFormat::colorize(str_replace("{KITS}", $list, DataManager::getKey(DataManager::LANG, "kit-list"))));
            return true;
        }

        if(!KitManager::exists($args[0])){
            $sender->sendMessage(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "kit-not-found")));
            return true;
        }
        try{
            KitManager::get($args[0])->claimFor($sender);
            $sender->sendMessage(TextFormat::colorize(str_replace("{NAME}", KitManager::get($args[0])->getName(), DataManager::getKey(DataManager::LANG, "kit-claim-success"))));
        }catch(KitException $e){
            switch ($e->getCode()){
                case 0:
                    $time = CooldownManager::getKitCooldown(KitManager::get($args[0]), $sender);
                    $timeString = "";
                    $timeArray = [];
                    if($time >= 86400){
                        $unit = floor($time/86400);
                        $time -= $unit*86400;
                        $timeArray[] = $unit." days";
                    }
                    if($time >= 3600){
                        $unit = floor($time/3600);
                        $time -= $unit*3600;
                        $timeArray[] = $unit." hours";
                    }
                    if($time >= 60){
                        $unit = floor($time/60);
                        $time -= $unit*60;
                        $timeArray[] = $unit." minutes";
                    }
                    if($time >= 1){
                        $timeArray[] = $time." seconds";
                    }
                    foreach($timeArray as $key => $value){
                        if($key === 0){
                            $timeString .= $value;
                        }elseif ($key === count($timeArray) - 1){
                            $timeString .= " and ".$value;
                        }else{
                            $timeString .= ", ".$value;
                        }
                    }
                    $sender->sendMessage(TextFormat::colorize(str_replace("{TIME}", $timeString, DataManager::getKey(DataManager::LANG, "kit-cooldown-active"))));
                    break;
                case 1:
                    $sender->sendMessage(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "kit-insufficient-funds")));
                    break;
                case 2:
                    $sender->sendMessage(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "no-economy")));
                    break;
                case 3:
                    $sender->sendMessage(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "kit-insufficient-space")));
                    break;
                default:
                    $sender->sendMessage(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "unknown-exception")));
                    break;
            }
        }
        return true;
    }

}
