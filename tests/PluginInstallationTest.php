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
        self::assertArrayHasKey('roundcube/plugin-installer', $manifest['require-dev']);

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

    public function testEveryLabelLocaleHasMatchingMessages(): void
    {
        foreach (glob(\dirname(__DIR__) . '/src/localization/labels/*.inc') ?: [] as $labelsFile) {
            self::assertFileExists(\dirname(__DIR__) . '/src/localization/messages/' . basename($labelsFile));

            $labels = [];
            require $labelsFile;
            self::assertNotEmpty($labels['ai_generate_subject'] ?? null);
        }
    }

    public function testPluginLoadsWhenHostAutoloaderHasNoPluginMapping(): void
    {
        if (!\function_exists('exec')) {
            self::markTestSkipped('The PHP exec function is unavailable');
        }

        $fixture = sys_get_temp_dir() . '/aicompose-bootstrap-' . bin2hex(random_bytes(8));
        $plugin = $fixture . '/plugins/aicomposeplugin';
        mkdir($fixture . '/vendor', 0700, true);
        mkdir($plugin . '/src', 0700, true);

        try {
            file_put_contents($fixture . '/vendor/autoload.php', '<?php class rcube_plugin {}');
            copy(\dirname(__DIR__) . '/aicomposeplugin.php', $plugin . '/aicomposeplugin.php');
            copy(\dirname(__DIR__) . '/src/AbstractAIComposePlugin.php', $plugin . '/src/AbstractAIComposePlugin.php');
            file_put_contents(
                $fixture . '/probe.php',
                '<?php define("RCUBE_INSTALL_PATH", ' . var_export($fixture . '/', true)
                . '); require ' . var_export($plugin . '/aicomposeplugin.php', true)
                . '; echo is_subclass_of("aicomposeplugin", "rcube_plugin") ? "loaded" : "missing";'
            );

            $output = [];
            $exitCode = 1;
            exec(escapeshellarg(\PHP_BINARY) . ' ' . escapeshellarg($fixture . '/probe.php') . ' 2>&1', $output, $exitCode);

            self::assertSame(0, $exitCode, implode("\n", $output));
            self::assertSame('loaded', implode("\n", $output));
        } finally {
            unlink($fixture . '/probe.php');
            unlink($fixture . '/vendor/autoload.php');
            unlink($plugin . '/aicomposeplugin.php');
            unlink($plugin . '/src/AbstractAIComposePlugin.php');
            rmdir($fixture . '/vendor');
            rmdir($plugin . '/src');
            rmdir($plugin);
            rmdir($fixture . '/plugins');
            rmdir($fixture);
        }
    }
}
