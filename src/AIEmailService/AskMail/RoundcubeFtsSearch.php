<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\AskMail;

final class RoundcubeFtsSearch
{
    private \rcmail $rcmail;

    public function __construct(\rcmail $rcmail)
    {
        $this->rcmail = $rcmail;
    }

    /** @return array<int, array<string, mixed>> */
    public function search(string $question): array
    {
        $criteria = self::criteria($question);
        if ($criteria === '') {
            return [];
        }
        $command = preg_match('/[^\x00-\x7F]/', $criteria) ? 'CHARSET UTF-8 ' . $criteria : $criteria;
        $results = [];
        $started = microtime(true);
        try {
            $folders = $this->rcmail->storage->list_folders_subscribed();
        } catch (\Throwable $error) {
            return [];
        }
        foreach ($folders as $folder) {
            if (microtime(true) - $started > 8 || \count($results) >= 60) {
                break;
            }
            if (!\is_string($folder) || self::excluded($folder)) {
                continue;
            }
            try {
                $found = $this->rcmail->storage->search_once($folder, $command);
                if ($found->is_error() || $found->is_empty() || $found->count() > 10000) {
                    continue;
                }
                $data = $this->rcmail->storage->folder_data($folder);
                $validity = $data['UIDVALIDITY'] ?? null;
                if (!is_numeric($validity) || (int) $validity < 1) {
                    continue;
                }
                $uids = $found->get();
                foreach (array_reverse(\array_slice($uids, -3)) as $uid) {
                    if (!is_numeric($uid) || (int) $uid < 1) {
                        continue;
                    }
                    $results[] = [
                        'id' => 'fts:' . hash('sha256', $folder . "\0" . $uid),
                        'mailbox_s' => $folder,
                        'uid_l' => (int) $uid,
                        'uidvalidity_l' => (int) $validity,
                        'part_s' => '1',
                        'offset_i' => 0,
                        'fts' => true,
                    ];
                }
            } catch (\Throwable $error) {
                // Other folders and vector search remain available.
                continue;
            }
        }

        return \array_slice($results, 0, 40);
    }

    public static function criteria(string $question): string
    {
        preg_match_all('/[\pL\pN]{3,}/u', $question, $matches);
        $stopwords = ['what', 'when', 'where', 'which', 'does', 'did', 'have', 'with', 'about',
            'koji', 'koja', 'kada', 'gdje', 'koliko', 'imam', 'samo', 'bilo', 'poruka', 'mail'];
        $terms = [];
        foreach ($matches[0] as $term) {
            $lower = mb_strtolower($term, 'UTF-8');
            if (!\in_array($lower, $stopwords, true)) {
                $terms[$lower] = $term;
            }
        }
        uasort($terms, static function (string $left, string $right): int {
            return mb_strlen($right, 'UTF-8') <=> mb_strlen($left, 'UTF-8');
        });
        $terms = \array_slice(array_values($terms), 0, 2);
        if ($terms === []) {
            return '';
        }

        return implode(' ', array_map(static function (string $term): string {
            return 'TEXT "' . $term . '"';
        }, $terms));
    }

    private static function excluded(string $folder): bool
    {
        $parts = preg_split('/[\/.]/u', mb_strtolower($folder, 'UTF-8'));
        foreach ($parts ?: [] as $part) {
            if (\in_array($part, ['spam', 'junk', 'trash', 'drafts', 'skice', 'smece', 'smeće'], true)) {
                return true;
            }
        }

        return false;
    }
}
