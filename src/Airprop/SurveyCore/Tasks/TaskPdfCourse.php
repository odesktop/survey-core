<?php namespace Airprop\SurveyCore\Tasks;

use Carbon\Carbon;
use Course;
use Exception;
use File;
use Job;
use Log;
use Queue;
use Task;

/**
 * 科目別PDF
 * Class TaskPdfCourse
 * @package Airprop\SurveyCore\Tasks
 */
class TaskPdfCourse implements TaskInterface
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

    $entries = Course::query()
      ->where('manaba_jobid', $jobid)
      ->lists('oid');

    $task = Task::create([
      'manaba_jobid' => $jobid,
      'name'         => 'all-pdf-course',
      'label'        => '科目別のPDF',
      'callback'     => __CLASS__.'::push',
      'callback_params' => serialize([
        'jobid'   => $jobid,
        'entries' => $entries,
      ]),
      'total'        => count($entries),
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
        'taskid' => $task->id,
        'callback' => __CLASS__.'::run',
        'params' => [
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
   * 実行
   * @param $queue_job
   * @param $params
   */
  public static function run($queue_job, $params)
  {
    try {
      $jobid = $params['jobid'];
      $oid   = $params['course'];

      /** @var Course $course */
      $course = Course::query()
        ->where('manaba_jobid', $jobid)
        ->where('oid', $oid)
        ->first();

      if (!$course)
      {
        throw new Exception('存在しないコース manaba_jobid = '.$jobid.' courses.oid = '.$oid);
      }

      $directory = $course->pdfDir();
      if (!File::exists($directory))
        File::makeDirectory($directory, 02775, true, true);

      $url      = route('course.summary', [$course->manaba_jobid, $course->id]);
      $filename = $directory.'/'.$oid.'.pdf';
      $command = static::savePdfCommand($url, $filename);
      queue_log($queue_job, 'GENPDF', '%s', [$command]);
      system($command);
    } catch (Exception $e) {
      queue_log($queue_job, 'ERROR', $e->getMessage(), '');
      Log::error($e->getTraceAsString(), ['context' => $e->getMessage()]);
    }
  }

  protected static function savePdfCommand($url, $filename)
  {
    $command = Config::get('survey-core::survey-core.wkhtmltopdf');
    return sprintf('%s %s %s > /dev/null 2>&1',
      $command,
      $url,
      $filename
    );
  }
}