<?php
/**
 * Основной класс
 * Код защищен авторским правом
 * © Alex Brin, 2017
 */

declare(strict_types=1);

namespace AlexBrin;

use AlexBrin\cmd\MoneyCommand;
use AlexBrin\events\EconomyAddEvent;
use AlexBrin\events\EconomyEvent;
use AlexBrin\events\EconomyGiveEvent;
use AlexBrin\events\EconomyReduceMoney;
use AlexBrin\events\EconomySetEvent;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\OfflinePlayer;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\event\Listener;

class aEconomy extends PluginBase implements Listener {

    /**
     * @var Config $config
     */
    private $config;

    /**
     * @var Config $players
     */
    private $players;

    private static $instance;

    public function onEnable() {
        $f = $this->getDataFolder();
        if(!is_dir($f))
            @mkdir($f);

        $this->saveResource('config.yml');
        $this->config = new Config($f.'config.yml', Config::YAML);
        $this->players = new Config($f.'players.json', Config::JSON);

        $this->getServer()->getPluginManager()->registerEvents(new EventHandler($this), $this);

        $this->getServer()->getCommandMap()->register(
            'money',
            new MoneyCommand(
                'money',
                'Economy',
                'aeconomy.use',
                '/money help',
                ['eco', 'mymoney', 'economy']
            )
        );

        self::$instance = &$this;
    }

    public function getMessage($node, $vars = []): string {
        $message = $this->config->getNested('messages.' . $node, '');

        $i = 0;
        foreach($vars as $var) {
            $message = str_replace("%var$i%", $var, $message);
            $i++;
        }

        return $message;
    }

    public function getParam($node, $default) {
        return $this->config->getNested('params.' . $node, $default);
    }

    public function getHelp(): string {
        return implode(
            "\n",
            $this->config->getNested('messages.help', [])
        );
    }

    /**
     * @param  string|OfflinePlayer|Player $player
     * @return string
     */
    public function getPlayer($player): string {
        if($player instanceof Player || $player instanceof OfflinePlayer)
            $player = $player->getName();

        return mb_strtolower($player);
    }

    ##################################
    #               API
    ##################################


    public function getMoney($player) {
        return $this->players->get($this->getPlayer($player), 0);
    }

    /**
     * @param string|Player $player
     * @param float $amount
     * @return bool
     * @param CommandSender $sender
     */
    public function addMoney($player, float $amount, CommandSender $sender = null): bool {
        if($sender === null)
            $sender = new ConsoleCommandSender();

        if(!$player instanceof Player && !$player instanceof OfflinePlayer)
            $player = $this->getServer()->getPlayer($player) ?? $this->getServer()->getOfflinePlayer($player);

        $ev = new EconomyAddEvent($player, $amount, EconomyEvent::ECONOMY_ACTION_ADD, $sender);
        if($ev->isCancelled())
            return false;

        $this->players->set(
            $this->getPlayer($player),
            $this->getMoney($player) + $ev->getAmount()
        );
        $this->players->save();

        $sender->sendMessage(
            $this->getMessage(
                'add.in',
                [
                    $ev->getAmount(),
                    $player->getName()
                ]
            )
        );

        if($player instanceof Player)
            $player->sendMessage(
                $this->getMessage(
                    'add.out',
                    [
                        $ev->getAmount(),
                        $this->getMoney($player)
                    ]
                )
            );
        return true;
    }

    /**
     * @param string|Player $player
     * @param float $amount
     * @param CommandSender $sender
     * @return bool
     */
    public function setMoney($player, float $amount, CommandSender $sender = null): bool {
        if($sender === null)
            $sender = new ConsoleCommandSender();

        if(!$player instanceof Player && !$player instanceof OfflinePlayer)
            $player = $this->getServer()->getPlayer($player) ?? $this->getServer()->getOfflinePlayer($player);

        $ev = new EconomySetEvent($player, $amount, EconomyEvent::ECONOMY_ACTION_SET, $sender);
        if($ev->isCancelled())
            return false;

        $this->players->set(
            $this->getPlayer($player),
            $ev->getAmount()
        );
        $this->players->save();

        $sender->sendMessage(
            $this->getMessage(
                'set.in',
                [
                    $ev->getAmount(),
                    $player->getName()
                ]
            )
        );

        if($player instanceof Player)
            $player->sendMessage(
                $this->getMessage(
                    'set.out',
                    [$ev->getAmount(), $player->getName()]
                )
            );

        return true;
    }

    /**
     * @param $player
     * @param float $amount
     * @param CommandSender $sender
     * @return bool
     */
    public function reduceMoney($player, float $amount, CommandSender $sender = null): bool {
        if($sender === null)
            $sender = new ConsoleCommandSender();

        if(!$player instanceof Player && !$player instanceof OfflinePlayer)
            $player = $this->getServer()->getPlayer($player) ?? $this->getServer()->getOfflinePlayer($player);

        $ev = new EconomyReduceMoney($player, $amount, EconomyEvent::ECONOMY_ACTION_REDUCE, $sender);
        if($ev->isCancelled())
            return false;

        $this->players->set(
            $this->getPlayer($player),
            $this->getMoney($player) - $ev->getAmount()
        );

        $sender->sendMessage(
            $this->getMessage(
                'reduce.in',
                [$ev->getAmount(), $player->getName()]
            )
        );

        $this->players->save();

        if($player instanceof Player)
            $player->sendMessage(
                $this->getMessage(
                    'reduce.out',
                    [$ev->getAmount()]
                )
            );

        return true;
    }

    /**
     * @param $player
     * @param float $amount
     * @param CommandSender $sender
     * @return bool
     */
    public function giveMoney($player, float $amount, CommandSender $sender = null): bool {
        if($sender === null)
            $sender = new ConsoleCommandSender();

        if(!$player instanceof Player && !$player instanceof OfflinePlayer)
            $player = $this->getServer()->getPlayer($player) ?? $this->getServer()->getOfflinePlayer($player);

        $ev = new EconomyGiveEvent($player, $amount, EconomyEvent::ECONOMY_ACTION_GIVE, $sender);
        if($ev->isCancelled())
            return false;

        if($sender instanceof Player)
            $this->players->set(
                $this->getPlayer($sender),
                $this->getMoney($sender) - $ev->getAmount()
            );
        $this->players->set(
            $this->getPlayer($player),
            $this->getMoney($player) + $ev->getAmount()
        );
        $this->players->save();

        $sender->sendMessage(
            $this->getMessage(
                'give.in',
                [$ev->getAmount(), $player->getName()]
            )
        );

        if($player instanceof Player)
            $player->sendMessage(
                $this->getMessage(
                    'give.out',
                    [$ev->getAmount(), $player->getName()]
                )
            );

        return true;
    }


    /**
     * @return aEconomy
     */
    public static function getInstance(): aEconomy {
        return self::$instance;
    }

}