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

class Main extends PluginBase implements Listener {

	public $banList = [];
	private $config;

	public function onEnable() {
		$this->getLogger()->info(TextFormat::GREEN . "ModBanSystem by BajanVlogs activated.");
		
		@mkdir($this->getDataFolder());
		@mkdir($this->getDataFolder() . "players/");
	  
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CountTask($this), 20);
		
	}

	public function onDisable() {
		
		$this->getLogger()->info(TextFormat::GREEN . "ModBanSystem by BajanVlogs disabled.");
	}
	

	public function onPlayerLogin(PlayerPreLoginEvent $event){
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
			
			if($ban == "True"){
				if($hour >= 1){
					$player->close("", TextFormat::RED . "You are still banned for " . TextFormat::AQUA . $hour . " hours. " . TextFormat::RED . "Comment: " . TextFormat::AQUA . $reason);
				} else {
					$totalMins = $totalTime / 60;
					if((int)$totalMins >= 2){
						$player->close("", TextFormat::RED . "You are still banned for " . TextFormat::AQUA . (int)$totalMins . " mins. " . TextFormat::RED . "Comment: " . TextFormat::AQUA . $reason);
					} else {
						$player->close("", TextFormat::RED . "You are still banned for less than " . TextFormat::AQUA . "1 min. " . TextFormat::RED . "Comment: " . TextFormat::AQUA . $reason);
					}
				}
				$event->setCancelled();
			}
			
			if($permBan == "True"){
				$player->close("", TextFormat::RED . "You are permanently banned");
				$event->setCancelled();
			}
			
		}
	}

	public function updateTime() {

		foreach($this->banList as $p){
			$name = trim(strtolower($p->getName()));
			$config = new Config($this->getDataFolder() . "players/" . strtolower($name) . ".yml", Config::YAML);
			$data = $config->getAll();
			$totalTime = $data["TotalTime"];
			$banCount = $data["BanCount"];
			
			$totalHour = $totalTime / 3600;
			$NewTime = $totalTime - 1;
			$config = new Config($this->getDataFolder() . "players/" . strtolower($name) . ".yml", Config::YAML);
			$config->set("Hour", (int)$totalHour);
			$config->set("TotalTime", $NewTime);
			$config->save();
			
			if($NewTime <= 0){
				unset($this->banList[strtolower($name)]);
				$config = new Config($this->getDataFolder() . "players/" . strtolower($name) . ".yml", Config::YAML);
				$config->set("Ban", "False");
				$config->set("Hour", 0);
				$config->set("TotalTime", 0);
				$config->set("Reason", "");
				$config->save();
			}

			if($banCount >= 168){
				$config = new Config($this->getDataFolder() . "players/" . strtolower($name) . ".yml", Config::YAML);
				$config->set("Ban", "False");
				$config->set("PermBan", "True");
				$config->set("BanCount", 0);
				$config->set("Hour", 0);
				$config->set("TotalTime", 0);
				$config->set("Reason", "");
				$config->save();
			}
			
		}
	}

	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
		
		switch($cmd->getName()){
			
			case "modban":
				if($sender instanceof Player) {
					if($sender->hasPermission("mod.modban")){
					
						$name = $this->getServer()->getPlayerExact($args[0]);
						
						if($args[1] == "0"){
							$hour = "6";
						} else if ($args[1] == ".5"){
							$hour = "12";
						} else if ($args[1] == "1"){
							$hour = "24";
						} else if ($args[1] == "2"){
							$hour = "48";
						} else if ($args[1] == "2.5"){
							$hour = "60";
						} else if ($args[1] == "3"){
							$hour = "72";
						} else if ($args[1] == "4"){
							$hour = "96";
						}
						
						if(count($args) >= 2) {
							$reason = "";
							for ($i = 2; $i < count($args); $i++) {
								$reason .= $args[$i];
								$reason .= " ";
							}
						}
						
						$reason = substr($reason, 0, strlen($reason) - 1);
						
						if($hour == "6"){
							$totalTime = 6 * 3600;
							$banCount = 0;
						} else if ($hour == "12"){
							$totalTime = 12 * 3600;
							$banCount = 0;
						} else if ($hour == "24"){
							$totalTime = 24 * 3600;
							$banCount = 24;
						} else if ($hour == "48"){
							$totalTime = 48 * 3600;
							$banCount = 48;
						} else if ($hour == "60"){
							$totalTime = 60 * 3600;
							$banCount = 60;
						} else if ($hour == "72"){
							$totalTime = 72 * 3600;
							$banCount = 72;
						} else if ($hour == "96"){
							$totalTime = 96 * 3600;
							$banCount = 96;
						}
					
						if($name instanceof Player) {
							$name->kick(TextFormat::RED . "You have been mod banned for " . TextFormat::AQUA . $hour . " hours. " . TextFormat::RED . "Comment: " . TextFormat::AQUA . $reason);
							$this->banList[strtolower($name->getName())] = $name;
							
							$name = trim(strtolower($name->getName()));
							$config = new Config($this->getDataFolder() . "players/" . strtolower($name) . ".yml", Config::YAML);
							$data = $config->getAll();
							$totalBanCount = $data["BanCount"];
							$newBanCount = $totalBanCount + $banCount;
							
							$config = new Config($this->getDataFolder() . "players/" . strtolower($name) . ".yml", Config::YAML);
							$config->set("Ban", "True");
							$config->set("BanCount", $newBanCount);
							$config->set("Hour", $hour);
							$config->set("TotalTime", $totalTime);
							$config->set("Reason", $reason);
							$config->save();
							$this->getServer()->broadcastMessage(TextFormat::WHITE . "•" .TextFormat::GREEN . $name . " has been [BANNED] for " . $reason . ".");
							$sender->sendMessage(TextFormat::WHITE . "•" . TextFormat::BLUE . $name . " has been banned!");
						}
					}
				}
				else{
					$sender->sendMessage(TextFormat::RED . "Use this Command in-game.");
					return true;
				}
			break;
			
			case "modbans":
				if($sender instanceof Player) {
					if($sender->hasPermission("mod.modbans")){
						$name = $this->getServer()->getPlayerExact($args[0]);
						$name = trim(strtolower($name->getName()));
						$config = new Config($this->getDataFolder() . "players/" . strtolower($name) . ".yml", Config::YAML);
						$data = $config->getAll();
						$banCount = $data["BanCount"];
						$totalDays = $banCount / 24;
						$sender->sendMessage(TextFormat::WHITE . "•" . $name . " has " . (int)$totalDays . " modban(s).");
					}
				}
			break;

			case "checkmodban":
				if($sender instanceof Player) {
					$name = trim(strtolower($sender->getName()));
					$config = new Config($this->getDataFolder() . "players/" . strtolower($name) . ".yml", Config::YAML);
					$data = $config->getAll();
					$warnCount = $data["WarnCount"];
					$banCount = $data["BanCount"];
					$totalDays = $banCount / 24;
					$sender->sendMessage(TextFormat::WHITE . "•" . TextFormat::AQUA . "You have " . (int)$totalDays . " modban(s) in the past 30 days.");
					$sender->sendMessage(TextFormat::WHITE . "•" . TextFormat::RED. "If you reach a total of 7. This account will be permanently banned.");
				}
			break;
			

			
			case "warn":
				if($sender instanceof Player) {
					if($sender->hasPermission("mod.warn")){
						$name = $this->getServer()->getPlayerExact($args[0]);
						
						if(count($args) >= 1) {
							$reason = "";
							for ($i = 1; $i < count($args); $i++) {
								$reason .= $args[$i];
								$reason .= " ";
							}
						}
						
						$reason = substr($reason, 0, strlen($reason) - 1);
						
						if($name instanceof Player) {
							$name->sendMessage(TextFormat::WHITE . "•" . TextFormat::RED . "You have been warned by " . $sender->getName() . " for " . $reason . ".");
							$name = trim(strtolower($name->getName()));
							$config = new Config($this->getDataFolder() . "players/" . strtolower($name) . ".yml", Config::YAML);
							$data = $config->getAll();
							$warnCount = $data["WarnCount"];
							$newWarnCount = $warnCount + 1;
							$config = new Config($this->getDataFolder() . "players/" . strtolower($name) . ".yml", Config::YAML);
							$config->set("WarnCount", $newWarnCount);
							$config->save();
							$this->getServer()->broadcastMessage(TextFormat::WHITE . "•" .TextFormat::GREEN . $name . " has been [WARNED] for " . $reason . ".");
							$sender->sendMessage(TextFormat::WHITE . "•" . TextFormat::BLUE . $name . " has been warned!");
						}
					}
				}
				else{
					$sender->sendMessage(TextFormat::RED . "Use this Command in-game.");
					return true;
				}
			break;
			
			case "warns":
				if($sender instanceof Player) {
					if(count($args) == 1){
						$name = $this->getServer()->getPlayerExact($args[0]);
						$name = trim(strtolower($name->getName()));
						$config = new Config($this->getDataFolder() . "players/" . strtolower($name) . ".yml", Config::YAML);
						$data = $config->getAll();
						$warnCount = $data["WarnCount"];
						$sender->sendMessage(TextFormat::WHITE . "•" . $name . " has " . $warnCount . " warning(s).");
					} else {
						$name = trim(strtolower($sender->getName()));
						$config = new Config($this->getDataFolder() . "players/" . strtolower($name) . ".yml", Config::YAML);
						$data = $config->getAll();
						$warnCount = $data["WarnCount"];
						$sender->sendMessage(TextFormat::WHITE . "•You have " . $warnCount . " warning(s).");
					}
						
				}
			break;
			
			case "permban":
				if($sender instanceof Player) {
					if($sender->hasPermission("mod.permban")){
						$name = $this->getServer()->getPlayerExact($args[0]);
						
						if(count($args) >= 1) {
							$reason = "";
							for ($i = 1; $i < count($args); $i++) {
								$reason .= $args[$i];
								$reason .= " ";
							}
						}
						
						$reason = substr($reason, 0, strlen($reason) - 1);
						
						if($name instanceof Player) {
							$name->kick(TextFormat::RED . "You have been permanently banned. " . TextFormat::RED . "Comment: " . TextFormat::AQUA . $reason);
							
							$name = trim(strtolower($name->getName()));
							$config = new Config($this->getDataFolder() . "players/" . strtolower($name) . ".yml", Config::YAML);
							$config->set("Ban", "False");
							$config->set("PermBan", "True");
							$config->set("BanCount", 0);
							$config->set("Hour", 0);
							$config->set("TotalTime", 0);
							$config->set("Reason", $reason);
							$config->save();
							$this->getServer()->broadcastMessage(TextFormat::WHITE . "•" .TextFormat::GREEN . $name . " has been [PERMANENTLY BANNED] for " . $reason . ".");
							$sender->sendMessage(TextFormat::WHITE . "•" . TextFormat::BLUE . $name . " has been permanently banned!");
						}
					}
				}
				else{
					$sender->sendMessage(TextFormat::RED . "Use this Command in-game.");
					return true;
				}
			break;
			
			case "unban":
				if($sender instanceof Player) {
					if($sender->hasPermission("mod.unban")){
						$name = $args[0];
						$config = new Config($this->getDataFolder() . "players/" . strtolower($name) . ".yml", Config::YAML);
						$config->set("Ban", "False");
						$config->set("PermBan", "False");
						$config->set("Hour", 0);
						$config->set("TotalTime", 0);
						$config->set("Reason", "");
						$config->save();
						$sender->sendMessage(TextFormat::WHITE . "•" . $name . " has been unbanned!");
					}
				}
			break;
			
		}
		return true;
	}

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
        $name = trim(strtolower($playersname));
        if ($name === "") {
            return null;
        }
        $path = $this->getDataFolder() . "players/" . $name . ".yml";
        if (!file_exists($path)) {
            return null;
        } else {
            $config = new Config($path, Config::YAML);
            return $config->getAll();
        }
    }

}
