<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Debug;

use HercegDoo\AIComposePlugin\AIEmailService\Prompt\EmailPrompt;

final class RequestLogger
{
    private bool $enabled;
    private ?string $userId;

    /** @var null|callable(string): void */
    private $writer;

    /** @param null|callable(string): void $writer */
    public function __construct(bool $enabled = false, ?string $userId = null, ?callable $writer = null)
    {
        $this->enabled = $enabled;
        $this->userId = $userId;
        $this->writer = $writer;
    }

    /**
     * @param array<string, float|int|string> $options
     *
     * @return null|array{id: string, started: float}
     */
    public function begin(string $provider, string $model, EmailPrompt $prompt, array $options = []): ?array
    {
        if (!$this->enabled) {
            return null;
        }

        try {
            $requestId = bin2hex(random_bytes(8));
        } catch (\Throwable $e) {
            return null;
        }

        $trace = ['id' => $requestId, 'started' => microtime(true)];
        $this->write([
            'event' => 'request',
            'request_id' => $trace['id'],
            'user_id' => $this->userId,
            'provider' => $provider,
            'operation' => $prompt->getPurpose(),
            'model' => $model,
            'options' => $options,
            'prompt' => [
                'system' => $prompt->getSystemInstruction(),
                'user' => $prompt->getUserInstruction(),
            ],
        ]);

        return $trace;
    }

    /**
     * @param null|array{id: string, started: float} $trace
     * @param array<string, int|string>              $details
     * @param array<string, int>                     $usage
     */
    public function finish(?array $trace, string $status, array $details = [], array $usage = []): void
    {
        if ($trace === null || !$this->enabled) {
            return;
        }

        $this->write([
            'event' => 'result',
            'request_id' => $trace['id'],
            'user_id' => $this->userId,
            'status' => $status,
            'duration_ms' => max(0, (int) round((microtime(true) - $trace['started']) * 1000)),
            'details' => $details,
            'usage' => $usage,
        ]);
    }

    /** @param array<string, mixed> $record */
    private function write(array $record): void
    {
        try {
            $line = json_encode($record, \JSON_UNESCAPED_UNICODE | \JSON_INVALID_UTF8_SUBSTITUTE | \JSON_THROW_ON_ERROR);
            if ($this->writer !== null) {
                ($this->writer)($line);
            } else {
                \rcube::write_log('aicomposeplugin_ai', $line);
            }
        } catch (\Throwable $e) {
            // Debug logging must not prevent email generation or summaries.
        }
    }
}
