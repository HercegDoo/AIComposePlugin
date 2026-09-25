<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Summary;

use HercegDoo\AIComposePlugin\AIEmailService\Summary\SummaryPromptBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class SummaryPromptBuilderTest extends TestCase
{
    public function testOpenedMessageLengthAddsAtMostTwoSentences(): void
    {
        self::assertSame(1, SummaryPromptBuilder::sentenceCountForBody(str_repeat('word ', 99)));
        self::assertSame(2, SummaryPromptBuilder::sentenceCountForBody(str_repeat('word ', 100)));
        self::assertSame(2, SummaryPromptBuilder::sentenceCountForBody(str_repeat('word ', 299)));
        self::assertSame(3, SummaryPromptBuilder::sentenceCountForBody(str_repeat('word ', 300)));
        self::assertSame(3, SummaryPromptBuilder::sentenceCountForBody(str_repeat('word ', 1000)));
    }

    public function testPreviewRemainsShortAndLongMessagesIncludeRelevantContext(): void
    {
        $builder = new SummaryPromptBuilder();
        $preview = $builder->build('Subject', 'Body', 'en_US')->getUserInstruction();
        $opened = $builder->build('Subject', 'Body', 'en_US', 3)->getUserInstruction();

        self::assertStringContainsString('at most 45 words and no more than 1 sentence', $preview);
        self::assertStringNotContainsString('earlier context', $preview);
        self::assertStringContainsString('at most 95 words and no more than 3 sentences', $opened);
        self::assertStringContainsString('only earlier context needed to understand the latest request or decision', $opened);
        self::assertStringNotContainsString('reply_suggestions', $preview);
    }

    public function testOpenedMessageRequestsSuggestionsOnlyForClearReplyIntent(): void
    {
        $prompt = (new SummaryPromptBuilder())->build('Meeting', 'Can we meet tomorrow?', 'en_US', 1, true);

        self::assertSame('summary', $prompt->getPurpose());
        self::assertStringContainsString('reply_intent_clear as a boolean', $prompt->getUserInstruction());
        self::assertStringContainsString('If the intent or required details are unclear', $prompt->getUserInstruction());
        self::assertStringContainsString('return an empty array', $prompt->getUserInstruction());
    }

    public function testTranslationCanBeSkippedOrTargetAnExplicitLanguage(): void
    {
        $builder = new SummaryPromptBuilder();
        $original = $builder->build('Betreff', 'Hallo', 'en_US', 1, false, false)->getUserInstruction();
        $french = $builder->build('Betreff', 'Hallo', 'fr_FR')->getUserInstruction();

        self::assertStringContainsString('Do not translate the summary; copy original_summary exactly', $original);
        self::assertStringContainsString('Translate that same summary into fr_FR', $french);
    }
}
