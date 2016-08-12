<?php namespace Airprop\SurveyCore\Controllers\Report;

use Job;
use Report\LayoutViewModel;
use Report\SurveyViewModel;
use Route;
use View;

class SurveyController extends BaseController
{
  protected $layout = 'layout';

  public static function routing()
  {
    Route::get('report/{manaba_jobid}/survey', [
    'as'   => 'survey',
    'uses' => __CLASS__.'@index',
    ]);

    Route::get('report/{manaba_jobid}/summary/overall', [
      'as'   => 'survey-core::summary.overall.show',
      'uses' => __CLASS__.'@show',
    ]);

  }

  /**
   * @return SurveyViewModel
   */
  public function getViewModel()
  {
    return app('Report\SurveyViewModel');
  }

  public function index(Job $job)
  {
    $layoutViewModel = new LayoutViewModel;
    $layoutViewModel->setStatus($job->status);
    $layoutViewModel->setModel($job);
    View::share('layoutViewModel', $layoutViewModel);

    $viewModel = $this->getViewModel();
    $viewModel->setJob($job);
    $topLevelViewModel = app('TopLevelViewModel');
    $topLevelViewModel->setJob($job);

    $this->setView('survey-core::report.survey.index', [
      'job'       => $job,
      'viewModel' => $viewModel,
      'topLevelViewModel' => $topLevelViewModel,
    ]);
  }


  public function show(Job $job)
  {
    $this->layout = View::make('print');

    /** @var \Survey\SummaryViewModel $viewModel */
    $viewModel = app('Survey\SummaryViewModel');
    $viewModel->setModel($job->surveys()->first());

    $this->setView('survey.summary', [
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