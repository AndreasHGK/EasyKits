<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\command;

use AndreasHGK\EasyKits\importer\AdvancedKitsImporter;
use AndreasHGK\EasyKits\importer\KitsPlusImporter;
use AndreasHGK\EasyKits\importer\KitUIImporter;
use AndreasHGK\EasyKits\ui\KitImportForm;
use AndreasHGK\EasyKits\utils\LangUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class EKImportCommand extends EKExecutor {

    public function __construct() {
        $this->setDataFromConfig("ekimport");

    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
        if(!$sender instanceof Player) {
            $sender->sendMessage(LangUtils::getMessage("sender-not-player"));
            return true;
        }

        if(isset($args[0])) {
            switch(strtolower($args[0])) {
                case "advancedkits":
                    $name = "AdvancedKits";
                    if(!AdvancedKitsImporter::isPluginLoaded()) {
                        $sender->sendMessage(LangUtils::getMessage("ekimport-not-loaded"));
                        return true;
                    }
                    $return = AdvancedKitsImporter::ImportAll();
                    break;
                case "kitui":
                    $name = "KitUI";
                    if(!KitUIImporter::isPluginLoaded()) {
                        $sender->sendMessage(LangUtils::getMessage("ekimport-not-loaded"));
                        return true;
                    }
                    $return = KitUIImporter::ImportAll();
                    break;
                case "kitsplus":
                    $name = "KitsPlus";
                    if(!KitsPlusImporter::isPluginLoaded()) {
                        $sender->sendMessage(LangUtils::getMessage("ekimport-not-loaded"));
                        return true;
                    }
                    $return = KitsPlusImporter::ImportAll();
                    break;
                default:
                    $sender->sendMessage(LangUtils::getMessage("ekimport-not-loaded"));
                    return true;
                    break;
            }
            $success = 0;
            $failed = 0;
            foreach($return as $bool) {
                if($bool) $success++;
                else $failed++;
            }
            $sender->sendMessage(LangUtils::getMessage("ekimport-success", true, ["{PLUGIN}" => $name, "{SUCCESS}" => $success, "{FAILED}" => $failed]));
            return true;
        }

        KitImportForm::sendTo($sender);
        return true;
    }

}
