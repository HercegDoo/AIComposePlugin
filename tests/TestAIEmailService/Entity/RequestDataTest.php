<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\TestAIEmailService\Entity;

use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class RequestDataTest extends TestCase
{
    public function testConstructorWithValues()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstrukcija', 'TestStil', 'TestLength', 'TestCreativity', 'TestLang');

        self::assertSame('Meho', $requestData->getRecipientName());
        self::assertSame('Muhi', $requestData->getSenderName());
        self::assertSame('TestInstrukcija', $requestData->getInstruction());
        self::assertSame('TestStil', $requestData->getStyle());
        self::assertSame('TestLength', $requestData->getLength());
        self::assertSame('TestCreativity', $requestData->getCreativity());
        self::assertSame('TestLang', $requestData->getLanguage());
    }

    public function testConstructorWithoutValuesForDefaults()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstrukcija');

        self::assertSame('Meho', $requestData->getRecipientName());
        self::assertSame('Muhi', $requestData->getSenderName());
        self::assertSame('TestInstrukcija', $requestData->getInstruction());
        self::assertSame('casual', $requestData->getStyle());
        self::assertSame('medium', $requestData->getLength());
        self::assertSame('medium', $requestData->getCreativity());
        self::assertSame('Bosnian', $requestData->getLanguage());
    }

    public function testSetRecipientName()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstrukcija');
        $returnedValue = $requestData->setRecipientName('Muhi');

        self::assertSame('Muhi', $requestData->getRecipientName());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetSenderName()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestStil');
        $returnedValue = $requestData->setSenderName('Jaha');
        self::assertSame('Jaha', $requestData->getSenderName());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetInstruction()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setInstruction('MyInstructions');
        self::assertSame('MyInstructions', $requestData->getInstruction());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetStyle()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstruction', 'TestStyle');
        $returnedValue = $requestData->setStyle('MyStyle');
        self::assertSame('MyStyle', $requestData->getStyle());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetLength()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'Instruction');
        $returnedValue = $requestData->setLength('MyLength');
        self::assertSame('MyLength', $requestData->getLength());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetCreativity()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setCreativity('MyCreativity');
        self::assertSame('MyCreativity', $requestData->getCreativity());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetLanguage()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setLanguage('MyLanguage');
        self::assertSame('MyLanguage', $requestData->getLanguage());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testGetRecipientName()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstruction');
        $returnData = $requestData->getRecipientName();
        self::assertSame('Meho', $returnData);
        self::assertIsString($returnData);
    }

    public function testSetRecipientEmail()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setRecipientEmail('dummymail@example.com');
        self::assertSame('dummymail@example.com', $requestData->getRecipientEmail());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetRecipientEmailNull()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setRecipientEmail(null);
        self::assertNull($requestData->getRecipientEmail());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetSenderEmail()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setSenderEmail('dummymail@example.com');
        self::assertSame('dummymail@example.com', $requestData->getSenderEmail());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetSenderEmailNull()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setSenderEmail(null);
        self::assertNull($requestData->getSenderEmail());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetFixTextNull()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setFixText(null, 'Meho');
        self::assertNull($requestData->getPreviousGeneratedEmail());
        self::assertSame('Meho', $requestData->getFixText());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetFixTextStr()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setFixText('MyFixedText', 'Meho');
        self::assertSame('MyFixedText', $requestData->getPreviousGeneratedEmail());
        self::assertSame('Meho', $requestData->getFixText());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetPreviousConversationNull()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setPreviousConversation(null);
        self::assertNull($requestData->getPreviousConversation());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetPreviousConversationStr()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setPreviousConversation('MyPreviousConversation');
        self::assertSame('MyPreviousConversation', $requestData->getPreviousConversation());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testGetSenderName()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstruction');
        $returnData = $requestData->getSenderName();
        self::assertSame('Muhi', $returnData);
    }

    public function testGetSenderEmailNull()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstruction');
        $returnData = $requestData->getSenderEmail();
        self::assertNull($requestData->getSenderEmail());
    }

    public function testGetSenderEmailStr()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstruction');
        $requestData->setSenderEmail('dummymail@example.com');
        $returnData = $requestData->getSenderEmail();
        self::assertSame('dummymail@example.com', $requestData->getSenderEmail());
    }

    public function testGetStyle()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstruction');
        $returnData = $requestData->getStyle();
        self::assertSame('casual', $returnData);
    }

    public function testGetLength()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'Instruction');
        $returnData = $requestData->getLength();
        self::assertSame('medium', $returnData);
    }

    public function testGetCreativity()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstruction');
        $returnData = $requestData->getCreativity();
        self::assertSame('medium', $returnData);
    }

    public function testGetLanguage()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstruction');
        $returnData = $requestData->getLanguage();
        self::assertSame('Bosnian', $returnData);
    }

    public function testGetInstruction()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstruction');
        $returnData = $requestData->getInstruction();
        self::assertSame('TestInstruction', $returnData);
    }

    public function testGetFixTestNull()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstruction');
        self::assertNull($requestData->getFixText());
    }

    public function testGetFixTestStr()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstruction');
        $requestData->setFixText('dummymail', 'myFixText');
        self::assertSame('myFixText', $requestData->getFixText());
    }

    public function testGetPreviousGeneratedEmailNull()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstruction');
        self::assertNull($requestData->getPreviousGeneratedEmail());
    }

    public function testGetPreviousGeneratedEmailStr()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstruction');
        $requestData->setFixText('dummymail', 'myPreviousGeneratedEmail');
        self::assertSame('dummymail', $requestData->getPreviousGeneratedEmail());
    }

    public function testGetPreviousConversationNull()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstruction');
        self::assertNull($requestData->getPreviousConversation());
    }

    public function testGetPreviousConversationStr()
    {
        $requestData = new RequestData('Meho', 'Muhi', 'TestInstruction');
        $requestData->setPreviousConversation('MyPreviousConversation');
        self::assertSame('MyPreviousConversation', $requestData->getPreviousConversation());
    }
}
