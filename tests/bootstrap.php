<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// https://github.com/sebastianbergmann/phpunit/issues/314
if (!defined('CORKED_TEST_ROOT')) {
    define('CORKED_TEST_ROOT', __DIR__);
}
if (!defined('CORKED_TEST_RESOURCES_ROOT')) {
    define('CORKED_TEST_RESOURCES_ROOT', CORKED_TEST_ROOT . DIRECTORY_SEPARATOR . 'resources');
}
