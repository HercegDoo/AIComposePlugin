<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Style;

final class SentStyleSampler
{
    private const MAX_EXAMPLES = 3;
    private const MAX_MATCH_CANDIDATES = 20;
    private const MAX_RECENT_CANDIDATES = 40;

    /** @var \Closure(string, string): \rcube_message */
    private \Closure $messageLoader;

    public function __construct(?callable $messageLoader = null)
    {
        $this->messageLoader = $messageLoader !== null
            ? \Closure::fromCallable($messageLoader)
            : static function (string $uid, string $folder): \rcube_message {
                return new \rcube_message($uid, $folder);
            };
    }

    /**
     * @return array<int, array{body: string, sameRecipient: bool}>
     */
    public function collect(\rcube_storage $storage, string $folder, string $senderEmail, ?string $recipientEmails): array
    {
        $senderEmail = strtolower(trim($senderEmail));
        if ($folder === '' || !filter_var($senderEmail, \FILTER_VALIDATE_EMAIL)) {
            return [];
        }

        $recipients = array_values(array_filter(array_map('trim', explode(',', (string) $recipientEmails)), static function (string $email): bool {
            return (bool) filter_var($email, \FILTER_VALIDATE_EMAIL);
        }));
        $recipients = array_map('strtolower', $recipients);
        $originalFolder = $storage->get_folder();
        $originalSearch = $storage->get_search_set();
        $examples = [];
        $seen = [];

        try {
            // Search the same correspondent first, even when their messages are older.
            foreach (\array_slice($recipients, 0, 2) as $recipient) {
                if (\count($examples) >= self::MAX_EXAMPLES) {
                    break;
                }

                $quoted = addcslashes($recipient, '\"');
                try {
                    $result = $storage->search_once($folder, 'OR TO "' . $quoted . '" CC "' . $quoted . '"');
                    $uids = array_reverse(\array_slice($result->get(), -self::MAX_MATCH_CANDIDATES));
                    $this->appendExamples($examples, $seen, $storage, $folder, $senderEmail, $recipients, $uids, true);
                } catch (\Throwable $e) {
                    // Some IMAP servers reject search criteria; recent Sent mail is still usable.
                    break;
                }
            }

            if (\count($examples) < self::MAX_EXAMPLES) {
                // An active Roundcube search must not restrict the Sent index.
                $storage->set_search_set([]);
                $index = $storage instanceof \rcube_imap
                    ? $storage->index($folder, 'date', 'DESC', true)
                    : $storage->index($folder, 'date', 'DESC');
                $uids = \array_slice($index->get(), 0, self::MAX_RECENT_CANDIDATES);
                $this->appendExamples($examples, $seen, $storage, $folder, $senderEmail, $recipients, $uids, false);
            }
        } finally {
            $storage->set_search_set($originalSearch);
            $storage->set_folder($originalFolder);
        }

        return $examples;
    }

    /**
     * @param array<int, array{body: string, sameRecipient: bool}> $examples
     * @param array<string, bool>                                  $seen
     * @param string[]                                             $recipients
     * @param array<int, int|string>                               $uids
     */
    private function appendExamples(array &$examples, array &$seen, \rcube_storage $storage, string $folder, string $senderEmail, array $recipients, array $uids, bool $requireMatch): void
    {
        $extractor = new SentStyleTextExtractor();
        foreach ($uids as $uid) {
            if (\count($examples) >= self::MAX_EXAMPLES) {
                break;
            }
            $uid = (string) $uid;
            if (isset($seen[$uid])) {
                continue;
            }
            $seen[$uid] = true;
            /** @var false|\rcube_message_header $headers */
            $headers = $storage->get_message_headers((int) $uid, $folder);
            if (!$headers || !$this->containsAddress((string) $headers->from, [$senderEmail])) {
                continue;
            }

            $sameRecipient = $this->containsAddress((string) $headers->to, $recipients)
                || $this->containsAddress((string) $headers->cc, $recipients);
            if ($requireMatch && !$sameRecipient) {
                continue;
            }

            try {
                $message = ($this->messageLoader)($uid, $folder);
                $body = $extractor->extract($message);
            } catch (\Throwable $e) {
                // A broken message should not prevent using other Sent examples.
                continue;
            }
            if (\count(preg_split('/\s+/u', $body) ?: []) < 8) {
                continue;
            }

            $examples[] = ['body' => $body, 'sameRecipient' => $sameRecipient];
        }
    }

    /**
     * @param string[] $addresses
     */
    private function containsAddress(string $header, array $addresses): bool
    {
        if ($addresses === []) {
            return false;
        }
        foreach (\rcube_mime::decode_address_list($header, null, true, 'UTF-8', true) as $address) {
            if (\in_array(strtolower($address), $addresses, true)) {
                return true;
            }
        }

        return false;
    }
}
