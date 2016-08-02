<?php namespace Airprop\SurveyCore\Services;

use Config;
use Job;
use Validator;

class JobBase
{
  protected $jobid;

  protected $reportid;

  /** @var Job */
  protected $job;

  protected $data;

  protected $rules = [
    'command'  => 'required',
    'jobid'    => 'required',
    'reportid' => 'required',
  ];

  protected $messages = [
    'required' => ':attributeは必須です',
  ];

  public function __construct(array $data)
  {
    $this->data = $data;
    $this->validator = Validator::make($data, $this->rules, $this->messages);
  }

  public function jobid()
  {
    return $this->jobid;
  }

  public function reportid()
  {
    return $this->reportid;
  }

  /**
   * @return Job
   */
  public function job()
  {
    return $this->job;
  }
}