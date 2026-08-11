<?php declare(strict_types=1);

    namespace STDW\Http\Middleware;

    use STDW\Contract\Http\MiddlewareManagerInterface;
    use STDW\Contract\Http\MiddlewareInterface;
    use STDW\Contract\Http\RequestInterface;
    use STDW\Contract\Http\ResponseInterface;


    class MiddlewareManager implements MiddlewareManagerInterface
    {
        /** @var array<int, MiddlewareInterface>
         */
        protected array $middlewares = [];

        /** @var null|MiddlewareInterface
         */
        protected ?MiddlewareInterface $finalHandler = null;


        /**
         * @param MiddlewareInterface $middleware 
         * @return void 
         */
        public function add(MiddlewareInterface $middleware): void
        {
            $this->middlewares[] = $middleware;
        }

        /**
         * @param MiddlewareInterface $middleware 
         * @return void 
         */
        public function setFinalHandler(MiddlewareInterface $middleware): void
        {
            $this->finalHandler = $middleware;
        }

        /**
         * @param RequestInterface $request 
         * @param ResponseInterface $response 
         * @return ResponseInterface 
         */
        public function handle(RequestInterface $request, ResponseInterface $response): ResponseInterface
        {
            return $this->dispatch($request, $response, 0);
        }


        /**
         * @param RequestInterface $request
         * @param ResponseInterface $response
         * @param int $index
         * @return ResponseInterface
         */
        protected function dispatch(RequestInterface $request, ResponseInterface $response, int $index): ResponseInterface
        {
            if ( ! isset($this->middlewares[$index])) {
                if ($this->finalHandler !== null) {
                    return $this->finalHandler->process($request, $response, static fn($req, $res) => $res);
                }

                return $response;
            }

            $middleware = $this->middlewares[$index];

            return $middleware->process(
                $request,
                $response,
                function (RequestInterface $req, ResponseInterface $res) use ($index): ResponseInterface {
                    return $this->dispatch($req, $res, $index + 1);
                }
            );
        }
    }
