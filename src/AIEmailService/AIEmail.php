<?php

namespace HercegDoo\AIComposePlugin\AIEmailService;

use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\Respond;
use HercegDoo\AIComposePlugin\AIEmailService\Exceptions\ProviderException;
use HercegDoo\AIComposePlugin\AIEmailService\Prompt\EmailPromptBuilder;
use HercegDoo\AIComposePlugin\AIEmailService\Prompt\PromptBuilderInterface;
use HercegDoo\AIComposePlugin\AIEmailService\Prompt\SubjectPromptBuilder;

final class AIEmail
{
    /**
     * @throws ProviderException
     */
    public static function generate(RequestData $requestData, ?PromptBuilderInterface $promptBuilder = null): Respond
    {
        $provider = Settings::getProvider();

        try {
            $prompt = ($promptBuilder ?? new EmailPromptBuilder())->build($requestData);
            $respond = $provider->generateEmail($requestData, $prompt);
        } catch (\Throwable $e) {
            if ($e instanceof ProviderException) {
                throw $e;
            }

            throw new ProviderException('General: ' . $e->getMessage());
        }

        return $respond;
    }

    /**
     * @throws ProviderException
     */
    public static function generateSubject(RequestData $requestData, string $draft, ?string $previousSubject = null): string
    {
        $response = self::generate($requestData, new SubjectPromptBuilder($draft, $previousSubject));
        $subject = self::normalizeSubject($response->getBody());

        if ($subject === '') {
            throw new ProviderException('No subject content found');
        }

        return $subject;
    }

    public static function normalizeSubject(string $subject): string
    {
        $subject = trim(html_entity_decode((string) preg_replace('/<[^>]*>/u', ' ', $subject), \ENT_QUOTES | \ENT_HTML5, 'UTF-8'));
        $lines = preg_split('/\R/u', $subject);
        $subject = trim($lines[0] ?? '', " \t\n\r\0\x0B\"'`*#");
        $subject = (string) preg_replace('/^\s*(?:subject|betreff|naslov|predmet)\s*:\s*/iu', '', $subject);
        $subject = trim($subject, " \t\n\r\0\x0B\"'`*#");
        $subject = trim((string) preg_replace('/[\x00-\x1F\x7F]/u', ' ', $subject));

        preg_match('/^.{0,160}/us', $subject, $matches);

        return trim($matches[0] ?? '');
    }
}
