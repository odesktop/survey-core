<?php namespace Airprop\SurveyCore\Tasks;

use Carbon\Carbon;
use Course;
use Exception;
use File;
use Job;
use Log;
use mikehaertl\pdftk\Pdf;
use Queue;
use Task;

class TaskPdfCourseList implements TaskInterface
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
      'name'         => 'pdf-course-list',
      'label'        => '科目別の分析一覧',
      'callback'     => __CLASS__.'::push',
      'callback_params' => serialize([
        'jobid'   => $jobid,
      ]),
      'total'     => 1,
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
      $jobid   = $params['jobid'];

      /** @var Job $job */
      $job = Job::query()
        ->where('manaba_jobid', $jobid)
        ->first();

      $directory = $job->pdfDir();
      if (!File::exists($directory))
        File::makeDirectory($directory, 02775, true, true);

      $pdf = new Pdf();

      $entries = Course::query()
        ->where('manaba_jobid', $jobid)
        ->orderBy('teacher', 'asc')
        ->orderBy('nameloc', 'asc')
        ->orderBy('oid', 'asc')
        ->get();

      $filePath =  $job->courseListPdfPath();
      foreach ($entries as $i => $course)
      {
        /** @var Course $course */
        if (!File::exists($course->pdfFilePath()))
        {
          continue;
        }
        $pdf->addFile($course->pdfFilePath());
      }
      queue_log($queue_job, 'GENPDF', '%s', [$filePath]);
      $pdf->saveAs($filePath);
    } catch (Exception $e) {
      queue_log($queue_job, 'ERROR', $e->getMessage(), '');
      Log::error($e->getTraceAsString(), ['context' => $e->getMessage()]);
    }
  }
}