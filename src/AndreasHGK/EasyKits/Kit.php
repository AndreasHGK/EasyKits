<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits;

use AndreasHGK\EasyKits\event\InteractItemClaimEvent;
use AndreasHGK\EasyKits\event\KitClaimEvent;
use AndreasHGK\EasyKits\manager\CooldownManager;
use AndreasHGK\EasyKits\manager\DataManager;
use AndreasHGK\EasyKits\manager\EconomyManager;
use AndreasHGK\EasyKits\utils\KitException;
use AndreasHGK\EasyKits\utils\LangUtils;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\entity\EffectInstance;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\tag\StringTag;
use pocketmine\permission\Permissible;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\Player;
use pocketmine\Server;

class Kit {

    //items
    /** @var Item[] */
    private $items = [];
    /** @var Item[] */
    private $armor = [];

    //settings
    /** @var string */
    private $name;
    /** @var string */
    private $permission;
    /** @var float */
    private $price = 0;
    /** @var int */
    private $cooldown = 60;
    /** @var EffectInstance[] */
    private $effects = [];
    /** @var string[] */
    private $commands = [];
    /** @var Item|null */
    private $interactItem = null;

    //flags
    /** @var bool */
    private $locked = true;
    /** @var bool */
    private $emptyOnClaim = true;
    /** @var bool */
    private $doOverride = false;
    /** @var bool */
    private $doOverrideArmor = false;
    /** @var bool */
    private $alwaysClaim = false;
    /** @var bool */
    private $chestKit = false;

    /**
     * The default claim function
     *
     * @param Player $player
     * @return bool
     */
    public function claim(Player $player) : bool {
        if($this->isChestKit()) return $this->claimChestKitFor($player);
        else return $this->claimFor($player);
    }

    /**
     * Claim the kit as a player and as a chestkit
     *
     * @param Player $player
     * @return bool
     */
    public function claimChestKitFor(Player $player) : bool {
        if(!$this->hasPermission($player) && $this->isLocked()) throw new KitException("Player is not permitted to claim this kit", 4);
        if($this->getCooldown() > 0) {
            if(CooldownManager::hasKitCooldown($this, $player)) {
                throw new KitException("Kit is on cooldown", 0);
            }
        }
        if($this->getPrice() > 0) {
            if(EconomyManager::isEconomyLoaded()) {
                if(EconomyManager::getMoney($player) < $this->getPrice()) {
                    throw new KitException("Player has insufficient funds", 1);
                }
            } else {
                throw new KitException("Economy not found", 2);
            }
        }

        if(count($player->getInventory()->getContents(false)) >= $player->getInventory()->getSize()) {
            throw new KitException("Player has insufficient space", 3);
        }

        $kit = clone $this;
        if($player->hasPermission(EasyKits::PERM_ROOT . "free." . $kit->getPermission())) $kit->price = 0;
        if($player->hasPermission(EasyKits::PERM_ROOT . "instant." . $kit->getPermission())) $kit->cooldown = 0;

        $event = new InteractItemClaimEvent($kit, $player);
        $event->call();

        if($event->isCancelled()) return false;


        $player = $event->getPlayer();
        $kit = $event->getKit();

        if($kit->getCooldown() > 0) {
            CooldownManager::setKitCooldown($kit, $player);
        }
        if($kit->getPrice() > 0) {
            EconomyManager::reduceMoney($player, $kit->getPrice(), true);
        }
        $player->getInventory()->addItem($kit->getInteractItem());
        return true;
    }

