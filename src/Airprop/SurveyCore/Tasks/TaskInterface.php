<?php namespace Airprop\SurveyCore\Tasks;

use Exception;
use Task;

/***
 * Interface TaskInterface
 * @package Tasks
 * @todo インスタンス化できるようにしたい
 */
interface TaskInterface
{
  /**
   * タスクを作成
   * @param $jobid
   * @param array $options
   * @return Task
   */
  public static function task($jobid, $options = []);

  /**
   * キューに追加
   * @param Task $task
   */
  public static function push(Task $task);

  /**
   * 実行
   * @param $queue_job
   * @param $params
   */
  public static function run($queue_job, $params);
}