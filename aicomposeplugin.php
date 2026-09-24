<?php

$roundcubeRoot = \defined('RCUBE_INSTALL_PATH') ? RCUBE_INSTALL_PATH : \dirname(__DIR__, 2);
$roundcubeAutoload = rtrim($roundcubeRoot, '/\\') . '/vendor/autoload.php';
$pluginAutoload = __DIR__ . '/vendor/autoload.php';

require_once is_file($roundcubeAutoload) ? $roundcubeAutoload : $pluginAutoload;

use HercegDoo\AIComposePlugin\AbstractAIComposePlugin;

/**
 * @description Load plugin to roundcube
 */
final class aicomposeplugin extends AbstractAIComposePlugin
{
}
