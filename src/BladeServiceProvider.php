<?php

namespace ABetter\Component;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class BladeServiceProvider extends ServiceProvider {

    public function boot() {

		view()->addLocation(base_path().'/vendor/abetter/component/views');

		// Component (extends laravel component directive)
		Blade::directive('component', function($expression){
			list($path,$vars,$end) = self::parseExpression($expression);
			if (!$view = self::componentExists(self::parseRelativePath($path))) { // Test relative
				if (!$view = self::componentExists($path)) { // Test original
					$view = 'components.missing.missing'; // Fallback
				}
			}
			return "<?php \$__env->startComponent('{$view}', array_merge({$vars},['component_name' => '{$path}'])); ?>";
		});

    }

    public function register() {
        //
    }

	// ---

	public static function parseExpression($parse) {
		$id = trim(strtok($parse,','));
		$vars = trim(str_replace($id,'',$parse),',');
		$vars = preg_replace('/(\'|") ?(=&gt;|=) ?(\'|")/',"$1 => $3",$vars);
		$end = trim(preg_match('/, ?(end|true|1)$/i',$parse));
		if ($end) $vars = trim(substr($vars,0,strrpos($vars,',')));
		$exp = array();
		$exp[0] = trim($id,'\'');
		$exp[1] = ($vars) ? $vars : '[]';
		$exp[2] = ($end) ? TRUE : FALSE;
		return $exp;
	}

	public static function parseRelativePath($path) {
		global $view_data; $data = \Arr::first($view_data);
		$current = substr($data['name'],0,strrpos($data['name'],'.'));
		return $current.'.'.ltrim($path,'~');
	}

	public static function componentExists($path) {
		if (empty($path)) return FALSE;
		if (!\View::exists($path)) $path .= '.'.\Arr::last(explode('.',$path)); // Test if folder
		if (!\View::exists($path)) return FALSE;
		return $path;
	}

}
