<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\command;

use AndreasHGK\EasyKits\CooldownManager;
use AndreasHGK\EasyKits\DataManager;
use AndreasHGK\EasyKits\EasyKits;
use AndreasHGK\EasyKits\Kit;
use AndreasHGK\EasyKits\KitManager;
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
            if(!DataManager::getKey(DataManager::CONFIG, "use-forms")){
                $list = implode("§7, §f", KitManager::getPermittedKitsFor($sender));
                $sender->sendMessage(LangUtils::getMessage("kit-list", true, ["{KITS}" => $list]));
                return true;
            }

            $ui = new SimpleForm(function (Player $player, $data){
                if($data === null){
                    return;
                }
                if(!KitManager::exists($data)){
                    $player->sendMessage(LangUtils::getMessage("kit-not-found"));
                    return;
                }
                $this->tryClaim(KitManager::get($data), $player);
            });
            $ui->setTitle(LangUtils::getMessage("kit-title"));
            $ui->setContent(LangUtils::getMessage("kit-text"));

            foreach(KitManager::getPermittedKitsFor($sender) as $kit) {
                if ($kit->getPrice() > 0) {
                    $ui->addButton(LangUtils::getMessage("kit-available-priced-format", true, ["{NAME}" => $kit->getName(), "{PRICE}" => $kit->getPrice()]), -1, "", $kit->getName());
                } else {
                    $ui->addButton(LangUtils::getMessage("kit-available-free-format", true, ["{NAME}" => $kit->getName()]), -1, "", $kit->getName());
                }
            }
            if(DataManager::getKey(DataManager::CONFIG, "show-locked")){
                foreach(KitManager::getAll() - KitManager::getPermittedKitsFor($sender) as $kit) {
                    $ui->addButton(LangUtils::getMessage("kit-locked-format", true, ["{NAME}" => $kit->getName(), "{PRICE}" => $kit->getPrice()]), -1, "", $kit->getName());
                }
            }
            $sender->sendForm($ui);
            return true;
        }

        if(!KitManager::exists($args[0])){
            $sender->sendMessage(LangUtils::getMessage("kit-not-found"));
            return true;
        }
        $this->tryClaim(KitManager::get($args[0]), $sender);
        return true;
    }

    public function tryClaim(Kit $kit, Player $player) : void {
        try{
            if($kit->claimFor($player)) $player->sendMessage(LangUtils::getMessage("kit-claim-success", true, ["{NAME}" => $kit->getName()]));

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
