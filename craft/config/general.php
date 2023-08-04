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
    'defaultImageQuality' => 85,
    'generateTransformsBeforePageLoad' => true,
    'maxUploadFileSize' => 67108864
  ),

  /* Local environment */
  'efedorenko.ddev.site' => array(
    'devMode' => true,
    'siteUrl' => 'https://efedorenko.ddev.site',
    'environmentVariables' => array(
      'baseUrl'  => 'https://efedorenko.ddev.site',
    ),
    'testToEmailAddress' => 'hello@efedorenko.com',
  ),

  /* Live environment */

  '107.170.36.109' => array(
    'siteUrl' => 'https://107.170.36.109/',
    'environmentVariables' => array(
      'basePath' => '/var/www/html/',
      'baseUrl'  => 'https://107.170.36.109/',
    )
  ),

  'efedorenko.com' => array(
    'siteUrl' => 'https://efedorenko.com/',
    'environmentVariables' => array(
      'baseUrl'  => 'https://efedorenko.com/',
    )
  )
);
