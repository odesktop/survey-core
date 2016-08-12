<?php namespace Airprop\SurveyCore\Tasks;

use Carbon\Carbon;
use Exception;
use Job;
use Organization;
use Queue;
use SummaryCalculator;
use Task;

/**
 * 科目区分別
 * Class Organization
 * @package Tasks
 */
class TaskSummaryOrganization implements TaskInterface
{
  /**
   * @param $jobid
   * @return Task
   * @throws Exception
   */
  public static function make($jobid, $options = [])
  {
    Job::firstOrCreate([
      'manaba_jobid' => $jobid,
    ]);

    $entries = Organization::query()
      ->where('manaba_jobid', $jobid)
      ->lists('code');
    $total   = count($entries);

    if ($total == 0)
    {
      throw new Exception('科目区分別 レコードが登録されていません');
    }

    $task = Task::create([
      'manaba_jobid' => $jobid,
      'name'         => 'all-summary-org',
      'label'        => '科目区分別の集計',
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
   * @param $task
   */
  public static function push(Task $task)
  {
    $params  = unserialize($task->callback_params);
    $jobid   = array_get($params, 'jobid');
    $entries = array_get($params, 'entries');

    foreach ($entries as $code)
    {
      Queue::push('TaskRunner', [
        'taskid'  => $task->id,
        'callback' => __CLASS__.'::run',
        'params'  => [
          'jobid'   => $jobid,
          'orgcode' => $code,
        ],
      ]);
    }

    $task->update([
      'pushed_at' => Carbon::now(),
    ]);
  }

  /**
   * @param $queue_job
   * @param $params
   */
  public static function run($queue_job, $params)
  {
    $jobid   = array_get($params, 'jobid');
    $orgcode = array_get($params, 'orgcode');

    /** @var SummaryCalculator $calc */
    $calc = app('SummaryCalculator');
    $calc->setJobid($jobid);
    $calc->setOrgcode($orgcode);

    queue_log($queue_job, 'JOB', 'jobid=%s orgcode=%s', [$jobid, $orgcode]);
    $calc->run();
  }
}