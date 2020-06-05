<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits;

use AndreasHGK\EasyKits\manager\DataManager;
use pocketmine\permission\Permissible;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;

class Category {

    /** @var string */
    protected $name;
    /** @var Kit[] */
    protected $kits = [];
    /** @var bool */
    protected $locked = true;

    /**
     * Get all the kits that the Permissible is allowed to claim
     *
     * @param Permissible $permissible
     * @return array|Kit[]
     */
    public function getPermittedKitsFor(Permissible $permissible) : array {
        $kits = [];
        foreach($this->kits as $kit) {
            if($kit->hasPermission($permissible)) $kits[$kit->getName()] = $kit;
        }
        return $kits;
    }

    /**
     * Check if someone has permission to see the category
     *
     * @param Permissible $permissible
     * @return bool
     */
    public function hasPermission(Permissible $permissible) : bool {
        return !$this->isLocked() || $permissible->hasPermission(EasyKits::PERM_ROOT . "category." . $this->getName()) || $permissible->hasPermission(EasyKits::PERM_ROOT . "category");
    }

    /**
     * Check if the given kit is displayed in the category
     *
     * @param Kit $kit
     * @return bool
     */
    public function hasKit(Kit $kit) : bool {
        return isset($this->kits[$kit->getName()]);
    }

    /**
     * Add a kit to be displayed in the category
     *
     * @param Kit $kit
     */
    public function addKit(Kit $kit) {
        $this->kits[$kit->getName()] = $kit;
    }

    /**
     * Remove a kit from the category
     *
     * @param Kit $kit
     */
    public function removeKit(Kit $kit) {
        unset($this->kits[$kit->getName()]);
    }

    /**
     * Get the displayed name of the category
     *
     * @return string
     */
    public function getName() : string {
        return $this->name;
    }

    /**
     * Change the name of the category
     *
     * @param string $name
     */
    public function setName(string $name) : void {
        $this->name = $name;
    }

    /**
     * Get all the kits inside the category
     *
     * @return array|Kit[]
     */
    public function getKits() : array {
        return $this->kits;
    }

    /**
     * Set all the kits inside the category
     *
     * @param array $kits
     */
    public function setKits(array $kits) : void {
        $this->kits = $kits;
    }

    /**
     * Check whether a permission is needed to view the category
     *
     * @return bool
     */
    public function isLocked() : bool {
        return $this->locked;
    }

    /**
     * Change whether a permission is needed to view the category
     *
     * @param bool $locked
     */
    public function setLocked(bool $locked) : void {
        $this->locked = $locked;
    }

    public function __construct(string $name) {
        $this->name = $name;
        PermissionManager::getInstance()->addPermission(new Permission(EasyKits::PERM_ROOT . "category." . $name, "permission to view category " . $name, DataManager::getKey(DataManager::CONFIG, "op-has-all-categories") ? Permission::DEFAULT_OP : Permission::DEFAULT_FALSE));
    }

}
