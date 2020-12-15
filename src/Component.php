<?php

namespace ABetter\Component;

use Illuminate\Support\Collection;
use Illuminate\View\Component as LaravelComponent;

class Component extends LaravelComponent {

	public $componentVar;
	public $viewFallback = 'components.missing.missing';

	// ---

	public function __construct() {
		$this->boot(...func_get_args());
	}

	// ---

	public function boot() {
		$this->componentVar = class_basename(get_called_class());
		$this->{$this->componentVar} = new Collector($this,$this->componentVar);
		$this->service();
		$this->build();
	}

	public function service() {
		//
	}

	public function build() {
		//
	}

	// ---

	public function data() {
        $data = parent::data();
		$data[$this->componentVar] = $this->{$this->componentVar};
		return $data;
	}

	public function render() {
		return function($data) {
			return $this->findView($data);
		};
    }

	// ---

	public function findView($data) {
		$class = get_called_class();
		$reflector = new \ReflectionClass($class);
		$path = dirname($reflector->getFileName());
		$view = strtolower(class_basename($class));
		$attributes = $this->data()['attributes'] ?? [];
		foreach ($attributes AS $key => $val) {
			if (preg_match('/^\./',$key)) {
				$view = $key;
			}
			if (preg_match('/^\-/',$key)) {
				$view .= $key;
			}
		}
		\View::addLocation($path);
		if (!\View::exists($view)) {
			return $this->viewFallback;
		}
		return $view;
	}

}
