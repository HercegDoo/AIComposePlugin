<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Style;

use HercegDoo\AIComposePlugin\AIEmailService\Style\SentStyleSampler;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class SentStyleSamplerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('RCUBE_CHARSET')) {
            \define('RCUBE_CHARSET', 'UTF-8');
        }
    }

    public function testPrefersSameRecipientAndUsesOnlySelectedIdentity(): void
    {
        $storage = $this->getMockBuilder(\rcube_imap::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get_folder', 'get_search_set', 'search_once', 'set_search_set', 'index', 'get_message_headers', 'set_folder'])
            ->getMock()
        ;
        $storage->method('get_folder')->willReturn('INBOX');
        $storage->method('get_search_set')->willReturn(null);
        $search = $this->createMock(\rcube_result_index::class);
        $search->method('get')->willReturn(['1', '2']);
        $storage->expects(self::once())->method('search_once')->with('Sent', 'OR TO "nahid@example.com" CC "nahid@example.com"')->willReturn($search);
        $recent = $this->createMock(\rcube_result_index::class);
        $recent->method('get')->willReturn(['4', '3', '2', '1']);
        $storage->expects(self::once())->method('index')->with('Sent', 'date', 'DESC', true)->willReturn($recent);
        $storage->expects(self::once())->method('set_folder')->with('INBOX');
        $headers = [
            '1' => (object) ['from' => 'Me <me@example.com>', 'to' => 'Nahid <nahid@example.com>', 'cc' => ''],
            '2' => (object) ['from' => 'Other <other@example.com>', 'to' => 'nahid@example.com', 'cc' => ''],
            '3' => (object) ['from' => 'me@example.com', 'to' => 'team@example.com', 'cc' => ''],
            '4' => (object) ['from' => 'me@example.com', 'to' => 'someone@example.com', 'cc' => ''],
        ];
        $storage->method('get_message_headers')->willReturnCallback(static function (int $uid) use ($headers) {
            return $headers[$uid];
        });

        $loaded = [];
        $sampler = new SentStyleSampler(function (string $uid) use (&$loaded): \rcube_message {
            $loaded[] = $uid;
            $message = $this->getMockBuilder(\rcube_message::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['get_part_body'])
                ->getMock()
            ;
            $message->parts = [(object) ['type' => 'content', 'ctype_primary' => 'text', 'ctype_secondary' => 'plain', 'mime_id' => '1']];
            $message->method('get_part_body')->willReturn('Hello Nahid, thanks for the update. I will review this and respond tomorrow.');

            return $message;
        });

        $examples = $sampler->collect($storage, 'Sent', 'me@example.com', 'nahid@example.com');

        self::assertSame(['1', '4', '3'], $loaded);
        self::assertCount(3, $examples);
        self::assertTrue($examples[0]['sameRecipient']);
        self::assertFalse($examples[1]['sameRecipient']);
        self::assertFalse($examples[2]['sameRecipient']);
    }

    public function testSkipsLookupWithoutSelectedSenderAddress(): void
    {
        $storage = $this->createMock(\rcube_imap::class);
        $storage->expects(self::never())->method('search_once');

        self::assertSame([], (new SentStyleSampler())->collect($storage, 'Sent', '', 'nahid@example.com'));
    }

    public function testFallsBackToRecentSentMessagesWhenRecipientSearchFails(): void
    {
        $storage = $this->getMockBuilder(\rcube_imap::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get_folder', 'get_search_set', 'search_once', 'set_search_set', 'index', 'get_message_headers', 'set_folder'])
            ->getMock()
        ;
        $storage->method('get_folder')->willReturn('INBOX');
        $storage->method('get_search_set')->willReturn(null);
        $storage->method('search_once')->willThrowException(new \RuntimeException('IMAP search unavailable'));
        $recent = $this->createMock(\rcube_result_index::class);
        $recent->method('get')->willReturn(['5']);
        $storage->expects(self::once())->method('index')->with('Sent', 'date', 'DESC', true)->willReturn($recent);
        $storage->method('get_message_headers')->willReturn((object) [
            'from' => 'me@example.com',
            'to' => 'other@example.com',
            'cc' => '',
        ]);

        $sampler = new SentStyleSampler(function (): \rcube_message {
            $message = $this->getMockBuilder(\rcube_message::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['get_part_body'])
                ->getMock()
            ;
            $message->parts = [(object) ['type' => 'content', 'ctype_primary' => 'text', 'ctype_secondary' => 'plain', 'mime_id' => '1']];
            $message->method('get_part_body')->willReturn('Hello there, thanks for your message. I will send the details later today.');

            return $message;
        });

        $examples = $sampler->collect($storage, 'Sent', 'me@example.com', 'nahid@example.com');

        self::assertCount(1, $examples);
        self::assertFalse($examples[0]['sameRecipient']);
    }
}
