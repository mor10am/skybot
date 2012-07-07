<?php

namespace Skybot\Cron;

use Skybot\BaseCron;
use Skybot\CronInterface;

class QOTD extends BaseCron implements CronInterface
{
	protected $expression = "0 12 * * 1-5";

	public function handle()
	{
		$data = file_get_contents("http://www.iheartquotes.com/api/v1/random");

		$quote = preg_replace("/\s*\[\w+\]\s*http:\/\/iheartquotes.*\s*$/m", '', $data);

		return $this->createChat('shoutwall', "echo $quote");
	}
}