    /**
     * claim a kit as a player
     *
     * @param Player $player
     * @return bool
     * @throws KitException
     */
    public function claimFor(Player $player) : bool {
        if(!$this->hasPermission($player) && $this->isLocked()) throw new KitException("Player is not permitted to claim this kit", 4);
        if($this->getCooldown() > 0) {
            if(CooldownManager::hasKitCooldown($this, $player)) {
                throw new KitException("Kit is on cooldown", 0);
            }
        }
        if($this->getPrice() > 0) {
            if(EconomyManager::isEconomyLoaded()) {
                if(EconomyManager::getMoney($player) < $this->getPrice()) {
                    throw new KitException("Player has insufficient funds", 1);
                }
            } else {
                throw new KitException("Economy not found", 2);
            }
        }

        $armorSlots = $this->getArmor();
        $playerArmorInv = $player->getArmorInventory();
        $playerArmor = $playerArmorInv->getContents(true);
        $playerInv = $player->getInventory();
        $playerSlots = $playerInv->getContents(false);
        $invSlots = $this->getItems();
        $invCount = count($invSlots);
        if($this->doOverrideArmor()) {
            foreach($armorSlots as $key => $armorSlot) {
                if($playerArmor[$key]->getId() !== Item::AIR) {
                    $invCount++;
                }
            }
        }
        if(!$this->alwaysClaim()) {
            if($invCount > $playerInv->getSize()) {
                throw new KitException("Player has insufficient space", 3);
            }
            if(!$this->emptyOnClaim() && !$this->doOverride() && $invCount > ($playerInv->getSize() - count($playerSlots))) {
                throw new KitException("Player has insufficient space", 3);
            }
        }
        $kit = clone $this;
        if($player->hasPermission(EasyKits::PERM_ROOT . "free." . $kit->getPermission())) $kit->price = 0;
        if($player->hasPermission(EasyKits::PERM_ROOT . "instant." . $kit->getPermission())) $kit->cooldown = 0;
        $event = new KitClaimEvent($kit, $player);
        $event->call();

        if($event->isCancelled()) return false;

        $player = $event->getPlayer();
        $kit = $event->getKit();

        if($kit->getCooldown() > 0) {
            CooldownManager::setKitCooldown($kit, $player);
        }
        if($kit->getPrice() > 0) {
            EconomyManager::reduceMoney($player, $kit->getPrice(), true);
        }
        if($kit->emptyOnClaim()) {
            $playerInv->clearAll();
            $playerArmorInv->clearAll();
        }
        foreach($invSlots as $key => $invSlot) {
            if($kit->doOverride()) $playerInv->setItem($key, $invSlot);
            else $playerInv->addItem($invSlot);
        }
        foreach($armorSlots as $key => $armorSlot) {
            if($kit->doOverrideArmor()) $playerArmorInv->setItem($key, $armorSlot);
            elseif($playerArmorInv->getItem($key)->getId() !== Item::AIR) $playerInv->addItem($armorSlot);
            else $playerArmorInv->setItem($key, $armorSlot);
        }
        foreach($kit->getEffects() as $effect) {
            $player->addEffect($effect);
        }
        foreach($kit->getCommands() as $command) {
            Server::getInstance()->dispatchCommand(new ConsoleCommandSender(), LangUtils::replaceVariables($command, ["{PLAYER}" => $player->getName(), "{NICK}" => $player->getDisplayName()]));
        }
        return true;
    }

    /**
     * Get the item that you use to claim the kit (for chestkits)
     *
     * @return Item|null
     */
    public function getInteractItem() : ?Item {
        return $this->interactItem;
    }

    /**
     * Check if the kit has an item that you use to claim it (for chestkits)
     *
     * @return bool
     */
    public function hasInteractItem() : bool {
        return $this->getItems() !== null;
    }

    /**
     * Change the item that claims the kit when you use it (for chestkits)
     *
     * @param Item $item
     */
    public function setInteractItem(Item $item) : void {
        if(!$item->getNamedTag()->hasTag("ekit") || $item->getNamedTag()->getTagValue("ekit", StringTag::class) !== $this->getName()) {
            $item->setNamedTagEntry(new StringTag("ekit", $this->name));
        }
        $this->interactItem = $item;
    }

    /**
     * Check if a permissible has permission to claim the kit
     *
     * @param Permissible $permissible
     * @return bool
     */
    public function hasPermission(Permissible $permissible) : bool {
        return $permissible->hasPermission(EasyKits::PERM_ROOT . "kit." . $this->getPermission()) || !$this->isLocked() || $permissible->hasPermission(EasyKits::PERM_ROOT . "kit");
    }

    /**
     * Get the permission needed to claim the kit
     *
     * @return string
     */
    public function getPermission() : string {
        return $this->permission;
    }

