<?php

namespace Skybot\Plugin;

use Skybot\BasePlugin;
use Skybot\PluginInterface;

class SMS extends BasePlugin implements PluginInterface
{
	protected $regexp = "/^sms\ (\d{8})\ (.*)/";
	protected $description = "Send SMS to Norwegian mobilephones.";

	public function handle($result, $message)
	{
		$number = $result[1];
		$txt = trim($result[2]);

		$first = substr($number, 0, 1);

		if ($first != 4 and $first != 9) {
			$this->reply("$number is not valid mobile in Norway");
			return true;
		}

		if (!$txt) {
			$this->reply("No message specified to send to $number");
		}

		$txt .= " [from $handle via SKYBOT]";

    	$url = "http://10.0.1.24/ss/generic_sms.php?projectid=SKYBOT&number={$number}&text=".urlencode($txt);
    
    	file_get_contents($url);

    	$message->reply("SMS sent to $number : " . $txt);

		return true;
	}
}