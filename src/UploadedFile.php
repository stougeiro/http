<?php declare(strict_types=1);

    namespace STDW\Http;

    use RuntimeException;

    use STDW\Contract\Http\UploadedFileInterface;


    class UploadedFile implements UploadedFileInterface
    {
        /**
         * @var array{
         *     name: string,
         *     type: string,
         *     tmp_name: string,
         *     size: int,
         *     error: int
         * }
         */
        protected readonly array $raw;

        /** @var string
         */
        protected string $name;

        /** @var string
         */
        protected string $type;

        /** @var null|string
         */
        protected ?string $tmpName;

        /** @var int
         */
        protected int $size;

        /** @var int
         */
        protected int $error;

        /** @var null|string
         */
        protected ?string $path = null;


        /**
         * @param string $name 
         * @param string $type 
         * @param string $tmp_name 
         * @param int $size 
         * @param int $error 
         * @return void 
         */
        public function __construct(
            string $name,
            string $type,
            string $tmp_name,
            int $size,
            int $error
        ) {
            $this->raw = [
                'name' => $name,
                'type' => $type,
                'tmp_name' => $tmp_name,
                'size' => $size,
                'error' => $error,
            ];

            $this->name = $name;
            $this->type = $type;
            $this->tmpName = $tmp_name;
            $this->size = $size;
            $this->error = $error;
        }


        /**
         * @param array{
         *     name: string,
         *     type: string,
         *     tmp_name: string,
         *     size: int,
         *     error: int
         * } $data
         * @return UploadedFileInterface 
         */
        public static function fromArray(array $data): UploadedFileInterface
        {
            return new self(
                $data['name'],
                $data['type'],
                $data['tmp_name'],
                $data['size'],
                $data['error'],
            );
        }

        /**
         * @return array{
         *     name: string,
         *     type: string,
         *     tmp_name: ?string,
         *     size: int,
         *     error: int
         * } $data
         */
        public function getRawData(): array
        {
            return $this->raw;
        }

        /** @return string 
         */
        public function getName(): string
        {
            return $this->name;
        }

        /** @return string 
         */
        public function getType(): string
        {
            return $this->type;
        }

        /** @return null|string 
         */
        public function getTmpName(): ?string
        {
            return $this->tmpName;
        }

        /** @return int 
         */
        public function getSize(): int
        {
            return $this->size;
        }

        /** @return int 
         */
        public function getError(): int
        {
            return $this->error;
        }

        /** @return null|string 
         */
        public function getErrorMessage(): ?string
        {
            return match ($this->error) {
                UPLOAD_ERR_OK => null,
                UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds upload_max_filesize.',
                UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds MAX_FILE_SIZE.',
                UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',

                default => 'Unknown upload error.',
            };
        }

        /** @return null|string 
         */
        public function getPath(): ?string
        {
            return $this->path;
        }

        /**
         * @param string $name 
         * @return UploadedFileInterface 
         */
        public function withName(string $name): UploadedFileInterface
        {
            $this->name = $name;

            return $this;
        }

        /**
         * @param string $path 
         * @return void 
         */
        public function moveTo(string $path): void
        {
            if ($this->error !== UPLOAD_ERR_OK) {
                throw new RuntimeException("Cannot move uploaded file: " . $this->getErrorMessage());
            }

            $isDir = $this->doIsDir($path);

            if ( ! $isDir) {
                $this->name = basename($path);
            }

            $target = $isDir
                ? rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $this->name
                : $path;

            $from = $this->tmpName ?? $this->path;

            if (is_null($from)) {
                throw new RuntimeException("Source file is not available.");
            }

            $success = is_null($this->tmpName)
                ? $this->doRename($from, $target)
                : $this->doMoveUploadedFile($from, $target);

            if ( ! $success) {
                throw new RuntimeException("Failed to move uploaded file.");
            }

            $this->tmpName = null;
            $this->path = $target;
        }

        /**
         * @param string $filename 
         * @return bool 
         */
        protected function doIsDir(string $filename): bool
        {
            return is_dir($filename);
        }

        /**
         * @param string $from 
         * @param string $to 
         * @param null|resource $context 
         * @return bool 
         */
        protected function doRename(string $from, string $to, $context = null): bool
        {
            return rename($from, $to, $context);
        }

        /**
         * @param string $from 
         * @param string $to 
         * @return bool 
         */
        protected function doMoveUploadedFile(string $from, string $to): bool
        {
            return move_uploaded_file($from, $to);
        }
    }
