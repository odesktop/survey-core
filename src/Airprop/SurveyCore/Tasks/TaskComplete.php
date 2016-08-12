<?php namespace Airprop\SurveyCore\Tasks;

use Carbon\Carbon;
use Job;
use Queue;
use Task;

class TaskComplete implements TaskInterface
{

  /**
   * タスクを作成
   * @param $jobid
   * @param array $options
   * @return Task
   */
  public static function make($jobid, $options = [])
  {
    Job::firstOrCreate([
      'manaba_jobid' => $jobid,
    ]);

    $task = Task::create([
      'manaba_jobid' => $jobid,
      'name'         => 'complete',
      'label'        => 'すべての処理が完了',
      'callback'     => __CLASS__.'::push',
      'callback_params' => serialize([
        'jobid'   => $jobid,
      ]),
      'total'        => 1
    ]);

    return $task;
  }

  /**
   * キューに追加
   * @param Task $task
   */
  public static function push(Task $task)
  {
    $params  = unserialize($task->callback_params);

    $jobid   = array_get($params, 'jobid');

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
   * 実行
   * @param $queue_job
   * @param $params
   * @return bool
   */
  public static function run($queue_job, $params)
  {
    return true;
  }
}