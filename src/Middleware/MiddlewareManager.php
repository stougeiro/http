<?php declare(strict_types=1);

    namespace STDW\Http\Middleware;

    use STDW\Contract\Http\MiddlewareManagerInterface;
    use STDW\Contract\Http\MiddlewareInterface;
    use STDW\Contract\Http\RequestInterface;
    use STDW\Contract\Http\ResponseInterface;


    class MiddlewareManager implements MiddlewareManagerInterface
    {
        /**
         * @param MiddlewareInterface $middleware 
         * @return void 
         */
        public function add(MiddlewareInterface $middleware): void
        {

        }

        /**
         * @param MiddlewareInterface $middleware 
         * @return void 
         */
        public function setFinalHandler(MiddlewareInterface $middleware): void
        {

        }

        /**
         * @param RequestInterface $request 
         * @param ResponseInterface $response 
         * @return ResponseInterface 
         */
        public function handle(RequestInterface $request, ResponseInterface $response): ResponseInterface
        {

        }
    }
