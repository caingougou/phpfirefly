<?php
/**
 * dispatch.php
 * dispatch process: load environment -> boot -> dispatch request
 *
 */

include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'environment.php');

require_once(FIREFLY_LIB_DIR . DIRECTORY_SEPARATOR . 'dispatcher.php');


$dispatcher = new Dispatcher();
$dispatcher->dispatch();
?>