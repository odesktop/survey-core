<?php
if (!function_exists('wkhtmltopdf')) {

  function wkhtmltopdf($url, $filename, $style = null)
  {
    $config = Config::get('survey-core::wkhtmltopdf');

    if (is_null($style))
      $style = array_get($config, 'default');
    $command = array_get($config, 'commands.'.$style);
    return sprintf('%s %s %s > /dev/null 2>&1',
      $command,
      $url,
      $filename
    );
  }

}