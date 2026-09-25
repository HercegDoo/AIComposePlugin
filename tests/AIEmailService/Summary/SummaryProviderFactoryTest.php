<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Summary;

use HercegDoo\AIComposePlugin\AIEmailService\Providers\Gemini;
use HercegDoo\AIComposePlugin\AIEmailService\Summary\SummaryProviderFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class SummaryProviderFactoryTest extends TestCase
{
    public function testGeminiSummaryInheritsComposeCredentialsAndAppliesOverrides(): void
    {
        $values = [
            'aiSummaryProvider' => 'Gemini',
            'aiProviderGeminiConfig' => ['apiKey' => 'compose-key', 'model' => 'gemini-3.8-flash'],
            'aiSummaryGeminiConfig' => ['model' => 'gemini-3.7-flash', 'maxTokens' => 1200],
        ];
        $settings = $this->getMockBuilder(\rcube_config::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock()
        ;
        $settings->method('get')->willReturnCallback(static function (string $name, $default = null) use ($values) {
            return $values[$name] ?? $default;
        });

        [$provider, $config] = (new SummaryProviderFactory())->create($settings);

        self::assertInstanceOf(Gemini::class, $provider);
        self::assertSame('compose-key', $config['apiKey']);
        self::assertSame('gemini-3.7-flash', $config['model']);
        self::assertSame(1200, $config['maxTokens']);
        self::assertSame(0, $config['temperature']);
    }
}
