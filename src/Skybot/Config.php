<?php

namespace Skybot;

use \Symfony\Component\Yaml\Yaml;

class Config
{
	public $skypename;
	public $plugin_dir;
    public $log_dir;

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

        if (isset($config['skybot']['log_dir'])) {
            $this->log_dir = $config['skybot']['log_dir'];
            if (!$this->log_dir) {
                throw new \Exception("Log dir directory is blank.");
            }
        } else {
            throw new \Exception("The config skybot.log_dir is missing!");
        }

    }

    public function getSkypeName()
    {
    	return $this->skypename;
    }

    public function getPluginDir()
    {
    	return $this->plugin_dir;
    }

    public function getLogDir()
    {
        return $this->log_dir;
    }    
}