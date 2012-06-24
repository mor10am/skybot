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

use \Symfony\Component\Yaml\Yaml;

class Config
{
	public $skypename;
	public $plugin_dir;
    public $filter_dir;
    public $log_dir;
    public $server_port;

    public $conf = array();

	public function __construct($filename)
	{
		$this->_load($filename);
	}

	private function _load($filename)
    {
    	if (!file_exists($filename)) {
    		throw new \InvalidArgumentException("Config file $filename does not exist.");
    	}

        $config = Yaml::parse($filename);
 
        $this->conf = $config['skybot'];

        if (isset($config['skybot']['skypename'])) {
        	$this->skypename = $config['skybot']['skypename'];
        	if (!$this->skypename) {
        		throw new \Exception("Skypename is blank.");
        	}
        } else {
        	throw new \Exception("The config skybot.skypename is missing!");
        }

        if (isset($config['skybot']['plugin_dir'])) {
        	$this->plugin_dir = $config['skybot']['plugin_dir'];
        	if (!$this->plugin_dir) {
        		throw new \Exception("Plugin directory is blank.");
        	}
        } else {
        	throw new \Exception("The config skybot.plugin_dir is missing!");
        }

        if (isset($config['skybot']['filter_dir'])) {
            $this->filter_dir = $config['skybot']['filter_dir'];
            if (!$this->filter_dir) {
                throw new \Exception("Filter directory is blank.");
            }
        } else {
            throw new \Exception("The config skybot.plugin_dir is missing!");
        }        

        if (isset($config['skybot']['log_dir'])) {
            $this->log_dir = $config['skybot']['log_dir'];
            if (!$this->log_dir) {
                throw new \Exception("Log dir directory is blank.");
            }
        } else {
            throw new \Exception("The config skybot.log_dir is missing!");
        }

        if (isset($config['skybot']['server_port'])) {
            $this->server_port = $config['skybot']['server_port'];
        } else {
            throw new \Exception("The config skybot.server_port is missing!");
        }

    }

    public function __get($field)
    {
        if (isset($this->conf[$field])) {
            return $this->conf[$field];
        }
    }

    public function __set($field, $value)
    {
        $this->conf[$field] = $value;
    }

    public function getSkypeName()
    {
    	return $this->skypename;
    }

    public function getPluginDir()
    {
    	return $this->plugin_dir;
    }

    public function getFilterDir()
    {
        return $this->filter_dir;
    }

    public function getLogDir()
    {
        return $this->log_dir;
    }    

    public function getServerPort()
    {
        return $this->server_port;
    }
}