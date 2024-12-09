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
    public function insertContentAboveElement(array $baseHTML, string $contentToInsertSelector, string $elementId, string $selectorType = 'id'): array
    {

        $selectorType = $selectorType === 'class' ? 'class' : 'id';

        $contentToInsert = $this->getParsedHtml($contentToInsertSelector);

        $hash = md5($contentToInsert);
        if (\in_array($hash, self::$doneContent)) {
            return $baseHTML;
        }

        if (isset($baseHTML['content']) && \is_string($baseHTML['content']) && !str_contains($baseHTML['content'], $contentToInsert)) {
            $pattern = '/(<div\s+'.$selectorType.'="' . $elementId . '".*?>)/';

            if (str_contains($baseHTML['content'], ''.$selectorType.'="' . $elementId . '"')) {
                $baseHTML['content'] = preg_replace($pattern, $contentToInsert . '$1', $baseHTML['content']);
            }
        }
        self::$doneContent[] = $hash;
        return $baseHTML;
    }


}
