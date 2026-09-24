<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Prompt;

use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use HercegDoo\AIComposePlugin\AIEmailService\Prompt\SubjectPromptBuilder;
use HercegDoo\AIComposePlugin\AIEmailService\Settings;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class SubjectPromptBuilderTest extends TestCase
{
    public function testUsesVisibleDraftTextAndRequestsOnlyAReplacementSubject(): void
    {
        if (!\defined('PHPUNIT_RUNNING')) {
            \define('PHPUNIT_RUNNING', true);
        }

        Settings::setStyles(['default' => 'casual']);
        Settings::setLengths(['default' => 'medium']);
        Settings::setLanguages(['default' => 'Bosnian']);

        $request = RequestData::make('', '', '', null, null, null, 'Bosnian');
        $prompt = (new SubjectPromptBuilder('<p>Meeting&nbsp;tomorrow</p><p>at 9</p>', 'Old subject'))->build($request);

        self::assertStringContainsString('in Bosnian', $prompt->getUserInstruction());
        self::assertStringContainsString('Meeting tomorrow at 9', $prompt->getUserInstruction());
        self::assertStringContainsString('different subject from: Old subject', $prompt->getUserInstruction());
        self::assertStringNotContainsString('<p>', $prompt->getUserInstruction());
    }
}
