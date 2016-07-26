<?php namespace Airprop\SurveyCore\Commands;

use Config;
use Illuminate\Console\Command;
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
    $endpoint = Config::get('app.url').'/api';

    $jsonFilePath = base_path('jobs/'.$json.'.json');
    if (!file_exists($jsonFilePath))
    {
      $this->error($jsonFilePath.' not exists.');
      return -1;
    }
    $header = [
      'Content-Type: application/json',
    ];

    $context = stream_context_create([
      'http' => [
        'method'  => 'POST',
        'header'  => implode(PHP_EOL, $header),
        'content' => file_get_contents($jsonFilePath),
        'ignore_errors' => true,
      ],
    ]);
    $response = file_get_contents($endpoint, false, $context);
    $this->info(json_encode(json_decode($response), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
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
