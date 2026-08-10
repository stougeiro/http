<?php declare(strict_types=1);

    namespace STDW\Http\Spec;


    trait CookiesTrait
    {
        /**
         * Normalizes cookie options ensuring a complete shape.
         *
         * @param array{
         *     expires?: int,
         *     path?: string,
         *     domain?: string,
         *     secure?: bool,
         *     httponly?: bool,
         *     samesite?: 'Lax'|'lax'|'None'|'none'|'Strict'|'strict'
         * } $options 
         * @return array{
         *     expires: int,
         *     path: string,
         *     domain: string,
         *     secure: bool,
         *     httponly: bool,
         *     samesite: 'Lax'|'lax'|'None'|'none'|'Strict'|'strict'
         * }
         */
        protected function normalizeCookieOptions(array $options): array
        {
            return [
                'expires' => $options['expires'] ?? 0,
                'path' => $options['path'] ?? '/',
                'domain' => $options['domain'] ?? '',
                'secure' => $options['secure'] ?? false,
                'httponly' => $options['httponly'] ?? false,
                'samesite' => $options['samesite'] ?? 'strict',
            ];
        }
    }
