<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Entity;

use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use HercegDoo\AIComposePlugin\AIEmailService\Settings;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class RequestDataTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PHPUNIT_RUNNING')) {
            \define('PHPUNIT_RUNNING', true); // Definišite konstantu samo ako nije već definisana
        }

        Settings::setStyles(['professional', 'default' => 'casual', 'assertive', 'enthusiastic', 'funny', 'informational', 'persuasive']);

        Settings::setLengths(['short', 'default' => 'medium', 'long']);

        Settings::setLanguages(['default' => 'Bosnian', 'Croatian', 'German', 'Dutch']);

        Settings::setDefaultMaxTokens(2000);
        Settings::setProviderConfig(['apiKey' => 'test-api-key', 'model' => 'model-test']);
    }

    public function testReturnInstance()
    {
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
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstrukcija');
        $returnedValue = $requestData->setRecipientName('Muhi');

        self::assertSame('Muhi', $requestData->getRecipientName());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetSenderName()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'TestStil');
        $returnedValue = $requestData->setSenderName('Jaha');
        self::assertSame('Jaha', $requestData->getSenderName());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetInstruction()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setInstruction('MyInstructions');
        self::assertSame('MyInstructions', $requestData->getInstruction());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetStyle()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction', 'TestStyle');
        $returnedValue = $requestData->setStyle('MyStyle');
        self::assertSame('MyStyle', $requestData->getStyle());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetLength()
    {
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
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setLanguage('MyLanguage');
        self::assertSame('MyLanguage', $requestData->getLanguage());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testGetRecipientName()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnData = $requestData->getRecipientName();
        self::assertSame('Meho', $returnData);
        self::assertIsString($returnData);
    }

    public function testSetRecipientEmail()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setRecipientEmail('dummymail@example.com');
        self::assertSame('dummymail@example.com', $requestData->getRecipientEmail());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetRecipientEmailNull()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setRecipientEmail(null);
        self::assertNull($requestData->getRecipientEmail());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetSenderEmail()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setSenderEmail('dummymail@example.com');
        self::assertSame('dummymail@example.com', $requestData->getSenderEmail());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetSenderEmailNull()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setSenderEmail(null);
        self::assertNull($requestData->getSenderEmail());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetFixTextNull()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setFixText(null, 'Meho');
        self::assertNull($requestData->getPreviousGeneratedEmail());
        self::assertSame('Meho', $requestData->getFixText());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetFixTextStr()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setFixText('MyFixedText', 'Meho');
        self::assertSame('MyFixedText', $requestData->getPreviousGeneratedEmail());
        self::assertSame('Meho', $requestData->getFixText());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetPreviousConversationNull()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setPreviousConversation(null);
        self::assertNull($requestData->getPreviousConversation());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testSetPreviousConversationStr()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnedValue = $requestData->setPreviousConversation('MyPreviousConversation');
        self::assertSame('MyPreviousConversation', $requestData->getPreviousConversation());
        self::assertInstanceOf(RequestData::class, $returnedValue);
    }

    public function testGetSenderName()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnData = $requestData->getSenderName();
        self::assertSame('Muhi', $returnData);
    }

    public function testGetSenderEmailNull()
    {
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
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnData = $requestData->getStyle();
        self::assertSame('casual', $returnData);
    }

    public function testGetLength()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'Instruction');
        $returnData = $requestData->getLength();
        self::assertSame('medium', $returnData);
    }

    public function testGetCreativity(): void
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnData = $requestData->getCreativity();
        self::assertSame('medium', $returnData);
    }

    public function testGetLanguage()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnData = $requestData->getLanguage();
        self::assertSame('Bosnian', $returnData);
    }

    public function testGetInstruction()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $returnData = $requestData->getInstruction();
        self::assertSame('TestInstruction', $returnData);
    }

    public function testGetFixTestNull()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        self::assertNull($requestData->getFixText());
    }

    public function testGetFixTestStr()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $requestData->setFixText('dummymail', 'myFixText');
        self::assertSame('myFixText', $requestData->getFixText());
    }

    public function testGetPreviousGeneratedEmailNull()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        self::assertNull($requestData->getPreviousGeneratedEmail());
    }

    public function testGetPreviousGeneratedEmailStr()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $requestData->setFixText('dummymail', 'myPreviousGeneratedEmail');
        self::assertSame('dummymail', $requestData->getPreviousGeneratedEmail());
    }

    public function testGetPreviousConversationNull()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        self::assertNull($requestData->getPreviousConversation());
    }

    public function testGetPreviousConversationStr()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstruction');
        $requestData->setPreviousConversation('MyPreviousConversation');
        self::assertSame('MyPreviousConversation', $requestData->getPreviousConversation());
    }

    public function testSetPreviousGeneratedEmail()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'jabuka');
        $requestData->setPreviousGeneratedEmail('Jucerasnji mail');
        self::assertSame('Jucerasnji mail', $requestData->getPreviousGeneratedEmail());
    }

    public function testSetPreviousGeneratedEmailReturnType()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'jabuka');
        $return = $requestData->setPreviousGeneratedEmail('Jucerasnji mail');
        self::assertInstanceOf(RequestData::class, $return);
    }
}
