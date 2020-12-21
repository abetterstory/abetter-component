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
			return $this->viewFind($data);
		};
    }

	// ---

	public function viewFind($data) {
		$class = get_called_class();
		$reflector = new \ReflectionClass($class);
		$path = dirname($reflector->getFileName());
		$view = (!empty($this->componentView)) ? $this->componentView : strtolower(class_basename($class));
		$attributes = $this->data()['attributes'] ?? [];
		$current = \Arr::last($GLOBALS['view_data'] ?? []);
		$climb = NULL;
		foreach ($attributes AS $key => $val) {
			if (preg_match('/^\-/',$key)) {
				$view .= $key;
			} else if (preg_match('/^\.+/',$key,$match)) {
				$view = ltrim($key,'.');
				$climb = $match[0];
			}
		}
		// 1. Try Class file dir
		\View::addLocation($path);
		if ($exist = $this->viewExist($view)) {
			return $exist;
		}
		// 2. Try Blade file dir
		if (!empty($current['path'])) {
			\View::addLocation($current['path']);
		}
		if ($exist = $this->viewExist($view)) {
			return $exist;
		}
		// 3. Try Parents dir
		\View::addLocation(dirname($path));
		if (!empty($current['path'])) {
			\View::addLocation(dirname($current['path']));
		}
		if ($exist = $this->viewExist($view)) {
			return $exist;
		}
		// 4. Try Grandparents dir
		if (count($climb) > 1) {
			\View::addLocation(dirname(dirname($path)));
			if (!empty($current['path'])) {
				\View::addLocation(dirname(dirname($current['path'])));
			}
			if ($exist = $this->viewExist($view)) {
				return $exist;
			}
		}
		// 5. Fallback
		return $this->viewFallback;
	}

	public function viewExist($view) {
		if (\View::exists($view)) return $view;
		$view .= '.'.\Arr::last(explode('.',$view)); // Test dir
		if (\View::exists($view)) return $view;
		return FALSE;
	}

}
