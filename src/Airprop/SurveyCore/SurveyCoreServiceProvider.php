<?php namespace Airprop\SurveyCore;

use Airprop\SurveyCore\Commands\ClearJob;
use Airprop\SurveyCore\Commands\QueryJob;
use Airprop\SurveyCore\Commands\RequestJob;
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
    $this->app->bind('airprop::command.job.request', function ($app) {
      return new RequestJob();
    });
    $this->app->bind('airprop::command.job.query', function ($app) {
      return new QueryJob();
    });
    $this->app->bind('airprop::command.job.clear', function ($app) {
      return new ClearJob();
    });
    $this->commands([
      'airprop::command.job.request',
      'airprop::command.job.query',
      'airprop::command.job.clear',
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
