<?php

$roundcubeRoot = \defined('RCUBE_INSTALL_PATH') ? RCUBE_INSTALL_PATH : \dirname(__DIR__, 2);
$roundcubeAutoload = rtrim($roundcubeRoot, '/\\') . '/vendor/autoload.php';
$pluginAutoload = __DIR__ . '/vendor/autoload.php';

if (is_file($roundcubeAutoload)) {
    require_once $roundcubeAutoload;
}

if (is_file($pluginAutoload) && $pluginAutoload !== $roundcubeAutoload) {
    require_once $pluginAutoload;
}

// Packaged Roundcube installations may have a host Composer autoloader that
// does not know about plugins copied into the plugins/ directory.
spl_autoload_register(static function (string $class): void {
    $prefix = 'HercegDoo\\AIComposePlugin\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    if ($relative === '' || strpos($relative, '..') !== false || strpos($relative, '/') !== false) {
        return;
    }

    $file = __DIR__ . '/src/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
}, true, true);

use HercegDoo\AIComposePlugin\AbstractAIComposePlugin;

if (!class_exists(AbstractAIComposePlugin::class)) {
    throw new RuntimeException('AIComposePlugin source files are missing from ' . __DIR__ . '/src');
}

/**
 * @description Load plugin to roundcube
 */
final class aicomposeplugin extends AbstractAIComposePlugin
{
}
