<?php
// DOCUMENT_ROOT can be set as {dirname(__FILE__)} or {dirname(__FILE__) . DIRECTORY_SEPARATOR . 'public'}
include_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php');
include_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'environment.php');
include_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . 'firefly' . DIRECTORY_SEPARATOR . 'dispatcher.php');

$dispatcher = new Dispatcher();
$dispatcher->dispatch();
?>