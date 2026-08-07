<?php declare(strict_types=1);

    namespace STDW\Http\Spec;

    use STDW\Contract\Http\ResponseInterface as BaseResponseInterface;


    interface ResponseInterface extends BaseResponseInterface
    {
        /**
         * @return array<string, array{
         *     name: string,
         *     value: string,
         *     options: array{
         *         expires?: int,
         *         path?: string,
         *         domain?: string,
         *         secure?: bool,
         *         httponly?: bool,
         *         samesite?: 'Lax'|'lax'|'None'|'none'|'Strict'|'strict'
         *     }
         * }>
         */
        public function getCookies(): array;

        /**
         * @param string $name
         * @return null|array{
         *     name: string,
         *     value: string,
         *     options: array{
         *         expires?: int,
         *         path?: string,
         *         domain?: string,
         *         secure?: bool,
         *         httponly?: bool,
         *         samesite?: 'Lax'|'lax'|'None'|'none'|'Strict'|'strict'
         *     }
         * }
         */
        public function getCookie(string $name): ?array;

        /**
         * @param string $name 
         * @param string $value 
         * @param array{
         *     expires?: int,
         *     path?: string,
         *     domain?: string,
         *     secure?: bool,
         *     httponly?: bool,
         *     samesite?: 'Lax'|'lax'|'None'|'none'|'Strict'|'strict'
         * } $options 
         * @return ResponseInterface 
         */
        public function withCookie(string $name, string $value, array $options = []): ResponseInterface;

        /**
         * @param string $name 
         * @return ResponseInterface 
         */
        public function withoutCookie(string $name): ResponseInterface;

        /** @return ResponseInterface 
         */
        public function clearCookies(): ResponseInterface;
    }
