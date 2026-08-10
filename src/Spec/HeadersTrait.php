<?php declare(strict_types=1);

    namespace STDW\Http\Spec;


    trait HeadersTrait
    {
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
