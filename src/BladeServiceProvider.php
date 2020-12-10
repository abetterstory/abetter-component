<?php

namespace ABetter\Component;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class BladeServiceProvider extends ServiceProvider {

    public function boot() {

		Blade::directive('abettercomponent', function(){
			return "<?php echo 'ABETTER COMPONENT!'; ?>";
		});

    }

    public function register() {
        //
    }

}
