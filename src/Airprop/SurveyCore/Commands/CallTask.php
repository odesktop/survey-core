<?php namespace Airprop\SurveyCore\Commands;

use Airprop\SurveyCore\Tasks\TaskInterface;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CallTask extends Command {

	protected $name = 'task:call';

	protected $description = 'Call task.';

	public function fire()
	{
    $class = $this->argument('class');
		$jobid = $this->argument('jobid');

    /** @var TaskInterface $class */
    $class = app($class);
    if (is_null($class))
    {
      $this->error('有効なクラス名を指定してください');
      return;
    }
    $task = $class::make($jobid);
    $task->call();
	}

  protected function getArguments()
	{
		return [
			['class', InputArgument::REQUIRED, 'task class name.'],
      ['jobid', InputArgument::REQUIRED, 'manaba jobid.'],
    ];
	}

}
