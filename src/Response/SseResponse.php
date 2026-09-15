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
            if (is_null($body) || $body === '') {
                return $this;
            }

            throw new InvalidArgumentException("Invalid body on SseResponse");
        }

        /** @return void
         */
        public function send(): void
        {
            $this->flushOutputBuffers();

            /** Send
             */

            $this->sendStatus();

            $this
                ->withHeader('Content-Type', 'text/event-stream')
                ->withHeader('Cache-Control', 'no-cache')
                ->withHeader('Connection', 'keep-alive');

            $this->sendHeaders();

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
                    if ( ! $this->isValidEvent($event['event'])) {
                        error_log("SseResponse: invalid event field, skipping");
                    } else {
                        echo "event: ", $event['event'], "\n";
                    }
                }

                // id
                if (isset($event['id'])) {
                    if ( ! $this->isValidId($event['id'])) {
                        error_log("SseResponse: invalid id field, skipping");
                    } else {
                        echo "id: ", $event['id'], "\n";
                    }
                }

                // retry
                if (isset($event['retry'])) {
                    if ( ! $this->isValidRetry($event['retry'])) {
                        error_log("SseResponse: invalid retry value ignored");
                    } else {
                        echo "retry: ", $event['retry'], "\n";
                    }
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

                if (connection_aborted()) {
                    break;
                }
            }

            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }
        }

        /**
         * @param string $event 
         * @return bool 
         */
        protected function isValidEvent(string $event): bool
        {
            return strpos($event, "\r") === false
                && strpos($event, "\n") === false;
        }

        /**
         * @param string $id
         * @return bool
         */
        protected function isValidId(string $id): bool
        {
            return preg_match('/[^\x20-\x7E]/', $id) === 0;
        }

        /**
         * @param string $retry
         * @return bool
         */
        protected function isValidRetry(string $retry): bool
        {
            return $retry !== '' && ctype_digit($retry);
        }

        /** @return void
         */
        protected function flushOutputBuffers(): void
        {
            while (ob_get_level() > 0) {
                ob_end_flush();
            }
        }
    }
