<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits;

use AndreasHGK\EasyKits\command\CreatecategoryCommand;
use AndreasHGK\EasyKits\command\CreatekitCommand;
use AndreasHGK\EasyKits\command\DeletecategoryCommand;
use AndreasHGK\EasyKits\command\DeletekitCommand;
use AndreasHGK\EasyKits\command\EditkitCommand;
use AndreasHGK\EasyKits\command\EKImportCommand;
use AndreasHGK\EasyKits\command\GivekitCommand;
use AndreasHGK\EasyKits\command\KitCommand;
use AndreasHGK\EasyKits\customenchants\PiggyCustomEnchantsLoader;
use AndreasHGK\EasyKits\listener\InteractClaimListener;
use AndreasHGK\EasyKits\manager\CategoryManager;
use AndreasHGK\EasyKits\manager\CooldownManager;
use AndreasHGK\EasyKits\manager\DataManager;
use AndreasHGK\EasyKits\manager\EconomyManager;
use AndreasHGK\EasyKits\manager\KitManager;
use JackMD\UpdateNotifier\UpdateNotifier;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\command\PluginCommand;
use pocketmine\plugin\PluginBase;

class EasyKits extends PluginBase {

    public const PERM_ROOT = "easykits.";

    protected static $instance;

    public static function get() : self {
        return self::$instance;
    }

    public function onLoad() : void {
        self::$instance = $this;

        UpdateNotifier::checkUpdate($this, $this->getName(), $this->getDescription()->getVersion());
        DataManager::loadDefault();
        if(DataManager::getKey(DataManager::CONFIG, "auto-update-config")) {
            DataManager::updateAllConfigs();
        }
        PiggyCustomEnchantsLoader::load();
        if(!PiggyCustomEnchantsLoader::isPluginLoaded()) {
            KitManager::loadAll();
            if(DataManager::getKey(DataManager::CONFIG, "enable-categories")) {
                CategoryManager::loadAll();
            }
        }
        CooldownManager::loadCooldowns();
        EconomyManager::loadEconomy();
        if(!EconomyManager::isEconomyLoaded()) $this->getLogger()->notice("no compatible economy loaded");
    }

    public function onEnable() : void {
        if(PiggyCustomEnchantsLoader::isPluginLoaded()) {
            KitManager::loadAll(); //because of PiggyCustomEnchants adding enchants in onEnable
            if(DataManager::getKey(DataManager::CONFIG, "enable-categories")) {
                CategoryManager::loadAll();
            }
        }
        $commands = [
            new CreatekitCommand(),
            new DeletekitCommand(),
            new EditkitCommand(),
            new EKImportCommand(),
            new KitCommand(),
            new GivekitCommand(),
        ];
        if(DataManager::getKey(DataManager::CONFIG, "enable-categories")) {
            array_push($commands,
                new CreatecategoryCommand(),
                new DeletecategoryCommand()
            );
        }
        foreach($commands as $command) {
            $cmd = new PluginCommand($command->getName(), $this);
            $cmd->setExecutor($command);
            $cmd->setDescription($command->getDesc());
            $cmd->setAliases($command->getAliases());
            $cmd->setPermission($command->getPermission());
            $cmd->setUsage($command->getUsage());
            $this->getServer()->getCommandMap()->register("easykits", $cmd);
        }
        $listeners = [
            new InteractClaimListener(),
        ];
        foreach($listeners as $listener) {
            $this->getServer()->getPluginManager()->registerEvents($listener, $this);
        }
        if(!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($this);
        }
    }

    public function onDisable() {
        KitManager::saveAll();
        CooldownManager::saveCooldowns();
        CategoryManager::saveAll();
    }
}
