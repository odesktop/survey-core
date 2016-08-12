<?php namespace Airprop\SurveyCore\Tasks;

use Carbon\Carbon;
use Config;
use Exception;
use File;
use Job;
use Log;
use Organization;
use Queue;
use Survey;
use Task;

/**
 * Class TaskPdfOverall
 * @package Airprop\SurveyCore\Tasks
 * @todo jissenではa1,a4に紐付いていたが今回はないので修正が必要
 */
class TaskPdfOverall implements TaskInterface
{
  /**
   * タスクを作成
   * @param $jobid
   * @throws Exception
   * @return Task
   */
  public static function make($jobid, $options = [])
  {
    Job::firstOrCreate([
      'manaba_jobid' => $jobid,
    ]);

    $task = Task::create([
      'manaba_jobid' => $jobid,
      'name'         => 'all-pdf-total',
      'label'        => '全体のPDF',
      'callback'     => __CLASS__.'::push',
      'callback_params' => serialize([
        'jobid'   => $jobid,
      ]),
      'total'        => 1,
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
   * @todo pdfの生成先ディレクトリ決定、PDF用のViewを更新
   */
  public static function run($queue_job, $params)
  {
    $jobid   = array_get($params, 'jobid');

    try {
      $survey = Survey::query()
        ->where('manaba_jobid', $jobid)
        ->first();
      if (!$survey)
        throw new Exception('存在しないアンケート surveys.manaba_jobid = '.$jobid);

      $directory = $organization->pdfDir();
      if (!File::exists($directory))
        File::makeDirectory($directory, 02775, true, true);

      $url      = route('summary.org', [$organization->id]);
      $filename = $directory.'/'.$orgcode.'.pdf';
      $command = wkhtmltopdf($url, $filename);
      queue_log($queue_job, 'GENPDF', '%s', [$command]);
      system($command);
    } catch (Exception $e) {
      queue_log($queue_job, 'ERROR', $e->getMessage(), '');
      Log::error($e->getTraceAsString(), ['context' => $e->getMessage()]);
    }
  }
}