    /**
     * Set the permission needed to claim the kit
     *
     * @param string $permission
     * @internal use the changePermission() function
     */
    public function setPermission(string $permission) : void {
        $this->permission = $permission;
    }

    /**
     * Change the permission of the kit
     *
     * @param string $permission
     */
    public function changePermission(string $permission) : void {
        $this->unregisterPermissions();
        $this->setPermission($permission);
        $this->registerPermissions();
    }

    /**
     * Get the name of the kit displayed to the player
     *
     * @return string
     */
    public function getName() : string {
        return $this->name;
    }

    /**
     * Set the name of the kit displayed to the player
     *
     * @param string $name
     */
    public function setName(string $name) : void {
        $this->name = $name;
    }

    /**
     * Get the inventory items of the kit
     *
     * @return Item[]
     */
    public function getItems() : array {
        return $this->items;
    }

    /**
     * Set the inventory items of the kit
     *
     * @param array|Item[] $items
     */
    public function setItems(array $items) : void {
        $this->items = $items;
    }

    /**
     * Get the armor items of the kit
     *
     * @return Item[]
     */
    public function getArmor() : array {
        return $this->armor;
    }

    /**
     * Set the armor items of the kit
     *
     * @param array|Item[] $armor
     */
    public function setArmor(array $armor) : void {
        $this->armor = $armor;
    }

    /**
     * Get the cost of the kit that the player needs to pay when claiming
     *
     * @return float
     */
    public function getPrice() : float {
        return $this->price;
    }

    /**
     * Set the cost of the kit
     *
     * @param float $price
     */
    public function setPrice(float $price) : void {
        if($price < 0) throw new KitException("price can't be below 0");
        $this->price = $price;
    }

    /**
     * Get the cooldown time of the kit in seconds
     *
     * @return int
     */
    public function getCooldown() : int {
        return $this->cooldown;
    }

    /**
     * Set the cooldown time of the kit in seconds
     *
     * @param int $cooldown
     */
    public function setCooldown(int $cooldown) : void {
        if($cooldown < 0) throw new KitException("cooldown can't be below 0");
        $this->cooldown = $cooldown;
    }

    /**
     * Get the potion effects the player gets when claiming the kit
     *
     * @return EffectInstance[]|array
     */
    public function getEffects() : array {
        return $this->effects;
    }

    /**
     * Set the potion effects the player gets when claiming the kit
     *
     * @param array|EffectInstance[] $effects
     */
    public function setEffects(array $effects) : void {
        $this->effects = $effects;
    }

    /**
     * Get the commands that are ran when a player claims the kit
     *
     * @return string[]
     */
    public function getCommands() : array {
        return $this->commands;
    }

    /**
     * Set the commands that are ran when a player claims the kit
     *
     * @param string[] $commands
     */
    public function setCommands(array $commands) : void {
        $this->commands = $commands;
    }

    /**
     * Check whether the kit needs permission to be claimed
     *
     * @return bool
     */
    public function isLocked() : bool {
        return $this->locked;
    }

    /**
     * Change whether the kit needs permission to be claimed
     *
     * @param bool $locked
     */
    public function setLocked(bool $locked) : void {
        $this->locked = $locked;
    }

    /**
     * Check whether the kit overrides items in the inventory when claiming the kit
     * (it will use the spots that you put the items in when making it, no matter what)
     *
     * @return bool
     */
    public function doOverride() : bool {
        return $this->doOverride;
    }

    /**
     * Change whether to override items
     *
     * @param bool $doOverride
     */
    public function setDoOverride(bool $doOverride) : void {
        $this->doOverride = $doOverride;
    }

    /**
     * heck whether the kit overrides items in the armor inventory when claiming the kit
     * (it will use the spots that you put the items in when making it, no matter what)
     *
     * @return bool
     */
    public function doOverrideArmor() : bool {
        return $this->doOverrideArmor;
    }

    /**
     * Change whether to override items in the armor inventory
     *
     * @param bool $doOverrideArmor
     */
    public function setDoOverrideArmor(bool $doOverrideArmor) : void {
        $this->doOverrideArmor = $doOverrideArmor;
    }

