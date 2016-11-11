<?php namespace Airprop\SurveyCore\Tasks;

use Carbon\Carbon;
use Exception;
use File;
use Job;
use Log;
use Queue;
use Task;
use ZipArchive;

class TaskZipCourseList implements TaskInterface
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
      'name'         => 'zip-course-list',
      'label'        => '科目別の分析一覧ZIPファイル',
      'callback'     => __CLASS__.'::push',
      'callback_params' => serialize([
        'jobid' => $jobid,
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
      'taskid' => $task->id,
      'callback' => __CLASS__.'::run',
      'params' => [
        'jobid'  => $jobid,
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
    try {
      $jobid = $params['jobid'];

      /** @var Job $job */
      $job = Job::where('manaba_jobid', $jobid)->first();

      $outputFilePath = $job->courseListZipPath();
      $zip = static::zip($outputFilePath);
      queue_log($queue_job, 'ZIP', '%sを作成', [$outputFilePath]);

      $pdfFilePath = $job->courseListPdfPath();
      if (!File::exists($pdfFilePath))
      {
        queue_log($queue_job, 'ERROR', '%sは生成されていません', [$pdfFilePath]);
        return;
      }
      $zip->addFile($pdfFilePath, $job->courseListPdfFilename());
      $zip->close();
    } catch (Exception $e) {
      queue_log($queue_job, 'ERROR', $e->getMessage(), '');
      Log::error($e->getTraceAsString(), ['context' => $e->getMessage()]);
    }
  }

  public static function zip($outputFilePath)
  {
    if (!File::exists(dirname($outputFilePath)))
      File::makeDirectory(dirname($outputFilePath), 02775, true, true);

    if (File::exists($outputFilePath))
      File::delete($outputFilePath);

    $zip = new ZipArchive();
    $zip->open($outputFilePath, ZipArchive::CREATE);

    return $zip;
  }
}