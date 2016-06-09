<?php

$databases['default']['default'] = [
  'database' => 'elife_2_0',
  'username' => 'elife_2_0',
  'password' => 'elife_2_0',
  'prefix' => '',
  'host' => 'localhost',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
];

$databases['elife_1_0']['default'] = [
  'database' => 'elife_1_0',
  'username' => 'elife_1_0',
  'password' => 'elife_1_0',
  'prefix' => '',
  'host' => 'localhost',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
];

$settings['trusted_host_patterns'] = [
  '^elife\-2\.0\-website\.dev$',
];