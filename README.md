# EasyKits
 A kit plugin for everyone
 
 ![kit creation](media/kitcreation.gif)

## Features
- [x] **Easy:**
You can easily create kits with a simple form. All the items in your inventory will be added to the kit. No configs needed!

- [x] **Customizable:**
You can change the claim behavior of each kit individually. Want to override the armor when claiming? It's all up to you!

- [x] **Language file:**
All messages in the plugin can be changed within the lang.yml file. You can also change the colors.

- [x] **Flexible:**
With all this customizability you can use it for tons of gamemodes from kitpvp to factions.

## Setup

#### Config
In the config you can change the general behaviour of the plugin.

```YAML
#don't touch this
version: 0

# the default values for the kit flags when creating a kit
default-flags:
  locked: true
  emptyOnClaim: false
  doOverride: false
  doOverrideArmor: false
  alwaysClaim: false

# this will only change /kit, not /createkit or /deletekit
use-forms: true

# show players kits they don't have permission to claim
show-locked: false
```

#### Lang
The language file allows you to change every single message in the plugin.
Sometimes it is handy to look at the message itself to see when it is used.

```YAML
#don't touch this
version: 0

# messages

sender-not-player: "§l§4> §r§7sender needs to be a player"

# this appears when trying to claim a kit with a cost when no compatible economy plugin is found
no-economy: "§c§l> §r§7There is no economy installed."

unknown-exception: "§c§l> §r§7There was an unknown exception."

# /createkit UI + messages
createkit-title: "§0Kit creation menu"
createkit-text: "§7When making a kit, all the items in your inventory will be added to the kit. Make sure you have the correct items before you submit the kit."
createkit-kitname: "§e§l> §rkit name"
createkit-price: "§e§l> §rPrice §7(0 for no price)"
createkit-cooldown: "§e§l> §rCooldown §7(in seconds)"
createkit-flags: "§e§l> §rFlags"
createkit-lockedToggle: "locked §7(= permission required)"
createkit-emptyOnClaimToggle: "emptyOnClaim"
createkit-doOverrideToggle: "doOverrideItems"
createkit-doOverrideArmorToggle: "doOverrideArmor"
createkit-alwaysClaimToggle: "alwaysClaim"

createkit-cancelled: "§c§l> §r§7Kit creation cancelled."
createkit-no-name: "§c§l> §r§7Please enter a name for your kit."
createkit-duplicate: "§c§l> §r§7A kit with that name already exists."
createkit-empty-inventory: "§c§l> §r§7You need to hold items in your inventory to create a kit with."
createkit-invalid-price: "§c§l> §r§7Please enter a valid price."
createkit-invalid-cooldown: "§c§l> §r§7Please enter a valid cooldown time."

createkit-success: "§a§l> §r§7A kit with name §a{NAME}§r§7 has been created!"

# /deletekit UI + messages
deletekit-title: "§0Kit deletion menu"
deletekit-text: "§7Please select the kit you want to delete and comfirm it by submitting."
deletekit-select: "§e§l> §rSelect kit"

deletekit-cancelled: "§c§l> §r§7Kit deletion cancelled."
deletekit-empty: "§c§l> §r§7Please select a kit to delete."
deletekit-not-found: "§c§l> §r§7Selected kit not found."

deletekit-success: "§a§l> §r§7Successfully deleted kit §a{NAME}§r§7."

# /kit UI + messages
kit-title: "§OKit selection"
kit-text: "" #this is optional
kit-available-free-format: "{NAME}\n§aUnlocked"
kit-available-priced-format: "{NAME}\n§6${PRICE}"
kit-locked-format: "{NAME}\n§cLocked" # only shows when enabled in config

kit-list: "§e§l> §r§7Available kits: §f{KITS}" # when UI is turned off

kit-none-available: "§c§l> §r§7There are no available kits."
kit-no-permission: "§c§l> §r§7You don't have permission to claim this kit."
kit-not-found: "§c§l> §r§7A kit with that name does not exist."
kit-insufficient-funds: "§c§l> §r§7You have insufficient funds to claim this kit."
kit-insufficient-space: "§c§l> §r§7You don't have enough inventory space to claim this kit."
kit-cooldown-active: "§c§l> §r§7You can't claim this kit for another §c{TIME}§7."

kit-claim-success: "§a§l> §r§7Claimed kit §a{NAME}§r§7."
```

