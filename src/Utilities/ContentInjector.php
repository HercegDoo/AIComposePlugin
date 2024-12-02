<?php

namespace HercegDoo\AIComposePlugin\Utilities;

use Rct567\DomQuery\DomQuery;

final class ContentInjector
{
    private static ?ContentInjector $instance = null;
    use ContentTrait;

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

    /**
     * @param array<string, mixed> $baseHTML
     *
     * @return array<string, mixed>
     */
    public function add_buttons(string $buttonsToAdd, string $containerId, array $baseHTML): array
    {
        if (isset($baseHTML['content']) && \is_string($baseHTML['content']) && !str_contains($baseHTML['content'], $buttonsToAdd) && str_contains($baseHTML['content'], 'class="' . $containerId . '"')) {
            $baseHTML['content'] = preg_replace_callback(
                '/(<div\s+class="' . $containerId . '".*?>)(.*?)(<\/div>)/s',
                static function ($matches) use ($buttonsToAdd) {
                    // Pronađi postojeća dugmad unutar formbuttons div-a
                    preg_match_all('/<button.*?>.*?<\/button>/', $matches[2], $buttons);

                    // Dodaj nova dugmad između postojećih ili ako nema dugmadi, dodaj ih na početak
                    $middle_buttons = $buttons[0]
                        ? $buttons[0][0] . $buttonsToAdd . implode('', \array_slice($buttons[0], 1))
                        : $buttonsToAdd;

                    return $matches[1] . $middle_buttons . $matches[3];
                },
                $baseHTML['content']
            );
        }

        return $baseHTML;
    }

    /**
     * @return array<int,string>>
     */
    public function findId(string $html): array
    {
        preg_match_all('/id=["\']([^"\']+)["\']/', $html, $matches);

        return $matches[1];
    }

    public function getParsedHtml(string $fileName): string
    {
        $htmlFile = '';
        $htmlFilePath = __DIR__ . '/../../skins/elastic/templates/' . $fileName . '.html';
        error_log('Ime Putanje : ' . print_r($htmlFilePath, true));
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
        $baseHTML['content'] = '<!DOCTYPE html>' . $baseHTML['content'];

        $hash = md5($insertContent);
        if (\in_array($hash, self::$doneContent)) {
            return $baseHTML;
        }

        $parsedHtmlContent = $this->getParsedHtml($insertContent);

        $html = $baseHTML['content'];

        $dom = new DomQuery($html);

        $targetElement = $dom->find($selector);

        if ($targetElement->count() === 0) {
            error_log('AICompose plugin: error cant find selector in templete: ' . $selector);

            return $baseHTML;
        }

        $position = $position === 'after' ? 'after' : 'before';

        $targetElement->{$position}($parsedHtmlContent);

        $baseHTML['content'] = $dom->getOuterHtml();

        self::$doneContent[] = $hash;

        return $baseHTML;
    }
}
