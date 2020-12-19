<?php

namespace ABetter\Component;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider {

    public function boot() {

		\View::addLocation(base_path().'/vendor/abetter/component/views');

		$this->loadViewComponentsAs('', [
			Component::class,
	        View::class,
	    ]);

	}

    public function register() {
		//
    }

}
