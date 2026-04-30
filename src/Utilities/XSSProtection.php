<?php

namespace HercegDoo\AIComposePlugin\Utilities;

/**
 * Centralized XSS protection utilities
 */
class XSSProtection
{
    /**
     * Sanitiza string para saída HTML
     */
    public static function escape(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Sanitiza string para JSON output
     */
    public static function escapeJson(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Sanitiza array de strings para JSON output
     */
    public static function escapeArray(array $data): array
    {
        return array_map(function($item) {
            return is_string($item) ? self::escapeJson($item) : $item;
        }, $data);
    }

    /**
     * Sanitiza atributos HTML
     */
    public static function escapeAttribute(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Sanitiza conteúdo para JavaScript
     */
    public static function escapeJs(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Verifica se contém conteúdo suspeito de XSS
     */
    public static function containsXSS(string $input): bool
    {
        $xssPatterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
            '/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload\s*=/i',
            '/onerror\s*=/i',
            '/onclick\s*=/i',
            '/onmouseover\s*=/i',
            '/onfocus\s*=/i',
            '/onblur\s*=/i',
            '/onchange\s*=/i',
            '/onsubmit\s*=/i',
            '/<\s*img[^>]*src\s*=\s*["\']?(?:javascript|vbscript):/i',
            '/<\s*embed[^>]*src\s*=\s*["\']?(?:javascript|vbscript):/i',
            '/<\s*object[^>]*data\s*=\s*["\']?(?:javascript|vbscript):/i',
        ];

        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Limpa string removendo conteúdo XSS
     */
    public static function clean(string $input): string
    {
        // Remove tags HTML perigosas
        $input = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $input);
        $input = preg_replace('/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi', '', $input);
        
        // Remove handlers de eventos
        $input = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/', '', $input);
        
        // Remove javascript: e vbscript:
        $input = preg_replace('/(?:javascript|vbscript):/i', '', $input);
        
        return trim($input);
    }
}
