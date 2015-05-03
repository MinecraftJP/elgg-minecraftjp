<?php
require(dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'autoload.php');
require(dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'vendor'  . DIRECTORY_SEPARATOR . 'autoload.php');

elgg_register_event_handler('init', 'system', array('ElggMinecraftJP\App', 'init'));