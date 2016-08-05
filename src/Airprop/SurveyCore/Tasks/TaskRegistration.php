<?php namespace Airprop\SurveyCore\Tasks;

use Carbon\Carbon;
use Exception;
use Queue;
use SurveyManager;
use Task;

class TaskRegistration implements TaskInterface
{
  /**
   * タスクを作成
   * @param $jobid
   * @param array $options
   * @return Task
   */
  public static function make($jobid, $options = [])
  {
    $entries = glob(storage_path('json/'.$jobid.'/*.json'));
    $total = count($entries);

    $task = Task::create([
      'manaba_jobid' => $jobid,
      'name'         => 'load-json',
      'label'        => 'manabaからJSONをロード',
      'total'        => $total,
      'callback'     => __CLASS__.'::push',
      'callback_params' => serialize([
        'entries' => $entries,
      ]),
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
    $entries = array_get($params, 'entries', []);

    Queue::push('TaskRunner', [
      'taskid'   => $task->id,
      'callback' => __CLASS__.'::run',
      'params'   => [
        'taskid'  => $task->id,
        'entries' => $entries,
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
   */
  public static function run($queue_job, $params)
  {
    $taskid   = array_get($params, 'taskid');
    $entries  = array_get($params, 'entries');

    $task = Task::find($taskid);

    $manager = new SurveyManager;
    foreach ($entries as $i => $jsonPath)
    {
      $task->update(['progress' => $i+1]);
      queue_log($queue_job, 'JSON', '%s %d/%d', [$jsonPath, $i, count($entries)]);
      $manager->setReportId($task->reportid);
      $manager->register(file_get_contents($jsonPath));
      $manager->store();
      $task->update(['completes' => $i+1]);
    }
    return true;
  }
}