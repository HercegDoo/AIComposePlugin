<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Translation;

use HercegDoo\AIComposePlugin\AIEmailService\Translation\MessageTranslationText;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class MessageTranslationTextTest extends TestCase
{
    public function testExtractsEntirePlainTextAndChunksWithoutLosingCharacters(): void
    {
        $body = str_repeat("Dobar dan. Molim odgovor sutra.\n\n", 450);
        $message = $this->getMockBuilder(\rcube_message::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get_part_body'])
            ->getMock()
        ;
        $message->parts = [(object) ['type' => 'content', 'ctype_primary' => 'text', 'ctype_secondary' => 'plain', 'mime_id' => '1']];
        $message->expects(self::once())->method('get_part_body')
            ->with('1', true)->willReturn($body)
        ;

        $extractor = new MessageTranslationText();
        $text = $extractor->extract($message);
        $chunks = $extractor->chunks($text);

        self::assertGreaterThan(12000, mb_strlen($text, 'UTF-8'));
        self::assertGreaterThan(1, \count($chunks));
        self::assertSame($text, implode('', $chunks));
        foreach ($chunks as $chunk) {
            self::assertLessThanOrEqual(MessageTranslationText::CHUNK_CHARACTERS, mb_strlen($chunk, 'UTF-8'));
        }
    }

    public function testHtmlEmailKeepsParagraphsWithoutSendingMarkup(): void
    {
        $message = $this->getMockBuilder(\rcube_message::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get_part_body'])
            ->getMock()
        ;
        $message->parts = [(object) ['type' => 'content', 'ctype_primary' => 'text', 'ctype_secondary' => 'html', 'mime_id' => '1']];
        $message->method('get_part_body')->willReturn('<p>Prvi dio.</p><p>Drugi dio.</p><script>ignore()</script>');

        $text = (new MessageTranslationText())->extract($message);

        self::assertStringContainsString('Prvi dio.', $text);
        self::assertStringContainsString('Drugi dio.', $text);
        self::assertStringNotContainsString('<script>', $text);
        self::assertStringNotContainsString('ignore()', $text);
    }

    public function testTooLongMessageFailsInsteadOfReturningPartialTranslation(): void
    {
        $message = $this->getMockBuilder(\rcube_message::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get_part_body'])
            ->getMock()
        ;
        $message->parts = [(object) ['type' => 'content', 'ctype_primary' => 'text', 'ctype_secondary' => 'plain', 'mime_id' => '1']];
        $message->method('get_part_body')->willReturn(str_repeat('a', MessageTranslationText::MAX_CHARACTERS + 1));

        $this->expectException(\LengthException::class);
        (new MessageTranslationText())->extract($message);
    }

    public function testOversizedRawPartIsRejectedBeforeFetch(): void
    {
        $message = $this->getMockBuilder(\rcube_message::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get_part_body'])
            ->getMock()
        ;
        $message->parts = [(object) [
            'type' => 'content',
            'ctype_primary' => 'text',
            'ctype_secondary' => 'plain',
            'mime_id' => '1',
            'size' => 1000001,
        ]];
        $message->expects(self::never())->method('get_part_body');

        $this->expectException(\LengthException::class);
        (new MessageTranslationText())->extract($message);
    }
}
