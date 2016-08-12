<?php namespace Airprop\SurveyCore\Tasks;

use Job;
use Task;

class TaskZipCourse implements TaskInterface
{

  /**
   * タスクを作成
   * @param $jobid
   * @param array $options
   * @return Task
   */
  public static function make($jobid, $options = [])
  {
    // TODO: Implement make() method.

    Job::firstOrCreate([
      'manaba_jobid' => $jobid,
    ]);
  }

  /**
   * キューに追加
   * @param Task $task
   */
  public static function push(Task $task)
  {
    // TODO: Implement push() method.
  }

  /**
   * 実行
   * @param $queue_job
   * @param $params
   */
  public static function run($queue_job, $params)
  {
    // TODO: Implement run() method.
  }
}