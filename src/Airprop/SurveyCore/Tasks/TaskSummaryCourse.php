<?php namespace Airprop\SurveyCore\Tasks;

use Carbon\Carbon;
use Course;
use Exception;
use Job;
use Queue;
use SummaryCalculator;
use Task;

/**
 * 科目別
 * Class TaskCourse
 * @package Tasks
 */
class TaskSummaryCourse implements TaskInterface
{
  /**
   * タスクを作成
   * @param $jobid
   * @return Task
   * @throws Exception
   */
  public static function make($jobid, $options = [])
  {
    Job::firstOrCreate([
      'manaba_jobid' => $jobid,
    ]);

    $entries = Course::query()
      ->where('manaba_jobid', $jobid)
      ->lists('oid');
    $total   = count($entries);

    if ($total == 0)
    {
      throw new Exception('科目別 すべての集計 レコードが登録されていません');
    }

    $task = Task::create([
      'manaba_jobid' => $jobid,
      'name'         => 'all-summary-course',
      'label'        => '科目別の集計',
      'callback'     => __CLASS__.'::push',
      'callback_params' => serialize([
        'jobid'   => $jobid,
        'entries' => $entries,
      ]),
      'total'        => $total,
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
    $entries = array_get($params, 'entries');

    foreach ($entries as $oid)
    {
      Queue::push('TaskRunner', [
        'taskid'   => $task->id,
        'callback' => __CLASS__.'::run',
        'params'   => [
          'jobid'  => $jobid,
          'course' => $oid,
        ],
      ]);
    }

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
    $jobid  = array_get($params, 'jobid');
    $course = array_get($params, 'course');

    /** @var SummaryCalculator $calc */
    $calc = app('SummaryCalculator');
    $calc->setJobid($jobid);
    $calc->setCourse($course);

    $calc->run();
  }
}