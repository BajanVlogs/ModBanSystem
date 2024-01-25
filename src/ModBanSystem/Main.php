<?php

namespace ModBanSystem;

use ModBanSystem\CountTask;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;

class Main extends PluginBase implements Listener {

    public $banList = [];
    private $config;
    private $gui;

    public function onEnable() {
        $this->getLogger()->info(TextFormat::GREEN . "ModBanSystem by BajanVlogs activated.");

        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder() . "players/");

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new CountTask($this), 20);

        $this->gui = new BanGUI($this);
        $this->getServer()->getPluginManager()->registerEvents($this->gui, $this);

        $command = new PluginCommand("modbansystem", $this);
        $command->setDescription("ModBanSystem GUI");
        $this->getServer()->getCommandMap()->register("modbansystem", $command);
    }

    public function onDisable() {
        $this->getLogger()->info(TextFormat::GREEN . "ModBanSystem by BajanVlogs disabled.");
    }

    public function onPlayerLogin(PlayerPreLoginEvent $event) {
        // Existing code...
    }

    public function updateTime() {
        // Existing code...
    }

    public function onCommand(CommandSender $sender, \pocketmine\command\Command $command, string $label, array $args): bool {
        $this->gui->openGUI($sender);
        return true;
    }

    public function playerRegistered($playersname) {
        // Existing code...
    }

    public function registerPlayer($playersname) {
        // Existing code...
    }

    public function getPlayerData($playersname) {
        // Existing code...
    }
}
