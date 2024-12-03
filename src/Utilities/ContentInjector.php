<?php

namespace HercegDoo\AIComposePlugin\Utilities;

use Rct567\DomQuery\DomQuery;

final class ContentInjector
{
    use TranslationTrait;
    private static ?ContentInjector $instance = null;

    /**
     * @var string[]
     */
    private static array $doneContent = [];

    public static function getContentInjector(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param array<string, mixed> $baseHTML
     *
     * @return array<string, mixed>
     */
    public function insertContentAboveElement(array $baseHTML, string $contentToInsert, string $selector): array
    {
        return $this->insertContent($baseHTML, $contentToInsert, $selector, 'before');
    }

    /**
     * @param array<string, mixed> $baseHTML
     *
     * @return array<string, mixed>
     */
    public function insertContentAfterElement(array $baseHTML, string $contentToInsert, string $selector): array
    {
        return $this->insertContent($baseHTML, $contentToInsert, $selector, 'after');
    }

    public function getParsedHtml(string $fileName): string
    {
        $htmlFile = '';
        $htmlFilePath = __DIR__ . '/../../skins/elastic/templates/' . $fileName . '.html';
        if (file_exists($htmlFilePath)) {
            $htmlFile = file_get_contents($htmlFilePath);
        }

        return \rcmail::get_instance()->output->just_parse($htmlFile);
    }

    /**
     * @param array<string, mixed> $baseHTML
     *
     * @return array<string, mixed>
     */
    private function insertContent(array $baseHTML, string $insertContent, string $selector, string $position): array
    {
        $hash = md5($insertContent);
        if (\in_array($hash, self::$doneContent)) {
            return $baseHTML;
        }

        $parsedHtmlContent = $this->getParsedHtml($insertContent);

        $html = $baseHTML['content'];

        $firstLine = '';

        $dom = new DomQuery($html);

        $targetElement = $dom->find($selector);

        if ($targetElement->count() === 0) {
            error_log('AICompose plugin: error cant find selector in templete: ' . $selector);

            return $baseHTML;
        }

        $position = $position === 'after' ? 'after' : 'before';

        $targetElement->{$position}($parsedHtmlContent);

        if (!empty($doctype = $this->validateDoctype(\is_string($html) ? $html : '')) && empty($this->validateDoctype($dom->getOuterHtml()))) {
            $firstLine = $doctype . \PHP_EOL;
        }

        $baseHTML['content'] = $firstLine . $dom->getOuterHtml();
        self::$doneContent[] = $hash;

        return $baseHTML;
    }

    private function validateDoctype(string $html, int $limit = 20): string
    {
        $lines = explode(\PHP_EOL, $html, $limit);
        $firstLine = '';

        foreach ($lines as $line) {
            if (!empty($line) && preg_match('/<![dD][oO][cC][tT][yY][pP][eE][^>]*>/', $line, $matches)) {
                $firstLine = $matches[0];
                break;
            }
        }

        return $firstLine;
    }
}
