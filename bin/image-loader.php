#!/bin/env php
<?php

require __DIR__.'/../vendor/autoload.php';

define('PROJECT_ROOT', __DIR__.'/..');

$application = new \ImageLoader\ImageLoaderApplication();
$application->run();

