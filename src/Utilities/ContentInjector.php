<?php

namespace HercegDoo\AIComposePlugin\Utilities;

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

    private function replaceText(string $original, string $search, string $replace, string $mode = 'replace') : string
    {
        switch (strtolower($mode)) {
            case 'prepend':
                return str_replace($search, $replace . $search, $original);

            case 'append':
                return str_replace($search, $search . $replace, $original);

            case 'replace':
            default:
                return str_replace($search, $replace, $original);
        }
    }

    /**
     * @param array<string, mixed> $baseHTML
     *
     * @return array<string, mixed>
     */
    public function insertContent(array $baseHTML, string $id, string $contentKey, string $position = 'append'): array
    {
        if (isset($baseHTML['content']) && \is_string($baseHTML['content'])) {
            $hash = md5($contentKey);
            if (\in_array($hash, self::$doneContent)) {
                return $baseHTML;
            }

            $targetElement = '';
            $pattern = '/<div\b[^>]*\bid\s*=\s*["\']' . preg_quote($id, '/') . '["\'][^>]*>/i';
            if (preg_match($pattern, $baseHTML['content'], $matches, \PREG_OFFSET_CAPTURE)) {
                $startPos = $matches[0][1];
                $matchedLength = \strlen($matches[0][0]);
                $currentPos = (int) $startPos + (int) $matchedLength;

                $openDivs = 1;
                $patternDiv = '/<\/?div\b[^>]*>/i';

                while ($openDivs > 0 && preg_match($patternDiv, $baseHTML['content'], $match, \PREG_OFFSET_CAPTURE, $currentPos)) {
                    $tag = strtolower($match[0][0]);
                    if (str_starts_with($tag, '</div')) {
                        --$openDivs;
                    } else {
                        ++$openDivs;
                    }
                    $currentPos = (int) $match[0][1] + \strlen($match[0][0]);
                }

                if ($openDivs == 0) {
                    $targetElement = substr($baseHTML['content'], $startPos, $currentPos - $startPos);
                } else {
                    return $baseHTML;
                }
            } else {
                return $baseHTML;
            }

            $baseHTML['content'] = $this->replaceText($baseHTML['content'], $targetElement, $this->getParsedHtml($contentKey), $position);
            self::$doneContent[] = $hash;
        }

        return $baseHTML;
    }
}
