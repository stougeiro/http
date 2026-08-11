<?php declare(strict_types=1);

    namespace STDW\Http\Response;

    use Closure;
    use InvalidArgumentException;

    use STDW\Contract\Http\ResponseInterface;
    use STDW\Http\Response;


    class SseResponse extends Response
    {
        /** @var Closure
         */
        protected Closure $eventGenerator;


        /**
         * @param Closure $eventGenerator
         * @param int $status
         * @param array<string, string> $headers
         * @param null|string $body
         * @return void
         */
        public function __construct(Closure $eventGenerator, int $status = 200, array $headers = [], ?string $body = '')
        {
            $this->eventGenerator = $eventGenerator;

            parent::__construct($status, $headers, $body);
        }


        /**
         * @param int $code
         * @return ResponseInterface
         * @throws InvalidArgumentException
         */
        public function withStatus(int $code): ResponseInterface
        {
            if ($code !== 200) {
                throw new InvalidArgumentException("SseResponse only supports status 200");
            }

            $this->status = $code;

            return $this;
        }

        /**
         * @param null|string $body
         * @return ResponseInterface
         * @throws InvalidArgumentException
         */
        public function withBody(?string $body): ResponseInterface
        {
            if ( ! empty($body)) {
                throw new InvalidArgumentException("Invalid body on SseResponse");
            }

            return $this;
        }

        /** @return void
         */
        public function send(): void
        {
            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            /** Status
             */

            http_response_code($this->status);

            /** Headers
             */

            $this
                ->withHeader('Content-Type', 'text/event-stream')
                ->withHeader('Cache-Control', 'no-cache')
                ->withHeader('Connection', 'keep-alive');

            foreach ($this->headers as $name => $value) {
                header($this->canonicalizeHeaderName($name) .': '. $value);
            }

            /** Event
             */

            while (true) {
                /**
                 * @var ?array{
                 *     event?: string,
                 *     id?: string,
                 *     retry?: string,
                 *     data?: string|array<int, string>
                 * }
                 */
                $event = ($this->eventGenerator)();

                if (is_null($event)) {
                    break;
                }

                if ( ! isset($event['data'])) {
                    error_log("SseResponse event ignored: missing 'data' field");

                    continue;
                }


                // event
                if (isset($event['event'])) {
                    echo "event: ", $event['event'], "\n";
                }

                // id
                if (isset($event['id'])) {
                    echo "id: ", $event['id'], "\n";
                }

                // retry
                if (isset($event['retry'])) {
                    echo "retry: ", $event['retry'], "\n";
                }

                // data
                $dataLines = is_array($event['data'])
                    ? $event['data']
                    : [$event['data']];

                foreach ($dataLines as $line) {
                    echo "data: ", $line, "\n";
                }

                echo "\n";

                flush();

                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                }

                if (connection_aborted()) {
                    break;
                }
            }
        }
    }
