<?php

define('ROOT_PATH', __DIR__);
define('TEST_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'test');

require(__DIR__ . '/vendor/autoload.php');

class TestController extends \Maverick\Controller\StandardController
{
    public function doAction(\Maverick\Http\StandardRequest $request) { }
}
