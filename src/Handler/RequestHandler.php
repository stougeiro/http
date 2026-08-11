<?php declare(strict_types=1);

    namespace STDW\Http\Handler;

    use STDW\Contract\Http\RequestHandlerInterface;
    use STDW\Contract\Http\MiddlewareManagerInterface;
    use STDW\Contract\Http\RequestInterface;
    use STDW\Contract\Http\ResponseInterface;


    class RequestHandler implements RequestHandlerInterface
    {
        /**
         * @property MiddlewareManagerInterface $manager 
         * @return void 
         */
        public function __construct(
            protected MiddlewareManagerInterface $manager
        ) {}


        /**
         * @param RequestInterface $request 
         * @param ResponseInterface $response 
         * @return ResponseInterface 
         */
        public function handle(RequestInterface $request, ResponseInterface $response): ResponseInterface
        {
            return $this->manager->handle($request, $response);
        }
    }
