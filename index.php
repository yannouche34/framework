<?php
use Test\TestDisplayer;

//autoload configuration
spl_autoload_extensions('.class.php');
spl_autoload_register();

//loading application configuration
include_once('config.php');

$displayer = new TestDisplayer();
$displayer->display();