# EasyKits
 A kit plugin for everyone
 
 ![kit creation](media/kitcreation.gif)

## Features
- [x] **Easy:**
You can easily create kits with a simple form. All the items in your inventory will be added to the kit. No configs needed!
All the kits can also be edited ingame.

- [x] **Customizable:**
You can change the claim behavior of each kit individually. Want to override the armor when claiming? It's all up to you!

- [x] **Language file:**
All messages in the plugin can be changed within the lang.yml file. You can also change the colors.

- [x] **Flexible:**
With all this customizability you can use it for tons of gamemodes from kitpvp to factions.

- [x] **Easy conversion:** Already have another kit plugin fully set up? 
Don't worry! You can use the /ekimport command to import kits from KitUI, KitsPlus and AdvancedKits

- [x] **Chestkits:** Each kit has the option to be a chestkit.
These kits will be a chest when claimed, and when you tap with them you will equip them.

## Info

### Permissions
**Commands:**
- easykits.command.kit *(permission for /kit)*
- easykits.command.createkit *(permission for /createkit)*
- easykits.command.deletekit *(permission for /deletekit)*
- easykits.command.editkit *(permission for /editkit)*
- easykits.command.ekimport *(permission for /ekimport)*
- easykits.command.createcategory *(permission for /createcategory)*
- easykits.command.deletecategory *(permission for /deletecategory)*

**Kit perms:**
- easykits.kit.[kitname] *(gives permission to claim a kit)*
- easykits.free.[kitname] *(gives permission to claim a kit for free)*
- easykits.instant.[kitname] *(gives permission to claim a kit without cooldown)*
- easykits.category.[kitname] *(gives permission to view a category)*

### Economy
The plugin currently supports 2 economy plugins: EconomyAPI and MultiEconomy.
It will automaticly detect which plugin is loaded.
If you use MultiEconomy, please change the currency you want to use in the config.

### Suggestions
If you have any suggestion to add onto the plugin, feel free to open an issue on github giving a detailed explanation of what you want to get added.
If I feel like the suggestion is good for the plugin, I will add it.

### Issues
Experiencing issues with the plugin? If so please open an issue on Github (and not by reviewing on poggit).
I will fix the issue as soon as possible.

### Contributions
You are free to contribute to the project.
If you open a pull request, make sure you contribute to the **development** branch.
Your code has to be readable, tested and bug-free.

### Flags
- **Locked:** The player requires permission (*easykits.kit.**kitname***) to claim the kit
- **doOverride:** The kit will take up the exact inventory slots assigned to the items
- **doOverrideArmor:** The kit will take up the exact armor slots assigned to the armor pieces
- **alwaysClaim:** Claim the kit even if he player lacks inventory space
- **emptyOnClaim:** Empty the player's inventory before claiming the kit
- **chestKit:** Make it so the kit is a chestkit

## Todo
- [ ] **One-per-life:** Support the option of only being able to claim 1 kit per life
- [ ] **Claim effects:** Add (optional)cosmetic effects when claiming kits
- [ ] **Kit & category icons:** Support icons in the kit select form.