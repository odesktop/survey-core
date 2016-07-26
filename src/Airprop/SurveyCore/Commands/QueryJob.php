<?php namespace Airprop\SurveyCore\Commands;

use Config;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class QueryJob extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'job:query';

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
		$reportid = $this->argument('reportid');
		$jobid    = $this->argument('jobid');

		$json     = json_encode([
			'jobid'    => $jobid,
			'reportid' => $reportid,
			'command'  => 'queryjob',
		]);

		$header = [
			'Content-Type: application/json',
		];

		$context = stream_context_create([
			'http' => [
				'method'  => 'POST',
				'header'  => implode(PHP_EOL, $header),
				'content' => $json,
				'ignore_errors' => true,
			],
		]);
		$response = file_get_contents(Config::get('app.url').'/api', false, $context);
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
			array('reportid', InputArgument::REQUIRED, 'reportid.'),
			array('jobid', InputArgument::REQUIRED, 'manaba_jobid.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
		);
	}

}
