<?php declare(strict_types=1);

    namespace STDW\Http\Spec;


    trait HeadersTrait
    {
        /** @var array<string, string>
         */
        protected array $headers = [];


        /** @return array<string, string>
         */
        public function getHeaders(): array
        {
            return $this->headers;
        }

        /**
         * @param string $name 
         * @return null|string 
         */
        public function getHeader(string $name): ?string
        {
            return $this->headers[$this->normalizeHeaderName($name)] ?? null;
        }

        /**
         * @param string $name 
         * @return bool 
         */
        public function hasHeader(string $name): bool
        {
            return isset($this->headers[$this->normalizeHeaderName($name)]);
        }


        /**
         * @param string $name 
         * @return string 
         */
        protected function normalizeHeaderName(string $name): string
        {
            return strtolower($name);
        }

        /**
         * @param string $name 
         * @return string 
         */
        protected function canonicalizeHeaderName(string $name): string
        {
            return implode('-', array_map('ucfirst', explode('-', $name)));
        }
    }
