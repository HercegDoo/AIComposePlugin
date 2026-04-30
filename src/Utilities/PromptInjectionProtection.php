<?php

namespace HercegDoo\AIComposePlugin\Utilities;

/**
 * Proteção contra Prompt Injection e manipulação de prompts
 */
class PromptInjectionProtection
{
    /**
     * Padrões de Prompt Injection detectados - BLACKLIST COMPLETA
     */
    private const INJECTION_PATTERNS = [
        // === TENTATIVAS DE ROLE/GROUP MANIPULATION ===
        '/ignore\s+(previous|all|any)\s+(instructions?|prompts?|commands?|rules?|guidelines?)/i',
        '/forget\s+(everything|all|previous|any|the)\s+(instructions?|prompts?|commands?|rules?)/i',
        '/disregard\s+(your\s+)?(programming|instructions?|prompts?|commands?|rules?|guidelines?)/i',
        '/override\s+(your\s+)?(programming|instructions?|prompts?|commands?|rules?|system)/i',
        '/bypass\s+(your\s+)?(restrictions?|limitations?|rules?|guidelines?|programming)/i',
        
        // === ROLE INJECTION ===
        '/act\s+as\s+(if\s+you\s+are\s+)?(a\s+)?(different|new)/i',
        '/pretend\s+(you\s+are)?/i',
        '/imagine\s+(you\s+are)?/i',
        '/from\s+now\s+on\s+(you\s+are)?/i',
        '/you\s+are\s+(now\s+)?(a\s+)?(different|new)/i',
        '/role\s*:\s*/i',
        '/system\s*:\s*/i',
        '/assistant\s*:\s*/i',
        '/user\s*:\s*/i',
        
        // === JAILBREAK ATTEMPTS ===
        '/(jailbreak|jail\s+break)/i',
        '/(dan|do\s+anything\s+now)/i',
        '/(hypothetical|imagine|pretend)\s+(you\s+)?(are|were)/i',
        '/as\s+(an\s+)?(ai|assistant|chatbot)/i',
        '/if\s+you\s+were\s+(a\s+)?(human|person)/i',
        
        // === INFORMATION EXTRACTION ===
        '/(what|tell|show|reveal|display|expose)\s+me\s+(about\s+)?your\s+/i',
        '/what\s+(are|is)\s+your\s+(instructions?|prompts?|commands?|rules?|guidelines?|programming|system)/i',
        '/reveal\s+(your\s+)?(instructions?|prompts?|commands?|system|programming)/i',
        '/show\s+me\s+(your\s+)?(prompt|instructions?|system)/i',
        '/how\s+(do|does|are|is)\s+you\s+/i',
        '/what\s+(can|do)\s+you\s+/i',
        
        // === CONTEXT SWITCHING ===
        '/\{\{.*?\}\}/',
        '/\[\[.*?\]\]/',
        '/<<.*?>>/',
        '/```.*?```/s',
        '/---.*?---/',
        '/===.*?===/',
        
        // === FORMAT INJECTION ===
        '/\b(class|function|if|else|for|while|switch|case|try|catch|throw)\s*\(/i',
        '/<\?(php|=)/i',
        '/javascript:/i',
        '/data:text\/html/i',
        '/vbscript:/i',
        '/<script[^>]*>/i',
        '/<iframe[^>]*>/i',
        '/<object[^>]*>/i',
        '/<embed[^>]*>/i',
        
        // === OUTPUT MANIPULATION ===
        '/respond\s+(only|just)\s+with/i',
        '/output\s+(only|just)\s+the/i',
        '/return\s+(only|just)\s+the/i',
        '/print\s+(only|just)\s+the/i',
        '/echo\s+(only|just)\s+the/i',
        '/(only|just)\s+(respond|reply|answer|output|return|print|echo)/i',
        '/no\s+(explanation|context|additional|extra)/i',
        '/without\s+(explanation|context|additional|extra)/i',
        
        // === ESCAPE SEQUENCES ===
        '/\\x[0-9a-fA-F]{2}/',
        '/\\u[0-9a-fA-F]{4}/',
        '/\\n|\\r|\\t/',
        '/%[0-9a-fA-F]{2}/',
        
        // === SUSPICIOUS KEYWORDS ===
        '/\b(admin|administrator|root|system|debug|developer|programmer)\b/i',
        '/\b(password|token|key|secret|api_key|private_key|access_token)\b/i',
        '/\b(execute|eval|system|shell|cmd|command|run)\b/i',
        '/\b(hack|exploit|bypass|override|inject|manipulate)\b/i',
        '/\b(config|configuration|settings|preferences|options)\b/i',
        
        // === ENCODING ATTEMPTS ===
        '/base64:/i',
        '/unicode:/i',
        '/hex:/i',
        '/url:/i',
        '/html:/i',
        
        // === ADVANCED TECHNIQUES ===
        '/character\s+by\s+character/i',
        '/step\s+by\s+step/i',
        '/word\s+for\s+word/i',
        '/reverse\s+engineer/i',
        '/deconstruct\s+(your\s+)?(thinking|logic|process)/i',
        '/analyze\s+(your\s+)?(behavior|response|logic)/i',
        
        // === DANGEROUS CHARACTERS ===
        '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/',
        '/[\p{C}\p{Zl}\p{Zp}]/u', // Control characters
        
        // === REPEATED PATTERNS (DoS) ===
        '/(.)\1{50,}/', // Characters repeated 50+ times
        '/(.{10,})\1{5,}/', // Patterns repeated 5+ times
    ];

