<?php

namespace ABetter\Component;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class BladeServiceProvider extends ServiceProvider {

    public function boot() {

		// AComponent (extends laravel component directive)
		Blade::directive('acomponent', function($expression){
			list($path,$vars,$class,$end) = self::parseExpression($expression);
			if (!$view = self::componentExists(self::parseRelativePath($path))) { // Test relative
				if (!$view = self::componentExists($path)) { // Test original
					$view = 'components.missing.missing'; // Fallback
				}
			}
			$end = ($end) ? "echo \$__env->renderComponent();" : "";
			return "<?php \$__env->startComponent('{$view}', array_merge({$vars},['componentName' => '{$path}'])); {$end} ?>";
		});

		Blade::directive('endacomponent', function(){
			return "<?php echo \$__env->renderComponent(); ?>";
		});

    }

    public function register() {
        //
    }

	// ---

	public static function parseExpression($parse) {
		if ($end = preg_match('/,\s?true/i',$parse)) {
			$parse = preg_replace('/,\s?true/i',"",$parse);
		}
		$strings = strtok($parse,'[');
		$data = str_replace($strings,"",$parse);
		$data = preg_replace('/(\'|") ?(=&gt;|=) ?(\'|")/',"$1 => $3",$data);
		$params = explode(',',trim($strings,' ,'));
		$name = trim(preg_replace('/(\'|")/',"",$params[0]));
		$class = "";
		if (!empty($params[1])) {
			$class = $name;
			$name = trim(preg_replace('/(\'|")/',"",$params[1]));
		}
		$exp = [];
		$exp[0] = $name;
		$exp[1] = ($data) ? $data : '[]';
		$exp[2] = ($class) ? $class : "";
		$exp[3] = ($end) ? TRUE : FALSE;
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
