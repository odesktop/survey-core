<?php namespace Airprop\SurveyCore\Commands;

use Config;
use Illuminate\Console\Command;
use Log;
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
		$jobid = $this->argument('jobid');

		$data = [
			'jobid'    => $jobid,
			'command'  => 'queryjob',
		];

    $queryjob = new \Airprop\SurveyCore\Services\QueryJob($data);
    try {
      $response = $queryjob->run();
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
