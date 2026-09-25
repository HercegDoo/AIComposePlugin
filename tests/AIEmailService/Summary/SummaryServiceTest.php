<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Summary;

use HercegDoo\AIComposePlugin\AIEmailService\Exceptions\ProviderException;
use HercegDoo\AIComposePlugin\AIEmailService\Prompt\EmailPrompt;
use HercegDoo\AIComposePlugin\AIEmailService\Providers\CompletionProviderInterface;
use HercegDoo\AIComposePlugin\AIEmailService\Summary\SummaryService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class SummaryServiceTest extends TestCase
{
    public function testSummarizesAndTranslatesIntoInterfaceLanguage(): void
    {
        $provider = new class implements CompletionProviderInterface {
            public ?EmailPrompt $prompt = null;

            public function complete(EmailPrompt $prompt, array $config): string
            {
                $this->prompt = $prompt;

                return '{"source_language":"German","original_summary":"Kunde bittet um Hilfe.","translated_summary":"Customer requests help."}';
            }
        };

        $result = (new SummaryService($provider, []))->summarize('Login issue', 'Ich kann mich nicht anmelden.', 'en_US');

        self::assertSame('German', $result['sourceLanguage']);
        self::assertSame('Kunde bittet um Hilfe.', $result['originalSummary']);
        self::assertSame('Customer requests help.', $result['translatedSummary']);
        self::assertStringContainsString('en_US', $provider->prompt->getUserInstruction());
        self::assertStringContainsString('Ich kann mich nicht anmelden.', $provider->prompt->getUserInstruction());
    }

    public function testRejectsIncompleteProviderResponse(): void
    {
        $provider = new class implements CompletionProviderInterface {
            public function complete(EmailPrompt $prompt, array $config): string
            {
                return '{"source_language":"German","original_summary":"Hallo"}';
            }
        };

        $this->expectException(ProviderException::class);
        (new SummaryService($provider, []))->summarize('', 'Hallo', 'en_US');
    }

    public function testPassesExpandedLengthToProviderForOpenedMessages(): void
    {
        $provider = new class implements CompletionProviderInterface {
            public ?EmailPrompt $prompt = null;

            public function complete(EmailPrompt $prompt, array $config): string
            {
                $this->prompt = $prompt;

                return '{"source_language":"German","original_summary":"Kunde benötigt Hilfe.","translated_summary":"Customer needs help."}';
            }
        };

        (new SummaryService($provider, []))->summarize('Login issue', 'Ich kann mich nicht anmelden.', 'en_US', 2);

        self::assertStringContainsString('at most 75 words and no more than 2 sentences', $provider->prompt->getUserInstruction());
    }

    public function testStripsHtmlFromProviderResponse(): void
    {
        $provider = new class implements CompletionProviderInterface {
            public function complete(EmailPrompt $prompt, array $config): string
            {
                return '{"source_language":"German","original_summary":"<b>Hallo</b>","translated_summary":"<script>bad</script>Hello"}';
            }
        };

        $result = (new SummaryService($provider, []))->summarize('', 'Hallo', 'en_US');
        self::assertSame('Hallo', $result['originalSummary']);
        self::assertSame('Hello', $result['translatedSummary']);
    }

    public function testReturnsAtMostThreeCleanSuggestionsForClearRequests(): void
    {
        $provider = new class implements CompletionProviderInterface {
            public function complete(EmailPrompt $prompt, array $config): string
            {
                return json_encode([
                    'source_language' => 'English',
                    'original_summary' => 'Sender requests a decision.',
                    'translated_summary' => 'Sender requests a decision.',
                    'reply_intent_clear' => true,
                    'reply_suggestions' => [
                        ['label' => '<b>Agree</b>', 'instruction' => 'Accept the proposal.'],
                        ['label' => 'Decline', 'instruction' => 'Politely decline.'],
                        ['label' => 'Ask a question', 'instruction' => 'Ask which date works.'],
                        ['label' => 'Another', 'instruction' => 'This must be discarded.'],
                    ],
                ], \JSON_THROW_ON_ERROR);
            }
        };

        $result = (new SummaryService($provider, []))->summarize('Proposal', 'Can you approve?', 'en_US', 1, true);

        self::assertCount(3, $result['replySuggestions']);
        self::assertSame('Agree', $result['replySuggestions'][0]['label']);
        self::assertSame('Accept the proposal.', $result['replySuggestions'][0]['instruction']);
    }

    public function testHidesSuggestionsWhenIntentIsUnclear(): void
    {
        $provider = new class implements CompletionProviderInterface {
            public function complete(EmailPrompt $prompt, array $config): string
            {
                return '{"source_language":"English","original_summary":"Status update.","translated_summary":"Status update.","reply_intent_clear":false,"reply_suggestions":[{"label":"Reply","instruction":"Say something."}]}';
            }
        };

        $result = (new SummaryService($provider, []))->summarize('Update', 'For your information.', 'en_US', 1, true);

        self::assertSame([], $result['replySuggestions']);
    }
}
