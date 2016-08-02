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

    $this->jobid = $data['jobid'];
    $this->reportid = $data['reportid'];

    Session::put('jobid', $data['jobid']);

    // jobを作成
    $job = Job::create([
      'manaba_jobid' => $data['jobid'],
      'status'       => 'processing',
      'current_step' => 'load-json',
    ]);
    $this->job = $job;

    // PDFメタデータを保存
    $job->saveMetaFile($data['metadata']['url']);

    $urls = [];
    $taskName = array_get($data, 'task', 'load-json');
    if ($taskName == 'load-json')
    {
      // JSON取得から開始する場合は
      // JSON取得タスクを作成し、キューに登録する
      // JSONファイルはstorage/json/{jobid}に保存する
      $jsonDir = storage_path('json/'.$data['jobid']);
      File::cleanDirectory($jsonDir);
      File::makeDirectory($jsonDir, 2775, true, true);
      foreach ($data['data_url'] as $meta)
      {
        file_put_contents($jsonDir.'/'.basename($meta['url']), file_get_contents($meta['url']));
      }

//      $firstTask = TaskManager::taskGetJson($data['reportid'], $data['jobid'], $urls);
      $firstTask = TaskRegistration::task($data['jobid']);
      TaskManager::taskRegisterTask($data['reportid'], $data['jobid'], $taskName);
    }
    else
    {
      // 集計以降の処理から開始する場合は
      // 開始する処理以降のタスクをすべて作成し、開始する処理のキューを登録する
      $firstTask = TaskManager::taskRegisterTask($data['reportid'], $data['jobid'], $taskName);
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