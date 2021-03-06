<?php

include __DIR__ . '/../public/init.php';

$di = \Phalcon\Di::getDefault();

$service = $di->get('importService');
$service->import();
$service->importCameraPicture();
$service->importGMCameraPicture();
$service->importOttawaSnow();
$service->restartFtpServer();

$service = $di->get('dataService');
$service->fakeInverterData();
#$service->fakeEnvkitData();
