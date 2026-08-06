<?php declare(strict_types=1);

    namespace STDW\Http\Exception;

    use Exception;


    class NotFoundException extends Exception
    {
        public function __construct()
        {
            parent::__construct("Not Found");
        }
    }
