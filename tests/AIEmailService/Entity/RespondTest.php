<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Entity;

use HercegDoo\AIComposePlugin\AIEmailService\Entity\Respond;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class RespondTest extends TestCase
{
    public function testConstructor()
    {
        $respond = new Respond('');
        self::assertSame('', $respond->getBody());
    }

    public function testSetSubject()
    {
        $respond = new Respond('');
        $returnData = $respond->setSubject('test');
        self::assertSame('test', $respond->getSubject());
        self::assertInstanceOf(Respond::class, $returnData);
    }

    public function testSetBody()
    {
        $respond = new Respond('');
        $respond->setBody('testBody');
        self::assertSame('testBody', $respond->getBody());
    }

    public function testGetBody()
    {
        $respond = new Respond('test');
        self::assertSame('test', $respond->getBody());
    }

    public function testGetSubjectNull()
    {
        $respond = new Respond('');
        self::assertNull($respond->getSubject());
    }

    public function testSetError()
    {
        $respond = new Respond('');
        $returnData = $respond->setError('');
        self::assertSame('', $respond->getError());
        self::assertInstanceOf(Respond::class, $returnData);
    }

    public function testGetErrorEmpty()
    {
        $respond = new Respond('');
        self::assertSame('', $respond->getError());
    }

    public function testGetErrorStr()
    {
        $respond = new Respond('');
        $respond->setError('test');
        self::assertSame('test', $respond->getError());
    }

    public function testToString()
    {
        $respond = new Respond('');
        $respond->setError('testError');
        self::assertSame('testError', $respond->__toString());

        $respond1 = new Respond('testBody1');
        $respond1->setError('testError');
        self::assertSame('testBody1', $respond1->__toString());
    }
}
