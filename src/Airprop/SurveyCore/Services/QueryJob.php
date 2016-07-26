<?php namespace Airprop\SurveyCore\Services;

use Config;
use Exception;
use Job;
use Task;

/**
 * 進捗情報リクエスト
 *
 * Class QueryJob
 * @package Airprop\SurveyCore\Services
 */
class QueryJob extends JobBase
{
  protected $progress;

  protected $total;

  public function run()
  {
    $data = $this->data;

    if ($this->validator->fails())
    {
      throw new Exception($this->validator->messages()->first());
    }

    $this->jobid    = $data['jobid'];
    $this->reportid = $data['reportid'];

    $job = Job::where('manaba_jobid', $data['jobid'])->first();
    $this->job = $job;

    if (!$job)
    {
      throw new Exception($this->jobid.'は処理を開始していません');
    }

    if ($job->status == 'error')
    {
      throw new Exception($job->message);
    }

    $this->progress = Task::query()
      ->where('manaba_jobid', $data['jobid'])
      ->sum('current');

    $this->total = Task::query()
      ->where('manaba_jobid', $data['jobid'])
      ->sum('total');

    return [
      'message'      => $job->message,
      'jobid'        => $this->jobid(),
      'reportid'     => $this->reportid(),
      'status'       => $job->status,
      'current_step' => $job->current_step,
      'progress'     => $this->progress(),
      'total'        => $this->total(),
      'output'       => $this->output(),
      'timestamp'    => (string)\Carbon\Carbon::now()->timestamp,
    ];
  }

  public function progress()
  {
    return $this->progress;
  }

  public function total()
  {
    return $this->total;
  }

  protected function output()
  {
    $jobid = $this->jobid;
    $reportid = $this->reportid;

    return [
      [
        'type'     => 'all',
        'title_ja' => '学校別の分析',
        'title_en' => 'Analysis by school',
        'url'      => sprintf('%s/zip/%s/%s/all.zip', Config::get('app.url'), $reportid, $jobid),
      ],
      [
        'type'     => 'course',
        'title_ja' => '科目別の分析',
        'title_en' => 'Analysis by course',
        'url'      => sprintf('%s/zip/%s/%s/course.zip', Config::get('app.url'), $reportid, $jobid),
      ],
      [
        'type'     => 'courseperorg',
        'title_ja' => '科目別の分析(科目区分単位)',
        'title_en' => 'Analysis by course (course group unit)',
        'url'      => sprintf('%s/zip/%s/%s/courseperorg.zip', Config::get('app.url'), $reportid, $jobid),
      ],
      [
        'type'     => 'org',
        'title_ja' => '科目区分別の分析',
        'title_en' => 'Analysis by course group',
        'url'      => sprintf('%s/zip/%s/%s/org.zip', Config::get('app.url'), $reportid, $jobid),
      ],
    ];
  }

}