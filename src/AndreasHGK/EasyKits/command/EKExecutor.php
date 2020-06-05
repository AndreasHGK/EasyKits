<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\command;

use AndreasHGK\EasyKits\EasyKits;
use AndreasHGK\EasyKits\manager\DataManager;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;

abstract class EKExecutor implements CommandExecutor {

    protected $name;
    protected $desc;
    protected $aliases = [];
    protected $permission;
    protected $usage;

    protected function setDataFromConfig(string $commandName) : void {
        $commandData = DataManager::getKey(DataManager::COMMANDS, $commandName);
        $this->name = array_shift($commandData["labels"]);
        if(isset($commandData["labels"])) $this->aliases = $commandData["labels"];
        $this->desc = $commandData["description"];
        $this->usage = $commandData["usage"];
        $this->permission = EasyKits::PERM_ROOT . "command." . $commandName;
    }

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

    abstract public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool;

}
