<?php namespace Airprop\SurveyCore\Tasks;

use Carbon\Carbon;
use File;
use Queue;
use SummaryCalculator;
use Task;

/**
 * 全体
 * Class TaskOverall
 * @package Tasks
 */
class TaskSummaryOverall implements TaskInterface
{
  /**
   * @param $jobid
   * @return Task
   */
  public static function task($jobid, $options = [])
  {
    $task = Task::create([
      'manaba_jobid' => $jobid,
      'name'         => 'summary-overall',
      'label'        => '全体の集計',
      'callback'     => __CLASS__.'::push',
      'callback_params' => serialize([
        'jobid'   => $jobid,
      ]),
      'total'        => 1
    ]);

    return $task;
  }

  public static function push(Task $task)
  {
    task_log($task, 'PUSH', '%s', [__CLASS__]);

    $params  = unserialize($task->callback_params);

    $jobid   = array_get($params, 'jobid');

    // PDFディレクトリをクリア
    File::cleanDirectory(public_path('pdf/'.$jobid));

    Queue::push('TaskRunner', [
      'taskid'   => $task->id,
      'callback' => __CLASS__.'::run',
      'params'   => [
        'jobid'   => $jobid,
      ],
    ]);

    $task->update([
      'pushed_at' => Carbon::now(),
    ]);
  }

  /**
   * 集計
   * @param $queue_job
   * @param $params
   */
  public static function run($queue_job, $params)
  {
    $jobid   = array_get($params, 'jobid');

    $calc = new SummaryCalculator;
    $calc->setJobid($jobid);

    queue_log($queue_job, 'JOB', 'jobid=%s', [$params['jobid']]);
    $calc->run();
  }

}