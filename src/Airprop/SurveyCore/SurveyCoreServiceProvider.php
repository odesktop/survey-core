<?php namespace Airprop\SurveyCore;

use Illuminate\Support\ServiceProvider;

class SurveyCoreServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('airprop/survey-core');
    $this->app->bind('airprop::command.request.job', function ($app) {
      return new RequestJob();
    });
    $this->commands([
      'airprop::command.request.job',
    ]);
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
