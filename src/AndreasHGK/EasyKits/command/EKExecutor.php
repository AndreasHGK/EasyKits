<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\command;

use AndreasHGK\EasyKits\DataManager;
use pocketmine\command\CommandExecutor;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use redstone\Main;

abstract class EKExecutor implements CommandExecutor {

    protected $name;
    protected $desc;
    protected $aliases = [];
    protected $permission;
    protected $usage;

    public function getName() : string {
        return $this->name;
    }

    public function getDesc() : string {
        return $this->desc;
    }

    public function getAliases() : array {
        return $this->aliases;
    }

    public function getPermission() : string {
        return $this->permission;
    }

    public function getUsage() : string {
        return $this->usage;
    }

    abstract public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool;

}
