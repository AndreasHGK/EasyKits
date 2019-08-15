<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\command;

use AndreasHGK\EasyKits\EasyKits;
use AndreasHGK\EasyKits\Kit;
use AndreasHGK\EasyKits\manager\DataManager;
use AndreasHGK\EasyKits\manager\KitManager;
use AndreasHGK\EasyKits\utils\LangUtils;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\Player;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

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
            $sender->sendMessage(LangUtils::getMessage("sender-not-player"));
            return true;
        }

        $ui = new CustomForm(function(Player $player, $data){
            if($data === null){
                $player->sendMessage(LangUtils::getMessage("createkit-cancelled"));
                return;
            }
            if(!isset($data["name"])){
                $player->sendMessage(LangUtils::getMessage("createkit-no-name"));
                return;
            }

            $name = (string)$data["name"];

            if(KitManager::exists($name)){
                $player->sendMessage(LangUtils::getMessage("createkit-duplicate"));
                return;
            }

            if(!isset($data["price"])){
                $price = 0;
            }elseif(!is_numeric((float)$data["price"])){
                $player->sendMessage(LangUtils::getMessage("createkit-invalid-price"));
                return;
            }else{
                $price = (float)$data["price"];
            }

            if(!isset($data["cooldown"])){
                $cooldown = 0;
            }elseif(!is_numeric((float)$data["cooldown"])){
                //todo: date format to time
                $player->sendMessage(LangUtils::getMessage("createkit-invalid-cooldown"));
                return;
            }else{
                $cooldown = (int)$data["cooldown"];
            }

            $locked = $data["locked"];
            $emptyOnClaim = $data["emptyOnClaim"];
            $doOverride = $data["doOverride"];
            $doOverrideArmor = $data["doOverrideArmor"];
            $alwaysClaim = $data["alwaysClaim"];
            $chestKit = $data["chestKit"];

            $items = $player->getInventory()->getContents();
            $armor = $player->getArmorInventory()->getContents();
            if(empty($items) && empty($armor)){
                $player->sendMessage(LangUtils::getMessage("createkit-empty-inventory"));
                return;
            }

            $kit = new Kit($name, $price, $cooldown, $items, $armor);
            $kit->setLocked($locked);
            $kit->setEmptyOnClaim($emptyOnClaim);
            $kit->setDoOverride($doOverride);
            $kit->setDoOverrideArmor($doOverrideArmor);
            $kit->setAlwaysClaim($alwaysClaim);
            $kit->setChestKit($chestKit);
            if(KitManager::add($kit)) $player->sendMessage(LangUtils::getMessage("createkit-success", true, ["{NAME}" => $name]));
            KitManager::saveAll();
            return;
        });
        $ui->setTitle(LangUtils::getMessage("createkit-title"));
        $ui->addLabel(LangUtils::getMessage("createkit-text"));
        $ui->addInput(LangUtils::getMessage("createkit-kitname"), "", null, "name");
        $ui->addInput(LangUtils::getMessage("createkit-price"), "", "0", "price");
        $ui->addInput(LangUtils::getMessage("createkit-cooldown"), "", "60", "cooldown");
        $ui->addLabel(LangUtils::getMessage("createkit-flags"));
        $ui->addToggle(LangUtils::getMessage("createkit-lockedToggle"), DataManager::getKey(DataManager::CONFIG, "default-flags")["locked"], "locked");
        $ui->addToggle(LangUtils::getMessage("createkit-emptyOnClaimToggle"), DataManager::getKey(DataManager::CONFIG, "default-flags")["emptyOnClaim"], "emptyOnClaim");
        $ui->addToggle(LangUtils::getMessage("createkit-doOverrideToggle"), DataManager::getKey(DataManager::CONFIG, "default-flags")["doOverride"], "doOverride");
        $ui->addToggle(LangUtils::getMessage("createkit-doOverrideArmorToggle"), DataManager::getKey(DataManager::CONFIG, "default-flags")["doOverrideArmor"], "doOverrideArmor");
        $ui->addToggle(LangUtils::getMessage("createkit-alwaysClaimToggle"), DataManager::getKey(DataManager::CONFIG, "default-flags")["alwaysClaim"], "alwaysClaim");
        $ui->addToggle(LangUtils::getMessage("createkit-chestKitToggle"), DataManager::getKey(DataManager::CONFIG, "default-flags")["chestKit"], "chestKit");
        $sender->sendForm($ui);
        return true;
    }

}
