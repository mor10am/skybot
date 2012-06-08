<?php

namespace Skybot\Plugin;

use Skybot\BasePlugin;
use Skybot\PluginInterface;

class CDK extends BasePlugin implements PluginInterface
{
	protected $regexp = "/^cdk\ [0-9]+$/";
	protected $description = "Get customerinfo from KAS";

	public function handle($result, $handle)
	{
		$customerid = $result[1];

		$url = "http://ws-01.teleperf.net/REST/cdk/subscriber/{$customerid}.php";

		$data = unserialize(file_get_contents($url));

		if (is_object($data) and isset($data->result)) {
			$text = "\nCUSTOMERDATA FROM KAS:\n\n";
			foreach ($data->result as $field => $value) {
				$text .= $field ." = " . $value . "\n";
			}

			$this->reply($text);

		} else {
			throw new \Exception("Customer $customerid not found.");
		}

		return true;
	}
}