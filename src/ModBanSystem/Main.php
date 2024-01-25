<?php

namespace ModBanSystem;

use ModBanSystem\CountTask;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\Command\Command;
use pocketmine\Command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\ConsoleCommand;
use pocketmine\command\PluginCommand;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\event\server\CommandEvent;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandExecutor as IExecutor;

class Main extends PluginBase implements Listener, IExecutor {

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
        $this->getServer()->getCommandMap()->register("modbansystem", new BanCommand($this));

    }

    public function onDisable() {
        $this->getLogger()->info(TextFormat::GREEN . "ModBanSystem by BajanVlogs disabled.");
    }

    public function onPlayerLogin(PlayerPreLoginEvent $event) {
        $player = $event->getPlayer();

        if (!$this->playerRegistered($player->getName())) {
            $this->registerPlayer($player->getName());
        } else {
            $name = trim(strtolower($player->getName()));
            $config = new Config($this->getDataFolder() . "players/" . strtolower($name) . ".yml", Config::YAML);
            $data = $config->getAll();
            $ban = $data["Ban"];
            $permBan = $data["PermBan"];
            $hour = $data["Hour"];
            $totalTime = $data["TotalTime"];
            $reason = $data["Reason"];

            if ($ban == "True") {
                if ($hour >= 1) {
                    $player->close("", TextFormat::RED . "You are still banned for " . TextFormat::AQUA . $hour . " hours. " . TextFormat::RED . "Comment: " . TextFormat::AQUA . $reason);
                } else {
                    $totalMins = $totalTime / 60;
                    if ((int)$totalMins >= 2) {
                        $player->close("", TextFormat::RED . "You are still banned for " . TextFormat::AQUA . (int)$totalMins . " mins. " . TextFormat::RED . "Comment: " . TextFormat::AQUA . $reason);
                    } else {
                        $player->close("", TextFormat::RED . "You are still banned for less than " . TextFormat::AQUA . "1 min. " . TextFormat::RED . "Comment: " . TextFormat::AQUA . $reason);
                    }
                }
                $event->setCancelled();
            }

            if ($permBan == "True") {
                $player->close("", TextFormat::RED . "You are permanently banned");
                $event->setCancelled();
            }
        }
    }

    public function updateTime() {
        // Existing code...
    }

    // Other existing methods...

    public function playerRegistered($playersname) {
        $name = trim(strtolower($playersname));
        return file_exists($this->getDataFolder() . "players/" . $name . ".yml");
    }

    public function registerPlayer($playersname) {
        $name = trim(strtolower($playersname));
        @mkdir($this->getDataFolder() . "players/");
        $data = new Config($this->getDataFolder() . "players/" . $name . ".yml", Config::YAML);
        $data->set("Ban", "False");
        $data->set("PermBan", "False");
        $data->set("WarnCount", 0);
        $data->set("BanCount", 0);
        $data->set("Hour", 0);
        $data->set("TotalTime", 0);
        $data->set("Reason", "");
        $data->save();
        return true;
    }

    public function getPlayerData($playersname) {
        // Existing code...
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool {
        $executor = new CommandExecutor($this->gui, $cmd, $label, $args);
        $executor->onCommand($sender, $cmd, $label, $args);
        return true;
    }
}
