<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Prompt;

use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use HercegDoo\AIComposePlugin\AIEmailService\Prompt\EmailPromptBuilder;
use HercegDoo\AIComposePlugin\AIEmailService\Settings;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class EmailPromptBuilderTest extends TestCase
{
    private RequestData $requestData;

    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PHPUNIT_RUNNING')) {
            \define('PHPUNIT_RUNNING', true);
        }

        Settings::setStyles(['professional', 'default' => 'casual', 'assertive', 'enthusiastic', 'funny', 'informational', 'persuasive']);
        Settings::setLengths(['short', 'default' => 'medium', 'long']);
        Settings::setLanguages(['default' => 'Bosnian', 'Croatian', 'German', 'Dutch']);

        $this->requestData = RequestData::make('Meho', 'Muhi', 'TestInstrukcija');
    }

    public function testSystemInstruction(): void
    {
        $prompt = (new EmailPromptBuilder())->build($this->requestData);

        self::assertStringContainsString('Write only the new email or reply', $prompt->getSystemInstruction());
        self::assertStringContainsString('Never copy quoted conversation', $prompt->getSystemInstruction());
    }

    public function testNewEmailUsesRequestDetails(): void
    {
        $instruction = (new EmailPromptBuilder())->build($this->requestData)->getUserInstruction();

        self::assertStringContainsString('Create a casual email', $instruction);
        self::assertStringContainsString('Without a subject', $instruction);
        self::assertStringContainsString('*Recipient: Meho', $instruction);
        self::assertStringContainsString('*Sender: Muhi', $instruction);
        self::assertStringContainsString('*Language: Bosnian', $instruction);
        self::assertStringContainsString('*Length: medium', $instruction);
        self::assertStringContainsString('TestInstrukcija', $instruction);
        self::assertStringContainsString('70 to 150 words', $instruction);
        self::assertStringContainsString("Greeting\n\nContent\n\nClosing Greeting", $instruction);
    }

    public function testSubjectAndPreviousConversationAreIncluded(): void
    {
        $this->requestData->setSubject('Quarterly report');
        $this->requestData->setPreviousConversation('Earlier note');
        $this->requestData->setRecipientName('');

        $instruction = (new EmailPromptBuilder())->build($this->requestData)->getUserInstruction();

        self::assertStringContainsString('Subject: Quarterly report', $instruction);
        self::assertStringContainsString('<previous_conversation>Earlier note</previous_conversation>', $instruction);
        self::assertStringContainsString('do not repeat, quote, summarize, or append any part of it', $instruction);
        self::assertStringNotContainsString('*Recipient:', $instruction);
        self::assertStringNotContainsString('Without a subject', $instruction);
    }

    public function testPluralInstructionOnlyAppearsForMultipleRecipients(): void
    {
        $builder = new EmailPromptBuilder();
        $singular = $builder->build($this->requestData)->getUserInstruction();

        $this->requestData->setMultipleRecipients(true);
        $plural = $builder->build($this->requestData)->getUserInstruction();

        self::assertStringNotContainsString('Address the recipient in plural form.', $singular);
        self::assertStringContainsString('Address the recipient in plural form.', $plural);
    }

    public function testSignatureInstructionOnlyAppearsWhenSignatureExists(): void
    {
        $builder = new EmailPromptBuilder();
        $withoutSignature = $builder->build($this->requestData)->getUserInstruction();

        $this->requestData->setSignaturePresent(true);
        $withSignature = $builder->build($this->requestData)->getUserInstruction();

        self::assertStringContainsString('Closing Greeting', $withoutSignature);
        self::assertStringNotContainsString('End the email after its message body', $withoutSignature);
        self::assertStringNotContainsString('Closing Greeting', $withSignature);
        self::assertStringContainsString('End the email after its message body', $withSignature);
        self::assertStringContainsString('The editor already contains the sender\'s signature', $builder->build($this->requestData)->getSystemInstruction());
    }

    public function testCustomStyleLengthAndLanguage(): void
    {
        $requestData = RequestData::make('Ime1', 'Ime2', 'Sastavi Mail', 'professional', 'long', 'low', 'Spanish');

        $instruction = (new EmailPromptBuilder())->build($requestData)->getUserInstruction();

        self::assertStringContainsString('Create a professional email', $instruction);
        self::assertStringContainsString('*Language: Spanish', $instruction);
        self::assertStringContainsString('*Length: long', $instruction);
        self::assertStringContainsString('over 150 words', $instruction);
        self::assertStringContainsString('Sastavi Mail', $instruction);
    }

    public function testRevisionUsesSelectedTextAndConversation(): void
    {
        $this->requestData->setFixText('Previously generated email', 'selected text');
        $this->requestData->setPreviousConversation('Earlier note');

        $instruction = (new EmailPromptBuilder())->build($this->requestData)->getUserInstruction();

        self::assertStringContainsString('Previously generated email', $instruction);
        self::assertStringContainsString('selected text', $instruction);
        self::assertStringContainsString('TestInstrukcija', $instruction);
        self::assertStringContainsString('<previous_conversation>Earlier note</previous_conversation>', $instruction);
        self::assertStringNotContainsString('Create a casual email', $instruction);
    }

    public function testRevisionWithoutPreviousConversation(): void
    {
        $this->requestData->setFixText('Previous email', 'selected text');

        $instruction = (new EmailPromptBuilder())->build($this->requestData)->getUserInstruction();

        self::assertStringContainsString('Previous email', $instruction);
        self::assertStringContainsString('selected text', $instruction);
        self::assertStringNotContainsString('<previous_conversation>', $instruction);
    }

    public function testSentExamplesAffectToneWithoutOverridingCurrentInstructions(): void
    {
        $this->requestData->setStyleExamples([
            ['body' => 'Zdravo Nahide, hvala ti na brzom odgovoru.', 'sameRecipient' => true],
            ['body' => 'Poštovani, javit ću vam se sutra.', 'sameRecipient' => false],
        ]);

        $instruction = (new EmailPromptBuilder())->build($this->requestData)->getUserInstruction();

        self::assertStringContainsString('<style_example recipient="same recipient">', $instruction);
        self::assertStringContainsString('Zdravo Nahide, hvala ti na brzom odgovoru.', $instruction);
        self::assertStringContainsString('do not copy their facts, requests, names, addresses, dates, or wording', $instruction);
        self::assertStringContainsString('current instruction, language, length, style selection, and existing signature rules take priority', $instruction);
        self::assertStringContainsString('<style_example recipient="another recipient">', $instruction);

        $this->requestData->setFixText('Earlier draft', 'selected text');
        self::assertStringContainsString('<style_example recipient="same recipient">', (new EmailPromptBuilder())->build($this->requestData)->getUserInstruction());
    }

    public function testRevisionWithExistingSignatureOmitsClosing(): void
    {
        $this->requestData->setFixText('Previous email', 'selected text');
        $this->requestData->setSignaturePresent(true);

        $instruction = (new EmailPromptBuilder())->build($this->requestData)->getUserInstruction();

        self::assertStringContainsString('End the email after its message body', $instruction);
        self::assertStringNotContainsString('Closing Greeting', $instruction);
    }

    public function testHtmlModeRequestsSafeEditorMarkupAndCountsOnlyVisibleWords(): void
    {
        $this->requestData->setHtmlMode(true);

        $instruction = (new EmailPromptBuilder())->build($this->requestData)->getUserInstruction();

        self::assertStringContainsString('70 to 150 words', $instruction);
        self::assertStringContainsString('HTML fragment suitable for the Roundcube TinyMCE email editor', $instruction);
        self::assertStringContainsString('Count only visible words', $instruction);
        self::assertStringContainsString('HTML tags and attributes do not count', $instruction);
        self::assertStringContainsString('without Markdown fences', $instruction);
    }

    public function testHtmlRevisionPreservesHtmlFormat(): void
    {
        $this->requestData->setHtmlMode(true);
        $this->requestData->setFixText('<p>Hello <strong>Meho</strong></p>', 'Meho');

        $instruction = (new EmailPromptBuilder())->build($this->requestData)->getUserInstruction();

        self::assertStringContainsString('<p>Hello <strong>Meho</strong></p>', $instruction);
        self::assertStringContainsString('HTML fragment suitable for the Roundcube TinyMCE email editor', $instruction);
    }

    public function testPlainModeExplicitlyRequestsText(): void
    {
        $instruction = (new EmailPromptBuilder())->build($this->requestData)->getUserInstruction();

        self::assertStringContainsString('Return only plain text, without HTML or Markdown formatting.', $instruction);
    }
}
