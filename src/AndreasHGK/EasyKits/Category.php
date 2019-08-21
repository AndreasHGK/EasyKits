<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits;

use AndreasHGK\EasyKits\manager\DataManager;
use pocketmine\permission\Permissible;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;

class Category{

    /** @var string */
    protected $name;
    /** @var Kit[] */
    protected $kits = [];
    /** @var bool */
    protected $locked = true;

    /**
     * @param Permissible $permissible
     * @return array|Kit[]
     */
    public function getPermittedKitsFor(Permissible $permissible) : array {
        $kits = [];
        foreach($this->kits as $kit){
            if($kit->hasPermission($permissible)) $kits[$kit->getName()] = $kit;
        }
        return $kits;
    }

    public function hasPermission(Permissible $permissible) : bool {
        return !$this->isLocked() || $permissible->hasPermission(EasyKits::PERM_ROOT."category.".$this->getName());
    }

    public function getName() : string {
        return $this->name;
    }

    public function setName(string $name) : void {
        $this->name = $name;
    }

    /**
     * @return array|Kit[]
     */
    public function getKits() : array {
        return $this->kits;
    }

    public function setKits(array $kits) : void {
        $this->kits = $kits;
    }

    public function isLocked() : bool {
        return $this->locked;
    }

    public function setLocked(bool $locked) : void {
        $this->locked = $locked;
    }

    public function __construct(string $name)
    {
        $this->name = $name;
        PermissionManager::getInstance()->addPermission(new Permission(EasyKits::PERM_ROOT."category.".$name, "permission to view category ".$name, DataManager::getKey(DataManager::CONFIG, "op-has-all-categories") ? Permission::DEFAULT_OP : Permission::DEFAULT_FALSE));
    }

}
