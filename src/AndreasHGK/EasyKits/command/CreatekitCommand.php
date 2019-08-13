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
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;

class CreatekitCommand extends EKExecutor {

    public function __construct()
    {
        $commandData = DataManager::getKey(DataManager::COMMANDS, "createkit");
        $this->name = array_shift($commandData["labels"]);
        if(isset($commandData["labels"])) $this->aliases = $commandData["labels"];
        $this->desc = $commandData["description"];
        $this->usage = $commandData["usage"];
        $this->permission = EasyKits::PERM_ROOT."command.createkit";
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if(!$sender instanceof Player){
            $sender->sendMessage(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "sender-not-player")));
            return true;
        }

        $ui = new CustomForm(function(Player $player, $data){
            if($data === null){
                $player->sendMessage(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "createkit-cancelled")));
                return;
            }
            if(!isset($data["name"])){
                $player->sendMessage(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "createkit-no-name")));
                return;
            }

            $name = $data["name"];

            if(KitManager::exists($name)){
                $player->sendMessage(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "createkit-duplicate")));
                return;
            }

            if(!isset($data["price"])){
                $price = 0;
            }elseif(!is_numeric((float)$data["price"])){
                $player->sendMessage(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "createkit-invalid-price")));
                return;
            }else{
                $price = (float)$data["price"];
            }

            if(!isset($data["cooldown"])){
                $cooldown = 0;
            }elseif(!is_numeric((float)$data["cooldown"])){
                //todo: date format to time
                $player->sendMessage(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "createkit-invalid-cooldown")));
                return;
            }else{
                $cooldown = (int)$data["cooldown"];
            }

            $locked = $data["locked"];
            $emptyOnClaim = $data["emptyOnClaim"];
            $doOverride = $data["doOverride"];
            $doOverrideArmor = $data["doOverrideArmor"];
            $alwaysClaim = $data["alwaysClaim"];

            $items = $player->getInventory()->getContents();
            $armor = $player->getArmorInventory()->getContents();
            if(empty($items) && empty($armor)){
                $player->sendMessage(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "createkit-empty-inventory")));
                return;
            }

            $kit = new Kit($name, $price, $cooldown, $items, $armor);
            $kit->setLocked($locked);
            $kit->setEmptyOnClaim($emptyOnClaim);
            $kit->setDoOverride($doOverride);
            $kit->setDoOverrideArmor($doOverrideArmor);
            $kit->setAlwaysClaim($alwaysClaim);

            KitManager::add($kit);
            KitManager::saveAll();
            $player->sendMessage(TextFormat::colorize(str_replace("{NAME}", $name, DataManager::getKey(DataManager::LANG, "createkit-success"))));
            return;
        });
        $ui->setTitle(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "createkit-title")));
        $ui->addLabel(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "createkit-text")));
        $ui->addInput(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "createkit-kitname")), "", null, "name");
        $ui->addInput(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "createkit-price")), "", "0", "price");
        $ui->addInput(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "createkit-cooldown")), "", "60", "cooldown");
        $ui->addLabel(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "createkit-flags")));
        $ui->addToggle(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "createkit-lockedToggle")), DataManager::getKey(DataManager::CONFIG, "default-flags")["locked"], "locked");
        $ui->addToggle(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "createkit-emptyOnClaimToggle")), DataManager::getKey(DataManager::CONFIG, "default-flags")["emptyOnClaim"], "emptyOnClaim");
        $ui->addToggle(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "createkit-doOverrideToggle")), DataManager::getKey(DataManager::CONFIG, "default-flags")["doOverride"], "doOverride");
        $ui->addToggle(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "createkit-doOverrideArmorToggle")), DataManager::getKey(DataManager::CONFIG, "default-flags")["doOverrideArmor"], "doOverrideArmor");
        $ui->addToggle(TextFormat::colorize(DataManager::getKey(DataManager::LANG, "createkit-alwaysClaimToggle")), DataManager::getKey(DataManager::CONFIG, "default-flags")["alwaysClaim"], "alwaysClaim");
        $sender->sendForm($ui);
        return true;
    }

}
