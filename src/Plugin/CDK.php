<?php

class Plugin_CDK extends BasePlugin implements PluginInterface
{
	public function parse(Skype_Message $message)
	{

		if (!preg_match("/^cdk\ (\d+)/", $message->getBody(), $results)) return false;

		$customerid = $results[1];

		$url = "http://ws-01.teleperf.net/REST/cdk/subscriber/{$customerid}.php";

		$data = unserialize(file_get_contents($url));

		if (is_object($data) and isset($data->result)) {
			$text = "\nCUSTOMERDATA FROM KAS:\n\n";
			foreach ($data->result as $field => $value) {
				$text .= $field ." = " . $value . "\n";
			}
			$message->reply($text);
		} else {
			throw new Exception("Customer $customerid not found.");
		}

		return true;
	}
}