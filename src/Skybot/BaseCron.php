<?php

namespace Skybot;

use Cron\CronExpression;
use Skybot\Message\Chat;

abstract class BaseCron
{
	private $skybot;
	private $due = false;

	public function __construct(\Skybot\Main $skybot)
	{
		$this->skybot = $skybot;
	}

	public function getExpression()
	{
		return $this->expression;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function run()
	{
		$cron = CronExpression::factory($this->getExpression());

		$a = (string) $cron->isDue();
		$b = (string) $this->due;

		if ($cron->isDue() and $this->due) {
			$this->due = false;
			return $this->handle();
		} elseif (!$cron->isDue()) {
			$this->due = true;
			return false;
		}
	}

	protected function createChat($chatname, $txt)
	{
		if (!$chatid = $this->skybot->findChat($chatname)) {
			throw new \Exception("Chat with name $chatname was not found");
		}

		$chatmsg = new Chat(null, $chatid, $this->skybot);
		$chatmsg->setContactName($this->skybot->getContactName());
		$chatmsg->setBody($txt);

		return $chatmsg;
	}
}