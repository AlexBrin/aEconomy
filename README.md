aEconomy
========

Economy for MCPE

Events ([Action types](/src/AlexBrin/events/EconomyEvent.php#L16-L20)):
```
EconomyAddEvent
EconomyGiveEvent
EconomyReduceEvent
EconomySetEvent

$event->getAction();
$event->getSender(); // $event->getCommandSender()
$event->getAmount();
$event->setAmount(float $amount);
```

[API (source)](/src/AlexBrin/aEconomy.php#L97-L266):
```
use AlexBrin\aEconomy

$economy = aEconomy::getInstance();

$economy->addMoney(new ConsoleCommandSender, $player, float $amount);
$economy->reduceMoney(new ConsoleCommandSender, $player, float $amount);
$economy->setMoney(new ConsoleCommandSender, $player, float $amount);
```