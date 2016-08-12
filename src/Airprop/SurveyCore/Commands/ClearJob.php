<?php namespace Airprop\SurveyCore\Commands;

use Illuminate\Console\Command;
use Job;
use Symfony\Component\Console\Input\InputArgument;
use Task;

class ClearJob extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'job:clear';

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
		$jobid    = $this->argument('jobid');

    Job::where('manaba_jobid', $jobid)->delete();
    $this->info('jobsテーブルから'.$jobid.'を削除しました');
    Task::where('manaba_jobid', $jobid)->delete();
    $this->info('tasksテーブルから'.$jobid.'を削除しました');
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
		return array();
	}

}
