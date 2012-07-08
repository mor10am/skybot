<?php

/*
* This file is part of Skybot
*
* (c) 2012 Morten Amundsen <mor10am@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Skybot;

class Storage extends \SQLite3
{

	public function __construct($filename)
	{
		parent::__construct($filename);

		$sql = 'CREATE  TABLE  IF NOT EXISTS "main"."brain" ("field" VARCHAR NOT NULL , "contactname" VARCHAR NOT NULL , "value" VARCHAR, PRIMARY KEY ("field", "contactname"))';

		$rs = $this->query($sql);
	}

	public function set($contactname, $field, $value)
	{
		$field = mb_strtolower($field);

		$sql = sprintf("insert or replace into brain (field, contactname, value) values ('%s', '%s', '%s')", $field, $contactname, $value);

		$this->exec($sql);
	}

	public function get($contactname, $field)
	{
		$field = mb_strtolower($field);

		$sql = sprintf("select value from brain where field = '%s' and contactname = '%s'", $field, $contactname);

		$rs = $this->query($sql);

		$data = $rs->fetchArray();

		return $data['value'];
	}
}