#### Commands
In **commands.yml** you're able to change the name, aliases, description and usage (message only) of the commands.

```YAML
#don't touch this
version: 0

# the command name. Don't change this or the command won't work.
createkit:
  # the command label is what the user types to execute the command. Add multiple ones for aliases. The first label will be the main label.
  labels:
    - "createkit"
    - "makekit"
    - "addkit"
  # the description is what will appear next to the command in the command list.
  description: "make a kit"
  # the usage is how to execute the command. It is best to leave it like this.
  usage: "/createkit [name]"

kit:
  labels:
    - "kit"
    - "ekit"
  description: "claim a kit"
  usage: "/kit [name]"

deletekit:
  labels:
    - "deletekit"
    - "delkit"
    - "removekit"
  description: "delete a kit"
  usage: "/deletekit [name]"

```

#### Kit format
You can add kits with the /createkit command. 
However, if you wish to edit kits or create kits in the **kits.yml** file directly you can do that too.

##### Flags
- **Locked:** The player requires permission (*easykits.kit.<kitname>*) to claim the kit
- **doOverride:** The kit will take up the exact inventory slots assigned to the items.
- **doOverrideArmor:** The kit will take up the exact armor slots assigned to the armor pieces.
- **alwaysClaim:** Claim the kit even if he player lacks inventory space.
- **emptyOnClaim:** Empty the player's inventory before claiming the kit.

```YAML
examplekit:
  # Items to put in the inventory. 0-8 -> hotbar (from left to right). 9-35 -> everything else
  items:
    0: #inventory slot
      id: 276
      damage: 0
      count: 1
      display_name: "§1Example Sword"
      enchants: 
        9: 1 # sharpness 1 (ID: LEVEL)
    1:
      id: 278
      damage: 0
      count: 1
      display_name: "§1Example Pickaxe"
    2:
      id: 279
      damage: 0
      count: 1
      display_name: "§1Example Axe"
    3:
      id: 277
      damage: 0
      count: 1
      display_name: "§1Example Shovel"
    8:
      id: 264
      damage: 0
      count: 1
      display_name: "§r§aThank you for using EasyKits!"
      lore:
        - "§r§7Developed by: §9AndreasHGK"
        - "§r§fTIP: §7Remove this kit. This is just an example."
  # Items to put in the armor slots. 0 -> boots, 1 -> leggings, 2 -> chestplate, 3 -> helmet
  armor:
    0:
      id: 306
      damage: 0
      count: 1
      display_name: "§1Example Boots"
    1:
      id: 307
      damage: 0
      count: 1
      display_name: "§1Example Leggings"
    2:
      id: 308
      damage: 0
      count: 1
      display_name: "§1Example Chestplate"
    3:
      id: 309
      damage: 0
      count: 1
      display_name: "§1Example Helmet"
  # The price a user has to pay to claim a kit
  price: 10.000000
  # The time a user has to wait before being able to claim a new kit. The cooldown is in seconds
  cooldown: 60
  flags:
    # The user will need permission (easykits.kit.<kitname>) to claim the kit
    locked: true
    # When claimed, the kit's items will be put in the exact slot is was when created, even if it is occupied
    doOverride: false
    # Same as before, but with armor. When false, armor from occupied slots will be put in the inventory
    doOverrideArmor: false
    # Claim the kit even when the inventory is full
    alwaysClaim: false
    # Clear a player's whole inventory before putting the kit items in
    emptyOnClaim: false
```

## Todo
- [ ] **AdvancedKits & KitUI importer:** Make it so people can easily switch from another kit plugin.
- [ ] **Kit Chests:** Make it so you can put kits in chests or any item which you have to right click to claim. (This will be optional)
- [ ] **Multi Economy:** Support multiple economy plugins.
- [ ] **One-per-life:** Support the option of only being able to claim 1 kit per life.
- [ ] **Potion effects:** Support the option to add potion effects to kits.
- [ ] **Claim effects:** Add (optional)cosmetic effects when claiming kits.