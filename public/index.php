<?php

use Symfony\Component\ErrorHandler\Debug;

require_once dirname(__DIR__).'/config/bootstrap.php';

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();
}

echo 'Hello World';
