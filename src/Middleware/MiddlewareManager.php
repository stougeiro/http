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
            $index = 0;
            $middlewares = $this->middlewares;
            $finalHandler = $this->finalHandler;

            $next = function (RequestInterface $req, ResponseInterface $res) use (&$index, $middlewares, $finalHandler, &$next): ResponseInterface {
                if ( ! isset($middlewares[$index])) {
                    if ( ! is_null($finalHandler)) {
                        return $finalHandler->process($req, $res, static fn($q, $s) => $s);
                    }

                    return $res;
                }

                $middleware = $middlewares[$index++];

                return $middleware->process($req, $res, $next);
            };

            return $next($request, $response);
        }
    }
