<?php

namespace ABetter\Component;

use Illuminate\Support\Collection;
use Illuminate\View\Component as LaravelComponent;

class Component extends LaravelComponent {

	public $componentVar;
	public $componentView;
	public $viewFallback = 'components.missing.missing';

	// ---

	public function __construct() {
		$this->boot(...func_get_args());
	}

	// ---

	public function boot() {
		$this->componentVar = (!empty($this->componentVar)) ? $this->componentVar : class_basename(get_called_class());
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
		$view = (!empty($this->componentView)) ? $this->componentView : strtolower(class_basename($class));
		$current = \Arr::last($GLOBALS['view_data'] ?? []);
		$attributes = $this->data()['attributes'] ?? [];
		\View::addLocation($path);
		if (!empty($current['path'])) {
			\View::addLocation($current['path']);
		}
		foreach ($attributes AS $key => $val) {
			if (preg_match('/^\-/',$key)) {
				$view .= $key;
			} else if (preg_match('/^\.+/',$key)) {
				$view = ltrim($key,'.');
				\View::addLocation(dirname($path)); // Test parent
				\View::addLocation(dirname(dirname($path))); // Test grandparent
			}
		}
		if (!\View::exists($view)) {
			$view .= '.'.\Arr::last(explode('.',$view)); // Test dir
		}
		if (!\View::exists($view)) {
			$view = $this->viewFallback;
		}
		return $view;
	}

}
