<?php

declare(strict_types=1);

namespace Shisui\TPHub;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\Server;
use pocketmine\scheduler\Task;
use pocketmine\event\player\PlayerQuitEvent;

class Main extends PluginBase implements Listener {

	private $lastExec = [];

	private $config = [];

	public function onEnable() {
		$this->saveDefaultConfig();
		$this->config = $this->getConfig()->getAll();

		if (is_numeric($this->config["temp"])) {
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
		} else {
			$this->getServer()->getLogger()->error("[TPHub+] J'ai désactiver le plugin suite a une erreur dans le config.yml ou tu a corrompu mes fichier");
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}
	}

	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
		switch ($cmd->getName()) {
			case 'hub':
				if ($sender instanceof Player) {
					$name = $sender->getName();
					if ((isset($this->lastExec[$name])) && (($this->lastExec[$name] + 5 + $this->config["temp"]) > (microtime(true)))) {
						$sender->sendMessage($this->config["msg_rapide"]);
					} else {
						$this->getScheduler()->scheduletempedTask(new HubTask($this, $sender->getName()), (20*$this->config["temp"]));
						$message = str_replace("{temp}", $this->config["temp"], $this->config["tp_chargement"]);
						$sender->sendMessage($message);
						$this->lastExec[$name] = microtime(true);
					}
					if (!isset($this->lastExec[$name])) {
						$this->lastExec[$name] = microtime(true);
					}
				} else {
					$sender->sendMessage("§eExecute cette commande en jeu et non sur la console.");
				}
			break;
		}
		return true;
	}

	public function onPlayerQuit(PlayerQuitEvent $evt) {
		unset($this->lastExec[$evt->getPlayer()->getName()]);
		
	}
}