    /**
     * Check whether the kit is claimable, even when the receiver inventory is full
     *
     * @return bool
     */
    public function alwaysClaim() : bool {
        return $this->alwaysClaim;
    }

    /**
     * Change whether the kit is claimable, even when the receiver inventory is full
     *
     * @param bool $alwaysClaim
     */
    public function setAlwaysClaim(bool $alwaysClaim) : void {
        $this->alwaysClaim = $alwaysClaim;
    }

    /**
     * Check whether the player's inventory will be cleared before claiming the kit
     *
     * @return bool
     */
    public function emptyOnClaim() : bool {
        return $this->emptyOnClaim;
    }

    /**
     * Change whether the player's inventory will be cleared before claiming the kit
     *
     * @param bool $emptyOnClaim
     */
    public function setEmptyOnClaim(bool $emptyOnClaim) : void {
        $this->emptyOnClaim = $emptyOnClaim;
    }

    /**
     * Check whether the kit is a chestkit and has an item that you use to claim it instead of directly claiming
     *
     * @return bool
     */
    public function isChestKit() : bool {
        return $this->chestKit;
    }

    /**
     * Change whether this kit is a chestkit
     *
     * @param bool $bool
     */
    public function setChestKit(bool $bool) : void {
        $this->chestKit = $bool;
        if($bool) {
            $item = ItemFactory::get(DataManager::getKey(DataManager::CONFIG, "chestKit-itemid"));
            $item->setCustomName(LangUtils::getMessage("chestkit-name", true, ["{NAME}" => $this->getName()]));
            $item->setLore(LangUtils::getMessage("chestkit-lore"));
            $this->setInteractItem($item);
        } elseif(isset($this->chestKit)) {
            $this->interactItem = null;
        }
    }

    /**
     * Internal function to register the required permissions when the kit instance is loaded
     *
     * @internal
     */
    private function registerPermissions() : void {
        $permissionManager = PermissionManager::getInstance();

        $permissionManager->addPermission(new Permission(EasyKits::PERM_ROOT . "kit." . $this->getPermission(),
            "permission to claim kit " . $this->getName(),
            DataManager::getKey(DataManager::CONFIG, "op-has-all-kits") ? Permission::DEFAULT_OP : Permission::DEFAULT_FALSE));

        $permissionManager->addPermission(new Permission(EasyKits::PERM_ROOT . "free." . $this->getPermission(),
            "permission to claim kit " . $this->getName() . " for free",
            DataManager::getKey(DataManager::CONFIG, "op-has-free-kits") ? Permission::DEFAULT_OP : Permission::DEFAULT_FALSE));

        $permissionManager->addPermission(new Permission(EasyKits::PERM_ROOT . "instant." . $this->getPermission(),
            "permission to claim kit " . $this->getName() . " without cooldown",
            DataManager::getKey(DataManager::CONFIG, "op-has-instant-kits") ? Permission::DEFAULT_OP : Permission::DEFAULT_FALSE));
    }

    /**
     * Unregister the permissions for the kit
     *
     * @internal
     */
    public function unregisterPermissions() : void {
        $permissionManager = PermissionManager::getInstance();

        $permissionManager->removePermission(EasyKits::PERM_ROOT . "kit." . $this->getPermission());
        $permissionManager->removePermission(EasyKits::PERM_ROOT . "free." . $this->getPermission());
        $permissionManager->removePermission(EasyKits::PERM_ROOT . "instant." . $this->getPermission());
    }

    public function __toString() {
        return $this->name;
    }

    /**
     * Kit constructor.
     * @param string $name
     * @param string $permission
     * @param float $price
     * @param int $cooldown
     * @param Item[] $items
     * @param Item[] $armor
     */
    public function __construct(string $name, string $permission, float $price, int $cooldown, array $items, array $armor) {
        $this->name = $name;
        $this->permission = $permission;
        $this->price = $price;
        $this->cooldown = $cooldown;
        $this->items = $items;
        $this->armor = $armor;
        $this->registerPermissions();
    }

}
