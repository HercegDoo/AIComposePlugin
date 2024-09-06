<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Entity;

use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use HercegDoo\AIComposePlugin\AIEmailService\Settings;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class RequestDataTest extends TestCase
{
    public function settingsSetters()
    {
        Settings::setStyles(['professional', 'default' => 'casual', 'assertive', 'enthusiastic', 'funny', 'informational', 'persuasive',]);

        Settings::setLengths(['short', 'default' => 'medium', 'long',]);

        Settings::setLanguages(['default' => 'Bosnian', 'Croatian', 'German', 'Dutch',]);
    }

    public function testReturnInstance()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstrukcija');
        self::assertInstanceOf(RequestData::class, $requestData);
    }

    public function testConstructorWithValues()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstrukcija', 'TestStil', 'TestLength', 'TestCreativity', 'TestLang');

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
        $this->settingsSetters();

        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstrukcija');

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
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstrukcija');
        $returnedValue = $requestData->setRecipientName('Muhi');

        self::assertSame('Muhi', $requestData->getRecipientName());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetSenderName()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestStil');
        $returnedValue = $requestData->setSenderName('Jaha');
        self::assertSame('Jaha', $requestData->getSenderName());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetInstruction()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setInstruction('MyInstructions');
        self::assertSame('MyInstructions', $requestData->getInstruction());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetStyle()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction', 'TestStyle');
        $returnedValue = $requestData->setStyle('MyStyle');
        self::assertSame('MyStyle', $requestData->getStyle());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetLength()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'Instruction');
        $returnedValue = $requestData->setLength('MyLength');
        self::assertSame('MyLength', $requestData->getLength());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetCreativity()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setCreativity('MyCreativity');
        self::assertSame('MyCreativity', $requestData->getCreativity());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetLanguage()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setLanguage('MyLanguage');
        self::assertSame('MyLanguage', $requestData->getLanguage());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testGetRecipientName()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnData = $requestData->getRecipientName();
        self::assertSame('Meho', $returnData);
        self::assertIsString($returnData);
    }

    public function testSetRecipientEmail()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setRecipientEmail('dummymail@example.com');
        self::assertSame('dummymail@example.com', $requestData->getRecipientEmail());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetRecipientEmailNull()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setRecipientEmail(null);
        self::assertNull($requestData->getRecipientEmail());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetSenderEmail()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setSenderEmail('dummymail@example.com');
        self::assertSame('dummymail@example.com', $requestData->getSenderEmail());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetSenderEmailNull()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setSenderEmail(null);
        self::assertNull($requestData->getSenderEmail());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetFixTextNull()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setFixText(null, 'Meho');
        self::assertNull($requestData->getPreviousGeneratedEmail());
        self::assertSame('Meho', $requestData->getFixText());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetFixTextStr()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setFixText('MyFixedText', 'Meho');
        self::assertSame('MyFixedText', $requestData->getPreviousGeneratedEmail());
        self::assertSame('Meho', $requestData->getFixText());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetPreviousConversationNull()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setPreviousConversation(null);
        self::assertNull($requestData->getPreviousConversation());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetPreviousConversationStr()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setPreviousConversation('MyPreviousConversation');
        self::assertSame('MyPreviousConversation', $requestData->getPreviousConversation());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testGetSenderName()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnData = $requestData->getSenderName();
        self::assertSame('Muhi', $returnData);
    }

    public function testGetSenderEmailNull()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnData = $requestData->getSenderEmail();
        self::assertNull($requestData->getSenderEmail());
    }

    public function testGetSenderEmailStr()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $requestData->setSenderEmail('dummymail@example.com');
        $returnData = $requestData->getSenderEmail();
        self::assertSame('dummymail@example.com', $requestData->getSenderEmail());
    }

    public function testGetStyle()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnData = $requestData->getStyle();
        self::assertSame('casual', $returnData);
    }

    public function testGetLength()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'Instruction');
        $returnData = $requestData->getLength();
        self::assertSame('medium', $returnData);
    }

    public function testGetCreativity()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnData = $requestData->getCreativity();
        self::assertSame('medium', $returnData);
    }

    public function testGetLanguage()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnData = $requestData->getLanguage();
        self::assertSame('Bosnian', $returnData);
    }

    public function testGetInstruction()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnData = $requestData->getInstruction();
        self::assertSame('TestInstruction', $returnData);
    }

    public function testGetFixTestNull()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        self::assertNull($requestData->getFixText());
    }

    public function testGetFixTestStr()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $requestData->setFixText('dummymail', 'myFixText');
        self::assertSame('myFixText', $requestData->getFixText());
    }

    public function testGetPreviousGeneratedEmailNull()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        self::assertNull($requestData->getPreviousGeneratedEmail());
    }

    public function testGetPreviousGeneratedEmailStr()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $requestData->setFixText('dummymail', 'myPreviousGeneratedEmail');
        self::assertSame('dummymail', $requestData->getPreviousGeneratedEmail());
    }

    public function testGetPreviousConversationNull()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        self::assertNull($requestData->getPreviousConversation());
    }

    public function testGetPreviousConversationStr()
    {
        $this->settingsSetters();
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $requestData->setPreviousConversation('MyPreviousConversation');
        self::assertSame('MyPreviousConversation', $requestData->getPreviousConversation());
    }
}
