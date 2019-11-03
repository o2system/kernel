<?php
/**
 * This file is part of the O2System Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author         Steeve Andrian Salim
 * @copyright      Copyright (c) Steeve Andrian Salim
 */

// ------------------------------------------------------------------------

namespace O2System\Kernel\Http\Message;

// ------------------------------------------------------------------------

use O2System\Kernel\DataStructures\Input\Files;
use O2System\Psr\Http\Message\StreamInterface;
use O2System\Psr\Http\Message\UploadedFileInterface;
use O2System\Spl\Exceptions\Logic\BadFunctionCall\BadPhpExtensionCallException;

class UploadFile implements UploadedFileInterface
{
    /**
     * UploadFile::$name
     *
     * @var string
     */
    protected $name;

    /**
     * UploadFile::$type
     *
     * @var string
     */
    protected $type;

    /**
     * UploadFile::$tmpName
     *
     * @var string
     */
    protected $tmpName;

    /**
     * UploadFile::$size
     *
     * @var int
     */
    protected $size;

    /**
     * UploadFile::$path
     *
     * @var string
     */
    protected $path;

    /**
     * UploadFile::$error
     *
     * @var mixed
     */
    protected $error;

    /**
     * UploadFIle::$isMoved
     *
     * @var bool
     */
    protected $isMoved = false;

    /**
     * Uploader File Stream
     *
     * @var Stream
     */
    protected $stream;

    // ------------------------------------------------------------------------

