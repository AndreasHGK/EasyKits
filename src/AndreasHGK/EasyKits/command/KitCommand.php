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
        self::tryClaim(KitManager::get($args[0]), $sender);
        return true;
    }

    public static function tryClaim(Kit $kit, Player $player) : void {

        try{
            if($kit->claim($player)) $player->sendMessage(LangUtils::getMessage("kit-claim-success", true, ["{NAME}" => $kit->getName()]));

        }catch(KitException $e){
            switch ($e->getCode()){
                case 0:
                    $time = CooldownManager::getKitCooldown($kit, $player);
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
                    $player->sendMessage(LangUtils::getMessage("kit-cooldown-active", true, ["{TIME}" => $timeString]));
                    break;
                case 1:
                    $player->sendMessage(LangUtils::getMessage("kit-insufficient-funds"));
                    break;
                case 2:
                    $player->sendMessage(LangUtils::getMessage("no-economy"));
                    break;
                case 3:
                    $player->sendMessage(LangUtils::getMessage("kit-insufficient-space"));
                    break;
                case 4:
                    $player->sendMessage(LangUtils::getMessage("kit-no-permission"));
                    break;
                default:
                    $player->sendMessage(LangUtils::getMessage("unknown-exception"));
                    break;
            }
        }
    }

}
