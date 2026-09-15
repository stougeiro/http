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
            return strtolower(strtr($name, '_', '-'));
        }

        /**
         * @param string $name 
         * @return string 
         */
        protected function canonicalizeHeaderName(string $name): string
        {
            return ucwords($name, '-');
        }
    }
