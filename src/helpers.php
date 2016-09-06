<?php
if (!function_exists('wkhtmltopdf')) {

  function wkhtmltopdf($url, $filename)
  {
    $config = Config::get('survey-core::wkhtmltopdf');

    $default = array_get($config, 'default');
    $command = array_get($config, 'commands.'.$default);
    return sprintf('%s %s %s > /dev/null 2>&1',
      $command,
      $url,
      $filename
    );
  }

}