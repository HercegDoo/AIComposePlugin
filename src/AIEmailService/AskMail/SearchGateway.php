<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\AskMail;

final class SearchGateway
{
    private string $solrUrl;
    private string $ollamaUrl;
    private string $model;
    private string $username;
    private string $password;

    /** @param array<string, mixed> $config */
    public function __construct(array $config)
    {
        $this->solrUrl = rtrim(self::stringOption($config, 'solrUrl'), '/');
        $this->ollamaUrl = rtrim(self::stringOption($config, 'ollamaUrl'), '/');
        $this->model = self::stringOption($config, 'embeddingModel', 'qwen3-embedding:0.6b');
        $this->username = self::stringOption($config, 'solrUser');
        $this->password = self::stringOption($config, 'solrPassword');
        if (!self::validUrl($this->solrUrl) || !self::validUrl($this->ollamaUrl)
            || $this->model === '') {
            throw new \InvalidArgumentException('Ask Mail search is not configured');
        }
    }

    /**
     * @param array<int, array<string, mixed>> $fullTextHits
     *
     * @return array{hits: array<int, array<string, mixed>>, mode: string}
     */
    public function search(int $owner, string $question, array $fullTextHits = []): array
    {
        $ranked = [];
        self::mergeRanks($ranked, $fullTextHits, 1.0);
        $mode = $fullTextHits === [] ? 'empty' : 'lexical';

        try {
            $vector = $this->embed($question);
            $semantic = $this->select([
                'q' => '{!knn f=vector topK=40}' . json_encode($vector, \JSON_THROW_ON_ERROR),
                'fq' => 'kind_s:chunk AND owner_i:' . $owner,
                'rows' => '40',
                'fl' => 'id,mailbox_s,uid_l,uidvalidity_l,part_s,offset_i',
                'wt' => 'json',
            ]);
            self::mergeRanks($ranked, $semantic, 1.1);
            $mode = $fullTextHits === [] ? 'semantic' : 'hybrid';
        } catch (\Throwable $error) {
            // The user's indexed terms still give a useful answer if Ollama is offline.
        }

        uasort($ranked, static function (array $left, array $right): int {
            return $right['rank'] <=> $left['rank'];
        });

        return ['hits' => array_values(\array_slice($ranked, 0, 30)), 'mode' => $mode];
    }

    /** @return array{indexed: int, skipped: int, estimated: int, complete: bool, error: string} */
    public function progress(int $owner): array
    {
        $docs = $this->select([
            'q' => 'kind_s:state AND owner_i:' . $owner,
            'rows' => '1',
            'fl' => 'indexed_i,skipped_i,estimated_i,complete_b,error_s',
            'wt' => 'json',
        ]);
        $state = $docs[0] ?? [];

        return [
            'indexed' => self::integerValue($state['indexed_i'] ?? null),
            'skipped' => self::integerValue($state['skipped_i'] ?? null),
            'estimated' => self::integerValue($state['estimated_i'] ?? null),
            'complete' => ($state['complete_b'] ?? false) === true,
            'error' => \is_string($state['error_s'] ?? null) ? $state['error_s'] : '',
        ];
    }

    /** @return array<int, float> */
    private function embed(string $question): array
    {
        $result = $this->request($this->ollamaUrl . '/api/embed', [
            'model' => $this->model,
            'input' => $question,
            'truncate' => false,
        ]);
        $vectors = $result['embeddings'] ?? null;
        $vector = \is_array($vectors) ? ($vectors[0] ?? null) : null;
        if (!\is_array($vector) || \count($vector) !== 1024) {
            throw new \RuntimeException('Embedding dimension mismatch');
        }
        foreach ($vector as $item) {
            if (!\is_int($item) && !\is_float($item)) {
                throw new \RuntimeException('Invalid embedding');
            }
        }

        return $vector;
    }

    /**
     * @param array<string, string> $params
     *
     * @return array<int, array<string, mixed>>
     */
    private function select(array $params): array
    {
        $result = $this->request(
            $this->solrUrl . '/select',
            http_build_query($params, '', '&', \PHP_QUERY_RFC3986),
            true
        );
        $response = $result['response'] ?? null;
        $docs = \is_array($response) ? ($response['docs'] ?? null) : null;
        if (!\is_array($docs)) {
            throw new \RuntimeException('Invalid Solr response');
        }

        return $docs;
    }

    /**
     * @param array<string, mixed>|string $body
     *
     * @return array<string, mixed>
     */
    private function request(string $url, $body, bool $form = false): array
    {
        $handle = curl_init($url);
        if ($handle === false) {
            throw new \RuntimeException('Could not initialize HTTP request');
        }
        $data = $form ? $body : json_encode($body, \JSON_THROW_ON_ERROR);
        $options = [
            \CURLOPT_POST => true,
            \CURLOPT_POSTFIELDS => $data,
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_CONNECTTIMEOUT => 5,
            \CURLOPT_TIMEOUT => 20,
            \CURLOPT_SSL_VERIFYPEER => true,
            \CURLOPT_SSL_VERIFYHOST => 2,
            \CURLOPT_HTTPHEADER => ['Content-Type: ' . ($form ? 'application/x-www-form-urlencoded' : 'application/json')],
        ];
        if ($this->username !== '' && $url === $this->solrUrl . '/select') {
            $options[\CURLOPT_USERPWD] = $this->username . ':' . $this->password;
            $options[\CURLOPT_HTTPAUTH] = \CURLAUTH_BASIC;
        }
        curl_setopt_array($handle, $options);
        try {
            $response = curl_exec($handle);
            $status = curl_getinfo($handle, \CURLINFO_HTTP_CODE);
        } finally {
            curl_close($handle);
        }
        if (!\is_string($response) || $status < 200 || $status >= 300) {
            throw new \RuntimeException('Search backend unavailable');
        }
        $decoded = json_decode($response, true);
        if (!\is_array($decoded) || ($decoded['responseHeader']['status'] ?? 0) !== 0) {
            throw new \RuntimeException('Search backend returned invalid JSON');
        }

        return $decoded;
    }

    private static function validUrl(string $url): bool
    {
        return \in_array(parse_url($url, \PHP_URL_SCHEME), ['http', 'https'], true)
            && \is_string(parse_url($url, \PHP_URL_HOST));
    }

    /** @param array<string, mixed> $config */
    private static function stringOption(array $config, string $key, string $default = ''): string
    {
        return \is_string($config[$key] ?? null) ? $config[$key] : $default;
    }

    /** @phpstan-param mixed $value */
    private static function integerValue($value): int
    {
        return \is_int($value) || (\is_string($value) && ctype_digit($value)) ? (int) $value : 0;
    }

    /**
     * @param array<string, array<string, mixed>> $ranked
     * @param array<int, array<string, mixed>>    $docs
     */
    private static function mergeRanks(array &$ranked, array $docs, float $weight): void
    {
        foreach ($docs as $position => $doc) {
            $id = $doc['id'] ?? null;
            if (!\is_string($id) || !isset($doc['mailbox_s'], $doc['uid_l'], $doc['uidvalidity_l'], $doc['part_s'], $doc['offset_i'])) {
                continue;
            }
            if (!isset($ranked[$id])) {
                $ranked[$id] = $doc + ['rank' => 0.0];
            }
            $ranked[$id]['rank'] += $weight / (60 + $position + 1);
        }
    }
}
