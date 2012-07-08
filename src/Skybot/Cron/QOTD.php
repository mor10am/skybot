<?php

/*
* This file is part of Skybot
*
* (c) 2012 Morten Amundsen <mor10am@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Skybot\Cron;

use Skybot\BaseCron;
use Skybot\CronInterface;

class QOTD extends BaseCron implements CronInterface
{
	protected $expression = "0 12 * * 1-5";
	protected $description = "Quote of the day";
	/**
	 * taken from Hubot
	 * https://github.com/github/hubot-scripts/blob/master/src/scripts/quote.coffee
	 *
	 */
	public function handle()
	{
		$data = file_get_contents("http://www.iheartquotes.com/api/v1/random");

		$quote = preg_replace("/\s*\[\w+\]\s*http:\/\/iheartquotes.*\s*$/m", '', $data);

		return $this->createChat('shoutwall', "echo $quote");
	}
}