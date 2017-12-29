<?php

namespace ModBanSystem;

use pocketmine\scheduler\PluginTask;

class CountTask extends PluginTask {
	
	public function onRun(int $currentTick) {
		$this->getOwner()->updateTime();
	}
	
}
