<?php

namespace HercegDoo\AIComposePlugin\Utilities;

/**
 * Rate Limiter para prevenir abuso e ataques de DoS
 */
class RateLimiter
{
    /**
     * Limites padrão por tipo de requisição
     */
    private const DEFAULT_LIMITS = [
        'ai_generation' => [
            'requests' => 10,    // 10 requisições
            'window' => 60,       // por minuto
            'block_duration' => 300 // 5 minutos de bloqueio
        ],
        'instruction_save' => [
            'requests' => 20,    // 20 requisições
            'window' => 60,       // por minuto
            'block_duration' => 120 // 2 minutos de bloqueio
        ],
        'general' => [
            'requests' => 100,    // 100 requisições
            'window' => 60,       // por minuto
            'block_duration' => 60 // 1 minuto de bloqueio
        ]
    ];

    /**
     * Armazenamento de tentativas (em produção, usar Redis/Memcached)
     */
    private static array $attempts = [];
    private static array $blocks = [];

    /**
     * Verifica se a requisição está permitida
     */
    public static function isAllowed(string $identifier, string $type = 'general'): array
    {
        $limits = self::DEFAULT_LIMITS[$type] ?? self::DEFAULT_LIMITS['general'];
        $now = time();
        $windowStart = $now - $limits['window'];

        // Verificar se está bloqueado
        if (isset(self::$blocks[$identifier])) {
            if (self::$blocks[$identifier] > $now) {
                return [
                    'allowed' => false,
                    'reason' => 'blocked',
                    'retry_after' => self::$blocks[$identifier] - $now,
                    'remaining' => 0,
                    'limit' => $limits['requests'],
                    'reset_time' => self::$blocks[$identifier]
                ];
            } else {
                // Remover bloqueio expirado
                unset(self::$blocks[$identifier]);
            }
        }

        // Limpar tentativas antigas
        if (!isset(self::$attempts[$identifier])) {
            self::$attempts[$identifier] = [];
        }

        self::$attempts[$identifier] = array_filter(
            self::$attempts[$identifier],
            fn($timestamp) => $timestamp > $windowStart
        );

        // Verificar limite
        $currentAttempts = count(self::$attempts[$identifier]);
        
        if ($currentAttempts >= $limits['requests']) {
            // Bloquear temporariamente
            self::$blocks[$identifier] = $now + $limits['block_duration'];
            
            return [
                'allowed' => false,
                'reason' => 'limit_exceeded',
                'retry_after' => $limits['block_duration'],
                'remaining' => 0,
                'limit' => $limits['requests'],
                'reset_time' => $now + $limits['window']
            ];
        }

        // Adicionar tentativa atual
        self::$attempts[$identifier][] = $now;

        return [
            'allowed' => true,
            'reason' => 'ok',
            'retry_after' => 0,
            'remaining' => $limits['requests'] - $currentAttempts - 1,
            'limit' => $limits['requests'],
            'reset_time' => $windowStart + $limits['window']
        ];
    }

    /**
     * Registra uma tentativa de requisição
     */
    public static function recordAttempt(string $identifier, string $type = 'general'): void
    {
        $limits = self::DEFAULT_LIMITS[$type] ?? self::DEFAULT_LIMITS['general'];
        $now = time();

        if (!isset(self::$attempts[$identifier])) {
            self::$attempts[$identifier] = [];
        }

        self::$attempts[$identifier][] = $now;

        // Manter apenas tentativas na janela de tempo
        $windowStart = $now - $limits['window'];
        self::$attempts[$identifier] = array_filter(
            self::$attempts[$identifier],
            fn($timestamp) => $timestamp > $windowStart
        );
    }

    /**
     * Bloqueia manualmente um identificador
     */
    public static function block(string $identifier, int $duration = 300): void
    {
        self::$blocks[$identifier] = time() + $duration;
    }

    /**
     * Verifica se está bloqueado
     */
    public static function isBlocked(string $identifier): bool
    {
        if (!isset(self::$blocks[$identifier])) {
            return false;
        }

        if (self::$blocks[$identifier] <= time()) {
            unset(self::$blocks[$identifier]);
            return false;
        }

        return true;
    }

    /**
     * Remove bloqueio de um identificador
     */
    public static function unblock(string $identifier): void
    {
        unset(self::$blocks[$identifier]);
    }

    /**
     * Limpa todas as tentativas e bloqueios
     */
    public static function clear(): void
    {
        self::$attempts = [];
        self::$blocks = [];
    }

    /**
     * Obtém estatísticas de uso
     */
    public static function getStats(string $identifier): array
    {
        $now = time();
        $windowStart = $now - 60; // Último minuto

        $attempts = self::$attempts[$identifier] ?? [];
        $recentAttempts = array_filter($attempts, fn($timestamp) => $timestamp > $windowStart);

        return [
            'total_attempts' => count($attempts),
            'recent_attempts' => count($recentAttempts),
            'is_blocked' => self::isBlocked($identifier),
            'block_expires' => self::$blocks[$identifier] ?? null,
            'first_attempt' => !empty($attempts) ? min($attempts) : null,
            'last_attempt' => !empty($attempts) ? max($attempts) : null
        ];
    }

    /**
     * Gera identificador único para o usuário
     */
    public static function generateIdentifier(): string
    {
        // Tentar obter IP real
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
              $_SERVER['HTTP_X_REAL_IP'] ?? 
              $_SERVER['REMOTE_ADDR'] ?? 
              'unknown';

        // Limpar IP (remover porta se existir)
        $ip = preg_replace('/:\d+$/', '', $ip);
        
        // Adicionar user agent para maior especificidade
        $userAgent = substr(md5($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 8);
        
        return hash('sha256', $ip . $userAgent);
    }

    /**
     * Obtém cabeçalhos de rate limit para resposta HTTP
     */
    public static function getRateLimitHeaders(array $rateLimitResult): array
    {
        return [
            'X-RateLimit-Limit' => $rateLimitResult['limit'],
            'X-RateLimit-Remaining' => $rateLimitResult['remaining'],
            'X-RateLimit-Reset' => $rateLimitResult['reset_time'],
            'X-RateLimit-Retry-After' => $rateLimitResult['retry_after']
        ];
    }
}
