<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\command;

use AndreasHGK\EasyKits\EasyKits;
use AndreasHGK\EasyKits\importer\AdvancedKitsImporter;
use AndreasHGK\EasyKits\importer\KitUIImporter;
use AndreasHGK\EasyKits\Kit;
use AndreasHGK\EasyKits\manager\DataManager;
use AndreasHGK\EasyKits\utils\LangUtils;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\command\CommandExecutor;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;

class EKImportCommand extends EKExecutor {

    public function __construct()
    {
        $commandData = DataManager::getKey(DataManager::COMMANDS, "ekimport");
        $this->name = array_shift($commandData["labels"]);
        if(isset($commandData["labels"])) $this->aliases = $commandData["labels"];
        $this->desc = $commandData["description"];
        $this->usage = $commandData["usage"];
        $this->permission = EasyKits::PERM_ROOT."command.ekimport";
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if(!$sender instanceof Player){
            $sender->sendMessage(LangUtils::getMessage("sender-not-player"));
            return true;
        }

        if(isset($args[0])){
            switch (strtolower($args[0])){
                case "advancedkits":
                    $name = "AdvancedKits";
                    if(!AdvancedKitsImporter::isPluginLoaded()){
                        $sender->sendMessage(LangUtils::getMessage("ekimport-not-loaded"));
                        return true;
                    }
                    $return = AdvancedKitsImporter::ImportAll();
                    break;
                case "kitui":
                    $name = "KitUI";
                    if(!KitUIImporter::isPluginLoaded()){
                        $sender->sendMessage(LangUtils::getMessage("ekimport-not-loaded"));
                        return true;
                    }
                    $return = KitUIImporter::ImportAll();
                    break;
                default:
                    $sender->sendMessage(LangUtils::getMessage("ekimport-not-loaded"));
                    break;
            }
            $success = 0;
            $failed = 0;
            foreach($return as $bool){
                if($bool) $success++;
                else $failed++;
            }
            $sender->sendMessage(LangUtils::getMessage("ekimport-success", true, ["{PLUGIN}" => $name, "{SUCCESS}" => $success, "{FAILED}" => $failed]));
        }

        $ui = new CustomForm(function(Player $player, $data){
            if($data === null){
                $player->sendMessage(LangUtils::getMessage("ekimport-cancelled"));
                return;
            }
            switch ($data["dropdown"]){
                case 0:
                    $name = "AdvancedKits";
                    if(!AdvancedKitsImporter::isPluginLoaded()){
                        $player->sendMessage(LangUtils::getMessage("ekimport-not-loaded"));
                        return;
                    }
                    $return = AdvancedKitsImporter::ImportAll();
                    break;
                case 1:
                    $name = "KitUI";
                    if(!KitUIImporter::isPluginLoaded()){
                        $player->sendMessage(LangUtils::getMessage("ekimport-not-loaded"));
                        return;
                    }
                    $return = KitUIImporter::ImportAll();
                    break;
                default:
                    $player->sendMessage(LangUtils::getMessage("ekimport-not-loaded"));
                    break;
            }
            $success = 0;
            $failed = 0;
            foreach($return as $bool){
                if($bool) $success++;
                else $failed++;
            }
            $player->sendMessage(LangUtils::getMessage("ekimport-success", true, ["{PLUGIN}" => $name, "{SUCCESS}" => $success, "{FAILED}" => $failed]));
            return;
        });
        $ui->setTitle(LangUtils::getMessage("ekimport-title"));
        $ui->addLabel(LangUtils::getMessage("ekimport-text"));
        $ui->addDropdown(LangUtils::getMessage("ekimport-select"), ["AdvancedKits", "KitUI",], null, "dropdown");
        $sender->sendForm($ui);
        return true;
    }

}
