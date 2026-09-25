<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Style;

use HercegDoo\AIComposePlugin\AIEmailService\Style\SentStyleTextExtractor;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class SentStyleTextExtractorTest extends TestCase
{
    public function testRemovesQuotedThreadAndSignatureFromPlainMessage(): void
    {
        $message = $this->getMockBuilder(\rcube_message::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get_part_body'])
            ->getMock()
        ;
        $message->parts = [(object) ['type' => 'content', 'ctype_primary' => 'text', 'ctype_secondary' => 'plain', 'mime_id' => '1']];
        $message->expects(self::once())->method('get_part_body')->with('1', true, 16000)->willReturn("Zdravo Nahide,\n\nHvala ti na ponudi. Javit ću se sutra kad pregledam detalje.\n\nLijep pozdrav,\nMoje ime\n\n> Stara poruka");

        self::assertSame("Zdravo Nahide,\n\nHvala ti na ponudi. Javit ću se sutra kad pregledam detalje.", (new SentStyleTextExtractor())->extract($message));
    }

    public function testRemovesHtmlQuoteAndSignature(): void
    {
        $message = $this->getMockBuilder(\rcube_message::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get_part_body'])
            ->getMock()
        ;
        $message->parts = [(object) ['type' => 'content', 'ctype_primary' => 'text', 'ctype_secondary' => 'html', 'mime_id' => '1']];
        $message->expects(self::once())->method('get_part_body')->with('1', true, 16000)->willReturn('<p>Pozdrav,</p><p>Hvala &amp; čujemo se sutra.</p><div id="_rc_sig">Ime i telefon</div><blockquote>Tuđi tekst</blockquote>');

        self::assertSame("Pozdrav,\nHvala & čujemo se sutra.", (new SentStyleTextExtractor())->extract($message));
    }
}
