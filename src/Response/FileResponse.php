<?php declare(strict_types=1);

    namespace STDW\Http\Response;

    use InvalidArgumentException;

    use STDW\Http\Spec\ResponseInterface;
    use STDW\Http\Response;


    class FileResponse extends Response
    {
        /** @var string
         */
        protected string $filePath;


        /**
         * @param string $filePath
         * @param int $status
         * @param array<string, string> $headers
         * @param null|string $body
         * @return void
         */
        public function __construct(string $filePath, int $status = 200, array $headers = [], ?string $body = '')
        {
            $this->filePath = $filePath;

            parent::__construct($status, $headers, $body);
        }


        /**
         * @param int $code
         * @return ResponseInterface
         * @throws InvalidArgumentException
         */
        public function withStatus(int $code): ResponseInterface
        {
            if ( ! in_array($code, [200, 403, 404], true)) {
                throw new InvalidArgumentException("Invalid HTTP status code for FileResponse: {$code}");
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
                throw new InvalidArgumentException("Invalid body on FileResponse");
            }

            return $this;
        }

        /** @return void
         */
        public function send(): void
        {
            /** Send
             */

            if ( ! is_file($this->filePath)) {
                $this->withStatus(404);
            }

            elseif ( ! is_readable($this->filePath)) {
                $this->withStatus(403);
            }

            else {
                $fileName = basename($this->filePath);
                $fileSize = (string) filesize($this->filePath);

                $this
                    ->withStatus(200)
                    ->withHeader('Content-Description', 'File Transfer')
                    ->withHeader('Content-Type', 'application/octet-stream')
                    ->withHeader('Content-Disposition', 'attachment; filename="'.$fileName.'"')
                    ->withHeader('Content-Transfer-Encoding', 'binary')
                    ->withHeader('Expires', '0')
                    ->withHeader('Cache-Control', 'must-revalidate')
                    ->withHeader('Pragma', 'public')
                    ->withHeader('Content-Length', $fileSize);
            }

            parent::send();

            /** Download file
             */

            if ($this->status !== 200) {
                return;
            }

            $handle = fopen($this->filePath, 'rb');

            if ($handle) {
                fpassthru($handle);
                fclose($handle);
            }
        }
    }
