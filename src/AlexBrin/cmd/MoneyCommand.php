<?php

/**
 * Команда /money
 * Код защищен авторским правом
 * © Alex Brin, 2017
 */

namespace AlexBrin\cmd;

use AlexBrin\aEconomy;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class MoneyCommand extends Command {

    public function __construct($name, $description = "", $permission = '', $usageMessage = null, $aliases = []) {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission($permission);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param string[] $args
     *
     * @return mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        switch(array_shift($args)) {

            case 'help':
                    $sender->sendMessage($this->help());
                break;

            case 'my':
                    if(!$sender instanceof Player)
                        return false;

                    $money = aEconomy::getInstance()->getMoney($sender);
                    $sender->sendMessage(
                        $this->getMessage('balance', [$money])
                    );
                break;

            case 'give':
                    $player = array_shift($args);
                    if(!$player) {
                        $sender->sendMessage($this->getUsage());
                        return true;
                    }

                    $amount = array_shift($args);
                    if(!$amount) {
                        $sender->sendMessage($this->getUsage());
                        return true;
                    }

                    aEconomy::getInstance()->giveMoney($sender, $player, $amount);
                break;

            case 'add':
                    if($sender->hasPermission('aeconomy.admin')) {
                        $sender->sendMessage($this->getMessage('perm'));
                        return true;
                    }

                    $player = array_shift($args);
                    if(!$player) {
                        $sender->sendMessage($this->getUsage());
                        return true;
                    }

                    $amount = array_shift($args);
                    if(!$amount) {
                        $sender->sendMessage($this->getUsage());
                        return true;
                    }

                    aEconomy::getInstance()->addMoney($sender, $player, $amount);
                break;

            case 'reduce':
                    if($sender->hasPermission('aeconomy.admin')) {
                        $sender->sendMessage($this->getMessage('perm'));
                        return true;
                    }

                    $player = array_shift($args);
                    if(!$player) {
                        $sender->sendMessage($this->getUsage());
                        return true;
                    }

                    $amount = array_shift($args);
                    if(!$amount) {
                        $sender->sendMessage($this->getUsage());
                        return true;
                    }

                    aEconomy::getInstance()->reduceMoney($sender, $player, $amount);
                break;

            case 'set':
                    if($sender->hasPermission('aeconomy.admin')) {
                        $sender->sendMessage($this->getMessage('perm'));
                        return true;
                    }

                    $player = array_shift($args);
                    if(!$player) {
                        $sender->sendMessage($this->getUsage());
                        return true;
                    }

                    $amount = array_shift($args);
                    if(!$amount) {
                        $sender->sendMessage($this->getUsage());
                        return true;
                    }

                    aEconomy::getInstance()->setMoney($sender, $player, $amount);
                break;

        }
        return true;
    }

    public function getMessage($node, $vars = []) {
        aEconomy::getInstance()->getMessage($node, $vars);
    }

    private function help(): string {
        return aEconomy::getInstance()->getHelp();
    }
}