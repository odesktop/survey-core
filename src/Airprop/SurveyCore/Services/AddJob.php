<?php namespace Airprop\SurveyCore\Services;

use Airprop\SurveyCore\Tasks\TaskRegistration;
use Artisan;
use DB;
use Exception;
use File;
use Job;
use Session;
use TaskManager;

class AddJob extends JobBase
{
  public function run()
  {
    $data = $this->data;

    if ($this->validator->fails())
    {
      throw new Exception($this->validator->messages()->first());
    }

    // 同一のjobidは不許可
    if (Job::where('manaba_jobid', $data['jobid'])->exists())
    {
      throw new Exception('The same jobid already exists');
    }

    // load-json中は新たなジョブを登録させない
    $jobLoadJsonExists =Job::query()
      ->where('status', '<>', 'error')
      ->where('current_step', 'load-json')
      ->exists();
    if ($jobLoadJsonExists)
    {
      throw new Exception('Now loading. Please wait and retry');
    }

    // storage/jobsに保存
    $logFilePath = storage_path(sprintf('jobs/%s.json', $data['jobid']));
    file_put_contents($logFilePath, json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));

    $this->jobid = $data['jobid'];
    $this->reportid = $data['reportid'];

    // jobを作成
    $job = Job::create([
      'manaba_jobid' => $data['jobid'],
      'status'       => 'processing',
      'current_step' => 'load-json',
    ]);
    $this->job = $job;

    $taskName = array_get($data, 'task', 'load-json');
    if ($taskName == 'load-json')
    {
      // job開始前にDBをバックアップしてtruncate
      Artisan::call('db:backup', [
        '--truncate' => null,
      ]);

//      $firstTask = TaskManager::taskGetJson($data['reportid'], $data['jobid'], $urls);
      $firstTask = TaskRegistration::make($data['jobid']);
      TaskManager::taskRegisterTask($data['jobid'], $taskName);
    }
    else
    {
      // 集計以降の処理から開始する場合は
      // 開始する処理以降のタスクをすべて作成し、開始する処理のキューを登録する
      $firstTask = TaskManager::taskRegisterTask($data['jobid'], $taskName);
    }

    // 現在走っているタスクがなければ実行する
    $otherTaskExists = DB::table('tasks')
      ->where('id', '<>', $firstTask->id)
      ->whereNotNull('pushed_at')
      ->whereNull('finished_at')
      ->exists();
    if (!$otherTaskExists)
    {
      call_user_func($firstTask->callback, $firstTask);
    }

    return [
      'message'  => '処理を開始しました',
      'jobid'    => $this->jobid(),
      'reportid' => $this->reportid(),
      'status'   => 'success',
    ];
  }
}