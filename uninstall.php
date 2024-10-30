<?php

if (!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

require_once('CF7RESTPlugin.php');
$aPlugin = new CF7RESTPlugin();
$aPlugin->uninstall();