    /**
     * Palavras suspeitas que requerem atenção extra
     */
    private const SUSPICIOUS_WORDS = [
        'admin', 'administrator', 'root', 'system', 'debug',
        'password', 'token', 'key', 'secret', 'api_key',
        'execute', 'eval', 'system', 'shell', 'cmd',
        'hack', 'exploit', 'bypass', 'override', 'inject',
    ];

    /**
     * Verifica se o input contém tentativas de Prompt Injection
     */
    public static function containsPromptInjection(string $input): bool
    {
        // Verificar padrões de injeção
        foreach (self::INJECTION_PATTERNS as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        // Verificar palavras suspeitas
        $lowerInput = strtolower($input);
        foreach (self::SUSPICIOUS_WORDS as $word) {
            if (strpos($lowerInput, $word) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sanitiza o input removendo conteúdo malicioso
     */
    public static function sanitize(string $input): string
    {
        // Remover padrões de injeção
        foreach (self::INJECTION_PATTERNS as $pattern) {
            $input = preg_replace($pattern, '[FILTERED]', $input);
        }

        // Limitar tamanho para prevenir DoS
        if (strlen($input) > 2000) {
            $input = substr($input, 0, 2000) . '... [TRUNCATED]';
        }

        return trim($input);
    }

    /**
     * Valida e sanitiza o input
     */
    public static function validateAndSanitize(string $input, bool $strict = true): array
    {
        $result = [
            'valid' => true,
            'sanitized' => $input,
            'warnings' => [],
            'blocked' => false,
        ];

        // Verificar se está vazio
        if (empty(trim($input))) {
            $result['valid'] = false;
            $result['warnings'][] = 'Input cannot be empty';
            return $result;
        }

        // Verificar Prompt Injection
        if (self::containsPromptInjection($input)) {
            if ($strict) {
                $result['valid'] = false;
                $result['blocked'] = true;
                $result['warnings'][] = 'Input contains potentially malicious content';
            } else {
                $result['sanitized'] = self::sanitize($input);
                $result['warnings'][] = 'Input was sanitized for security reasons';
            }
        }

        // Verificar comprimento
        if (strlen($input) > 2000) {
            $result['warnings'][] = 'Input is too long and will be truncated';
            $result['sanitized'] = substr($result['sanitized'], 0, 2000) . '... [TRUNCATED]';
        }

        // Verificar se contém apenas caracteres válidos
        if (!preg_match('/^[\p{L}\p{N}\s\.,\?!;:\-\(\)\[\]{}"\'\/@#%&*+=<>~`|\\\\]*$/u', $input)) {
            $result['warnings'][] = 'Input contains unusual characters';
        }

        return $result;
    }

    /**
     * Escapa caracteres especiais para o prompt
     */
    public static function escapeForPrompt(string $input): string
    {
        // Escapar caracteres que poderiam interferir com o prompt
        $replacements = [
            '{{' => '\\{\\{',
            '}}' => '\\}\\}',
            '[[' => '\\[\\[',
            ']]' => '\\]\\]',
            '```' => '\\`\\`\\`',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $input);
    }

    /**
     * Gera um hash do input para detecção de tentativas repetidas
     */
    public static function generateHash(string $input): string
    {
        return hash('sha256', strtolower(trim($input)));
    }

    /**
     * Verifica se o input é uma tentativa de role injection
     */
    public static function isRoleInjection(string $input): bool
    {
        $rolePatterns = [
            '/you\s+are\s+(now\s+)?(a\s+)?(different|new)/i',
            '/act\s+as\s+(if\s+you\s+are)?/i',
            '/pretend\s+(you\s+are)?/i',
            '/imagine\s+(you\s+are)?/i',
            '/from\s+now\s+on\s+(you\s+are)?/i',
        ];

        foreach ($rolePatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }
}