    /**
     * UploadFile::__construct
     *
     * @param array $uploadedFile
     *
     * @throws \O2System\Spl\Exceptions\Logic\BadFunctionCall\BadPhpExtensionCallException
     */
    public function __construct(array $uploadedFile)
    {
        if ( ! class_exists('finfo')) {
            throw new BadPhpExtensionCallException('E_HEADER_BADPHPEXTENSIONCALLEXCEPTION', 1);
        }

        $this->name = $uploadedFile[ 'name' ];
        $this->type = $uploadedFile[ 'type' ];
        $this->tmpName = $uploadedFile[ 'tmp_name' ];
        $this->size = $uploadedFile[ 'size' ];
        $this->error = $uploadedFile[ 'error' ];

        if (defined('PATH_STORAGE')) {
            $this->path = PATH_STORAGE;
        } else {
            $this->path = dirname($_SERVER[ 'SCRIPT_FILENAME' ]) . DIRECTORY_SEPARATOR . $path;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * UploadedFileInterface::getStream
     *
     * Retrieve a stream representing the uploaded file.
     *
     * This method MUST return a StreamInterface instance, representing the
     * uploaded file. The purpose of this method is to allow utilizing native PHP
     * stream functionality to manipulate the file upload, such as
     * stream_copy_to_stream() (though the result will need to be decorated in a
     * native PHP stream wrapper to work with such functions).
     *
     * If the moveTo() method has been called previously, this method MUST raise
     * an exception.
     *
     * @return StreamInterface Stream representation of the uploaded file.
     * @throws \RuntimeException in cases when no stream is available.
     * @throws \RuntimeException in cases when no stream can be created.
     */
    public function getStream()
    {
        if ($this->isMoved) {
            throw new \RuntimeException('File Has Been Already Moved');
        }

        if ($this->stream === null) {
            $context = fopen($this->tmpName, 'r');

            if (is_resource($context)) {
                $this->stream = new Stream();
            } else {
                throw new \RuntimeException('Cannot create stream context');
            }
        }

        return $this->stream;
    }

    // ------------------------------------------------------------------------

    /**
     * UploadFile::setName
     *
     * Sets target filename.
     *
     * @param string $name               The target filename.
     * @param string $conversionFunction Conversion function name, by default it's using dash inflector function.
     *
     * @return static
     */
    public function setName($name, $conversionFunction = 'dash')
    {
        $this->name = call_user_func_array(
            $conversionFunction,
            [
                strtolower(
                    trim(
                        pathinfo($name, PATHINFO_FILENAME)
                    )
                ),
            ]
        );

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Uploader::setPath
     *
     * Sets uploaded file path.
     *
     * @param string $path [description]
     *
     * @return static
     */
    public function setPath($path = '')
    {
        if (is_dir($path)) {
            $this->path = $path;
        } elseif (defined('PATH_STORAGE')) {
            if (is_dir($path)) {
                $this->path = $path;
            } else {
                $this->path = PATH_STORAGE . str_replace(PATH_STORAGE, '', $path);
            }
        } else {
            $this->path = dirname($_SERVER[ 'SCRIPT_FILENAME' ]) . DIRECTORY_SEPARATOR . $path;
        }
    }

    // --------------------------------------------------------------------------------------

    /**
     * UploadFile::store
     *
     * @param string|null $path
     */
    public function store($path = null)
    {
        if(isset($path)) {
            $this->setPath($path);
        }

        $this->moveTo($this->path . pathinfo($this->name, PATHINFO_FILENAME) . $this->getExtension());

        return $this->getErrors() ? false : true;
    }

    // --------------------------------------------------------------------------------------

    /**
     * UploadedFileInterface::moveTo
     *
     * Move the uploaded file to a new location.
     *
     * Use this method as an alternative to move_uploaded_file(). This method is
     * guaranteed to work in both SAPI and non-SAPI environments.
     * Implementations must determine which environment they are in, and use the
     * appropriate method (move_uploaded_file(), rename(), or a stream
     * operation) to perform the operation.
     *
     * $targetPath may be an absolute path, or a relative path. If it is a
     * relative path, resolution should be the same as used by PHP's rename()
     * function.
     *
     * The original file or stream MUST be removed on completion.
     *
     * If this method is called more than once, any subsequent calls MUST raise
     * an exception.
     *
     * When used in an SAPI environment where $_FILES is populated, when writing
     * files via moveTo(), is_uploaded_file() and move_uploaded_file() SHOULD be
     * used to ensure permissions and upload status are verified correctly.
     *
     * If you wish to move to a stream, use getStream(), as SAPI operations
     * cannot guarantee writing to stream destinations.
     *
     * @see http://php.net/is_uploaded_file
     * @see http://php.net/move_uploaded_file
     *
     * @param string $targetPath Path to which to move the uploaded file.
     *
     * @throws \InvalidArgumentException if the $targetPath specified is invalid.
     * @throws \RuntimeException on any errors during the move operation.
     * @throws \RuntimeException on the second or subsequent call to the method.
     */
    public function moveTo($targetPath)
    {
        if ($this->isMoved) {
            throw new \RuntimeException('Uploaded File Has Been Moved');
        }

        $fileInfo = pathinfo($targetPath);

        if ( ! is_file($filePath = $fileInfo['dirname'] . DIRECTORY_SEPARATOR . $fileInfo['filename'] . '.' . $fileInfo['extension'])) {
            $targetPath = $filePath;
        } elseif ( ! is_file($filePath = $fileInfo['dirname'] . DIRECTORY_SEPARATOR . $fileInfo['filename'] . '-1' . '.' . $fileInfo['extension'])) {
            $targetPath = $filePath;
        } else {
            $existingFiles = glob($fileInfo['dirname'] . DIRECTORY_SEPARATOR . $fileInfo['filename'] . '*.' . $fileInfo['extension']);
            if (count($existingFiles)) {
                $increment = count($existingFiles) - 1;
            }

            foreach (range($increment + 1, $increment + 3, 1) as $increment) {
                if ( ! is_file($filePath = $fileInfo['dirname'] . DIRECTORY_SEPARATOR . $fileInfo['filename'] . '-' . $increment . '.' . $fileInfo['extension'])) {
                    $targetPath = $filePath;
                    break;
                }
            }
        }

        if ( ! is_writable(dirname($targetPath))) {
            @mkdir(dirname($targetPath), 0777, true);
        }

        if (strpos($targetPath, '://') !== false) {
            if ( ! copy($this->tmpName, $targetPath)) {
                throw new \RuntimeException(sprintf('Cant Move Uploaded File %1 to %2', $this->tmpName, $targetPath));
            }

            if ( ! unlink($this->tmpName)) {
                throw new \RuntimeException('Failed To Remove Uploaded Temp File');
            }
        } else {
            if ( ! is_uploaded_file($this->tmpName)) {
                throw new \RuntimeException('File Is Not Valid Uploaded File');
            }

            if ( ! move_uploaded_file($this->tmpName, $targetPath)) {
                throw new \RuntimeException(sprintf('Cant Move Uploaded File %1 to %2', $this->tmpName, $targetPath));
            }
        }

        $this->isMoved = true;
    }

    // ------------------------------------------------------------------------

    /**
     * UploadedFileInterface::getSize
     *
     * Retrieve the file size.
     *
     * Implementations SHOULD return the value stored in the "size" key of
     * the file in the $_FILES array if available, as PHP calculates this based
     * on the actual size transmitted.
     *
     * @return int|null The file size in bytes or null if unknown.
     */
    public function getSize()
    {
        return $this->size;
    }

    // ------------------------------------------------------------------------

    /**
     * UploadedFileInterface::getError
     *
     * Retrieve the error associated with the uploaded file.
     *
     * The return value MUST be one of PHP's UPLOAD_ERR_XXX constants.
     *
     * If the file was uploaded successfully, this method MUST return
     * UPLOAD_ERR_OK.
     *
     * Implementations SHOULD return the value stored in the "error" key of
     * the file in the $_FILES array.
     *
     * @see http://php.net/manual/en/features.file-upload.error.php
     * @return int One of PHP's UPLOAD_ERR_XXX constants.
     */
    public function getError()
    {
        return $this->error;
    }

    // ------------------------------------------------------------------------

    /**
     * UploadedFileInterface::getClientFilename
     *
     * Retrieve the filename sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious filename with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "name" key of
     * the file in the $_FILES array.
     *
     * @return string|null The filename sent by the client or null if none
     *     was provided.
     */
    public function getClientFilename()
    {
        return $this->name;
    }

    // ------------------------------------------------------------------------

    /**
     * UploadedFileInterface::getClientMediaType
     *
     * Retrieve the media type sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious media type with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "type" key of
     * the file in the $_FILES array.
     *
     * @return string|null The media type sent by the client or null if none
     *     was provided.
     */
    public function getClientMediaType()
    {
        return $this->type;
    }

    // --------------------------------------------------------------------------------------

    /**
     * UploadFile::getFileMime
     *
     * Get file mime type
     *
     * @return string
     */
    public function getFileMime()
    {
        $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $this->tmpName);

        return $mime;
    }

    // --------------------------------------------------------------------------------------

    /**
     * UploadFile::getExtension
     *
     * Get uploaded file extension
     *
     * @return string
     */
    public function getExtension()
    {
        return pathinfo($this->name, PATHINFO_EXTENSION);
    }

    // ------------------------------------------------------------------------

    /**
     * UploadFile::getFileTemp
     *
     * @return mixed|string
     */
    public function getFileTemp()
    {
        return $this->tmpName;
    }
}
