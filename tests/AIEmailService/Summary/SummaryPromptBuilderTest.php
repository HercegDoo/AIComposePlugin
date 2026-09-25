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
    }
}
