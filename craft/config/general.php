<?php

/**
 * General Configuration
 *
 * All of your system's general configuration settings go in here.
 * You can see a list of the default settings in craft/app/etc/config/defaults/general.php
 */

return array(
  '*' => array(
    'omitScriptNameInUrls' => true,
    'appId' => 'efedorenko',
    'defaultImageQuality' => 85
  ),

  /* Local environment */
  'efedorenko.craft' => array(
    'devMode' => true,
    'siteUrl' => 'http://efedorenko.craft:8888/',
    'environmentVariables' => array(
      'baseUrl'  => 'http://efedorenko.craft:8888/',
    ),
    'testToEmailAddress' => 'fedorenko@gmail.com',
  ),

  /* Live environment */

  '107.170.36.109' => array(
    'siteUrl' => 'http://107.170.36.109/',
    'environmentVariables' => array(
      'basePath' => '/var/www/html/',
      'baseUrl'  => 'http://107.170.36.109/',
    )
  ),

  'efedorenko.com' => array(
    'siteUrl' => 'http://efedorenko.com/',
    'environmentVariables' => array(
      'baseUrl'  => 'http://efedorenko.com/',
    )
  )
);
