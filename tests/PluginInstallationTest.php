<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests;

use HercegDoo\AIComposePlugin\Actions\Mail\GenereteEmailAction;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class PluginInstallationTest extends TestCase
{
    public function testComposerNameMatchesRoundcubeEntryPointClassAndResourcePath(): void
    {
        $root = \dirname(__DIR__);
        $manifest = json_decode((string) file_get_contents($root . '/composer.json'), true);
        self::assertSame('roundcube-plugin', $manifest['type']);
        self::assertArrayHasKey('roundcube/plugin-installer', $manifest['require']);

        $pluginName = explode('/', $manifest['name'])[1];
        $entryPoint = $root . '/' . $pluginName . '.php';
        self::assertFileExists($entryPoint);

        require_once $entryPoint;
        $api = new class {
            public string $dir = '/roundcube/plugins/';
            public string $url = '/plugins/';
        };
        $plugin = new \aicomposeplugin($api);

        self::assertSame($pluginName, $plugin->ID);
        self::assertSame('/plugins/aicomposeplugin/assets/dist/compose.bundle.js', $plugin->url('assets/dist/compose.bundle.js'));
        self::assertSame('plugin.aicomposeplugin_GenereteEmailAction', GenereteEmailAction::getActionSlug());
    }

    public function testInstallerCopiedConfigTemplateIsValidPhp(): void
    {
        $config = [];
        require \dirname(__DIR__) . '/config.inc.php.dist';

        self::assertSame('OpenAI', $config['aiComposeProvider']);
        self::assertSame('', $config['aiProviderOpenAIConfig']['apiKey']);
        self::assertSame('gpt-4.1', $config['aiProviderOpenAIConfig']['model']);
    }
}
