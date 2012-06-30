<?php

/*
* This file is part of Skybot
*
* (c) 2012 Morten Amundsen <mor10am@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Skybot\Plugin;

use Skybot\BasePlugin;
use Skybot\PluginInterface;
use Skybot\Skype\Reply;

class Brain extends BasePlugin implements PluginInterface
{
	protected $regexp = "/^\@(get|set) (.*)$/ms";
	protected $description = "Key/value storage";

	public function handle($chatmsg, $result)
	{
		$dic = $chatmsg->getDic();

		if (!isset($dic['storage'])) {
			throw new \Exception("There is no storage defined.");
		}

		$storage = $dic['storage'];

		if ($result[1] == 'set') {
			$tmp = explode(" ", $result[2]);

			$field = $tmp[0];
			unset($tmp[0]);
			$value = implode(" ", $tmp);

			if ($field and $value) {
				$storage->set($chatmsg->getSkypeName(), $field, $value);
				return "Saved key ".$field;
			} else {
				return "Missing key and/or value";
			}
		} elseif ($result[1] == 'get') {
			$tmp = explode(" ", $result[2]);

			$field = $tmp[0];
			unset($tmp[0]);

			if ($field) {
				$value = $storage->get($chatmsg->getSkypeName(), $field);

				if (!$value) {
					return "Key $field does not have any value";
				} else {
					return $field."=".$value;
				}
			} else {
				return "Missing key";
			}
		}
	}
}