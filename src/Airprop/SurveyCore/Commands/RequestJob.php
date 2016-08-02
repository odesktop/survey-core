<?php namespace Airprop\SurveyCore\Commands;

use Airprop\SurveyCore\Services\AddJob;
use Config;
use Illuminate\Console\Command;
use Log;
use Symfony\Component\Console\Input\InputArgument;

/**
 * ジョブリクエストのテスト
 * /jobs/job1.json等を事前に用意しておく
 *
 * Class RequestJob
 * @package Airprop\SurveyCore
 */
class RequestJob extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
  protected $name = 'job:request';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{

    $json = $this->argument('json');

    $jsonFilePath = base_path('jobs/'.$json.'.json');
    if (!file_exists($jsonFilePath))
    {
      $this->error($jsonFilePath.' not exists.');
      return -1;
    }

    $data   = json_decode(file_get_contents($jsonFilePath), true);
    $addjob = new AddJob($data);
    try {
      $response = $addjob->run();
      $this->info(json_encode($response, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    } catch (\Exception $e) {
      Log::error($e->getTraceAsString(), ['message' => $e->getMessage()]);
      $this->error($e->getMessage());
    }
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
      array('json', InputArgument::REQUIRED, 'json file name in the jobs directory.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array();
	}

}
