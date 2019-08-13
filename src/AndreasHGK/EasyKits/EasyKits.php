<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits;

use AndreasHGK\EasyKits\command\CreatekitCommand;
use AndreasHGK\EasyKits\command\DeletekitCommand;
use AndreasHGK\EasyKits\command\KitCommand;
use AndreasHGK\EasyKits\utils\KitException;
use onebone\economyapi\EconomyAPI;
use pocketmine\command\PluginCommand;
use pocketmine\permission\Permissible;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class EasyKits extends PluginBase{

    public const PERM_ROOT = "easykits.";

    protected static $instance;

    public static function get() : self {
        return self::$instance;
    }

    public function onLoad() : void
    {
        self::$instance = $this;
        DataManager::loadDefault();
        KitManager::loadAll();
        CooldownManager::loadCooldowns();
    }

    public function onEnable() : void
    {
        $commands = [
            new CreatekitCommand(),
            new DeletekitCommand(),
            new KitCommand(),
        ];
        foreach($commands as $command){
            $cmd = new PluginCommand($command->getName(), $this);
            $cmd->setExecutor($command);
            $cmd->setDescription($command->getDesc());
            $cmd->setAliases($command->getAliases());
            $cmd->setPermission($command->getPermission());
            $cmd->setUsage($command->getUsage());
            $this->getServer()->getCommandMap()->register("easykits", $cmd);
        }
	}

	public function onDisable()
    {
        DataManager::save(DataManager::KITS);
        CooldownManager::saveCooldowns();
    }
}
