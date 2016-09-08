<?php
return [

  'default' => 'portrait',

  'commands' => [
    'portrait' => 'wkhtmltopdf --username manaba --password survey -T 7 -L 10 -B 0 -R 4 --disable-smart-shrinking',
  ],

];