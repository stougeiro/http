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
            $parts = explode('-', $name);
            $count = count($parts);

            for ($i = 0; $i < $count; $i++) {
                $parts[$i] = ucfirst($parts[$i]);
            }

            return implode('-', $parts);
        }
    }
