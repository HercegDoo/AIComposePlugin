<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Summary;

use HercegDoo\AIComposePlugin\AIEmailService\Summary\MessageTextExtractor;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class MessageTextExtractorTest extends TestCase
{
    public function testPrefersPlainTextOverHtml(): void
    {
        $message = $this->getMockBuilder(\rcube_message::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get_part_body'])
            ->getMock()
        ;
        $message->parts = [
            (object) ['type' => 'content', 'ctype_primary' => 'text', 'ctype_secondary' => 'html', 'mime_id' => '1'],
            (object) ['type' => 'content', 'ctype_primary' => 'text', 'ctype_secondary' => 'plain', 'mime_id' => '2'],
        ];
        $message->expects(self::once())->method('get_part_body')->with('2', true, 30000)->willReturn('  Please help.  ');

        self::assertSame('Please help.', (new MessageTextExtractor())->extract($message));
    }

    public function testRemovesHtmlAndScripts(): void
    {
        $message = $this->getMockBuilder(\rcube_message::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get_part_body'])
            ->getMock()
        ;
        $message->parts = [
            (object) ['type' => 'content', 'ctype_primary' => 'text', 'ctype_secondary' => 'html', 'mime_id' => '1'],
        ];
        $message->expects(self::once())->method('get_part_body')->with('1', true, 30000)->willReturn('<style>.x{}</style><p>Bitte &amp; danke</p><script>bad()</script>');

        self::assertSame('Bitte & danke', (new MessageTextExtractor())->extract($message));
    }
}
