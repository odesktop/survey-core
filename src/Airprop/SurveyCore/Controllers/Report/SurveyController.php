<?php namespace Airprop\SurveyCore\Controllers\Report;

use Report\LayoutViewModel;
use Report\SurveyViewModel;
use Route;
use View;

class SurveyController extends BaseController
{
  protected $layout = 'layout';

  public static function routing()
  {
    Route::get('job/{job}/survey', [
      'as'   => 'survey',
      'uses' => __CLASS__.'@index',
    ]);
  }

  /**
   * @return SurveyViewModel
   */
  public function getViewModel()
  {
    return app('Report\SurveyViewModel');
  }

  public function index($job)
  {
    $layoutViewModel = new LayoutViewModel;
    $layoutViewModel->setStatus($job->status);
    $layoutViewModel->setModel($job);
    View::share('layoutViewModel', $layoutViewModel);

    $viewModel = $this->getViewModel();
    $viewModel->setJob($job);
    $topLevelViewModel = app('TopLevelViewModel');
    $topLevelViewModel->setJob($job);

    $this->setView('survey.index', [
      'job'       => $job,
      'viewModel' => $viewModel,
      'topLevelViewModel' => $topLevelViewModel,
    ]);
  }


  public function summary(SurveyOrganization $organization)
  {
    if (Config::get('app.debug'))
    {
      Debugbar::disable();
    }

    $this->layout = View::make('print');

    /** @var Survey\CrossSummaryViewModel $crossViewModel */
    $crossViewModel = app('Survey\CrossSummaryViewModel');
    $crossViewModel->setOrganization($organization);

    /** @var Survey\SummaryViewModel $viewModel */
    $viewModel = app('Survey\SummaryViewModel');
    $viewModel->setOrganization($organization);

    $this->setView('survey.summary', [
      'crossViewModel' => $crossViewModel,
      'viewModel' => $viewModel,
    ]);
  }

  public function makeSummary(SurveyOrganization $organization)
  {
    if (Config::get('app.debug'))
    {
      Debugbar::disable();
    }
    Artisan::call('summary:org', [
      'jobid'   => $organization->survey->manaba_jobid,
      'orgcode' => $organization->code,
    ]);

    return Redirect::back();
  }
}