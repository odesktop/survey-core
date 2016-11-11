<?php namespace Airprop\SurveyCore\Tasks;

use Carbon\Carbon;
use Course;
use Exception;
use File;
use Job;
use Log;
use Queue;
use Task;
use ZipArchive;

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
    Job::firstOrCreate([
      'manaba_jobid' => $jobid,
    ]);

    $task = Task::create([
      'manaba_jobid' => $jobid,
      'name'         => 'zip-course',
      'label'        => '科目別の分析ZIPファイル',
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

      $outputFilePath = $job->courseZipPath();
      $zip = static::zip($outputFilePath);
      queue_log($queue_job, 'ZIP', '%sを作成', [$outputFilePath]);

      // メタ情報を追加
      $jobJsonPath = storage_path('jobs/'.$jobid.'.json');
      queue_log($queue_job, 'METADATA', '%sをロード', [$jobJsonPath]);
      $json = json_decode(file_get_contents($jobJsonPath), true);
      $metadata = array_get($json, 'metadata');
      $url = array_get($metadata, 'url');
      queue_log($queue_job, 'METADATA', '%sをロード', [$url]);
      $metadataJson = json_decode(file_get_contents($url), true);
      if (array_get($metadataJson, 'status') != 'ok')
      {
        throw new Exception('metadata status is not ok.');
      }
      $filename = array_get($metadataJson, 'filename');
      $content  = array_get($metadataJson, 'filecontent');
      $zip->addFromString($filename, $content);

      $entries = Course::where('manaba_jobid', $jobid)->get();
      foreach ($entries as $course)
      {
        if (!File::exists($course->pdfFilePath()))
        {
          queue_log($queue_job, 'ERROR', '%sは生成されていません', [$course->pdfFilePath()]);
          continue;
        }

        $dirname  = sprintf('%s-%s-%s', $course->coursecode, $course->year, $course->term);
        $filename = $course->nameloc.'.pdf';
        queue_log($queue_job, 'ZIP', '%sを追加', [$filename]);
        $filename = mb_convert_encoding($filename, 'cp932', 'UTF-8');
        $zip->addFile($course->pdfFilePath(), $dirname.'/'.$filename);
      }
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