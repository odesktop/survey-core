<?php namespace Airprop\SurveyCore\Commands;

use Illuminate\Console\Command;
use Loaders\SurveyLoader;
use Symfony\Component\Console\Input\InputArgument;

class LoadJson extends Command {

  protected $name = 'json:load';

  protected $description = 'Load json.';

  public function fire()
  {
    $url = $this->argument('url');
    $json = file_get_contents($url);
    $loader = new SurveyLoader($json);
    $loader->clear();
    $loader->store();
  }

  protected function getArguments()
  {
    return [
      ['url', InputArgument::REQUIRED, 'json url.'],
    ];
  }

}
