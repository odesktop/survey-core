<?php namespace Airprop\SurveyCore\Controllers\Report;

use Airprop\SurveyCore\Tasks\TaskInterface;
use Job;
use Redirect;
use Route;
use Task;

class TaskController extends BaseController
{
  public static function routing()
  {
    Route::get('{manaba_jobid}/task/summary/overall', [
      'as'   => 'survey-core::task.overall.summary',
      'uses' => __CLASS__.'@summary',
    ]);
  }

  /**
   * @param $job
   * @param $class
   * @return Task
   */
  public function getTask($job, $class)
  {
    /** @var TaskInterface $instance */
    $instance = app($class);
    return $instance::make($job->manaba_jobid);
  }

  public function summary(Job $job)
  {
    $task = $this->getTask($job, 'TaskSummaryOverall');
    $task->call();

    return Redirect::back()->with('info', 'タスクを作成しました');
  }
}