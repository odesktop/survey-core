<?php namespace Airprop\SurveyCore\Controllers\Report;

use Report\LayoutViewModel;
use View;
use BaseController as SurveyBaseController;

class BaseController extends SurveyBaseController
{
  public function __construct()
  {
    View::share('layoutViewModel', new LayoutViewModel);
  }

}