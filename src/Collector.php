<?php

namespace ABetter\Component;

class Collector {

	public $component;
	public $var;
	public $debug;
	public $dev;

	// ---

	public function __construct() {
		$this->boot(...func_get_args());
	}

	// ---

	public function __call($method, $arguments) {
		if (isset($this->$method) && is_callable($this->$method)) {
			return call_user_func($this->$method, ...$arguments);
		}
	}

	public function boot($component=NULL,$var=NULL) {
		$this->component = $component;
		$this->var = $var;
		$this->debug = (env('APP_DEBUG')) ? TRUE : FALSE;
		$this->dev = (in_array(strtolower(env('APP_ENV')),[
			'stage',
			'production'
		])) ? FALSE : TRUE;
	}

	// ---

	public function dev($dev=NULL) {
		$this->dev = ($dev !== NULL) ? $dev : TRUE;
	}

	// ---

	public function set($method, $return=NULL, $options=[]) {
		switch (gettype($return)) {
			case 'boolean' : return $this->setBoolean($method,$return,$options);
			case 'integer' : return $this->setInteger($method,$return,$options);
			case 'double' : return $this->setFloat($method,$return,$options);
			case 'string' : return $this->setString($method,$return,$options);
			case 'array' : return $this->setArray($method,$return,$options);
			case 'object' : return $this->setObject($method,$return,$options);
		}
		return $this->setMethod($method,$return,$options);
	}

	public function setBoolean($method, $return=NULL, $options=[]) {
		return (boolean) $this->setMethod($method,$return,$options);
	}

	public function setInteger($method, $return=NULL, $options=[]) {
		return (integer) $this->setMethod($method,$return,$options);
	}

	public function setFloat($method, $return=NULL, $options=[]) {
		return (float) $this->setMethod($method,$return,$options);
	}

	public function setString($method, $return=NULL, $options=[]) {
		return (string) $this->setMethod($method,$return,$options);
	}

	public function setArray($method, $return=NULL, $options=[]) {
		return (array) $this->setMethod($method,$return,$options);
	}

	public function setObject($method, $return=NULL, $options=[]) {
		return (object) $this->setMethod($method,$return,$options);
	}

	// ---

	public function setMethod($method, $return=NULL, $options=[]) {
		if (empty($method)) return $this->setEmpty($return);
		$options = (empty($options) && is_array($return)) ? $return : $options;
		$data = NULL;
		if (method_exists($this,$method)) {
			$data = $this->{$method}($options) ?? NULL;
		}
		if ($data === NULL && method_exists($this->component,$method)) {
			$data = $this->component->{$method}($options) ?? NULL;
		}
		if ($data === NULL && $this->debug) {
			echo "<!-- missing-data:{$this->var}.{$method} -->";
		}
		return ($data === NULL) ? $this->setEmpty($return) : $data;
	}

	public function setEmpty($return) {
		return (!empty($this->dev)) ? $return : "";
	}

}
