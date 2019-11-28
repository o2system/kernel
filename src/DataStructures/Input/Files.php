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

namespace O2System\Kernel\DataStructures\Input;

// ------------------------------------------------------------------------

use O2System\Filesystem\Handlers\Uploader;
use O2System\Kernel\Http\Message\UploadFile;
use O2System\Spl\DataStructures\SplArrayObject;
use O2System\Spl\Exceptions\Logic\BadFunctionCall\BadDependencyCallException;
use O2System\Spl\Iterators\ArrayIterator;
use O2System\Spl\Traits\Collectors\ErrorCollectorTrait;

/**
 * Class Files
 * @package O2System\Kernel\DataStructures\Input
 */
class Files extends SplArrayObject
{
    use ErrorCollectorTrait;

    /**
     * Files::$path
     *
     * Uploader file destination path.
     *
     * @var string
     */
    protected $path;

    /**
     * Files::$allowedMimes
     *
     * Allowed uploaded file mime types.
     *
     * @var array
     */
    protected $allowedMimes;

    /**
     * Files::$allowedExtensions
     *
     * Allowed uploaded file extensions.
     *
     * @var array
     */
    protected $allowedExtensions;

    /**
     * Files::$allowedFileSize
     *
     * Allowed uploaded file size.
     *
     * @var array
     */
    protected $allowedFileSize = [
        'min' => 0,
        'max' => 0,
    ];

    /**
     * Files::$stored
     *
     * @var array|\O2System\Spl\Iterators\ArrayIterator
     */
    protected $stored = [];
    
    // --------------------------------------------------------------------------------------

    /**
     * Files::__construct
     */
    public function __construct()
    {
        parent::__construct();

        $this->stored = new ArrayIterator();
    }

    // --------------------------------------------------------------------------------------

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
        
        return $this;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Files::setAllowedMimes
     *
     * Set allowed mime for uploaded file.
     *
     * @param string|array $mimes List of allowed file mime types.
     *
     * @return static
     */
    public function setAllowedMimes($mimes)
    {
        if (is_string($mimes)) {
            $mimes = explode(',', $mimes);
        }

        $this->allowedMimes = array_map('trim', $mimes);

        return $this;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Files::setAllowedExtensions
     *
     * Set allowed extensions for uploaded file.
     *
     * @param string|array $extensions List of allowed file extensions.
     *
     * @return static
     */
    public function setAllowedExtensions($extensions)
    {
        if (is_string($extensions)) {
            $extensions = explode(',', $extensions);
        }

        $this->allowedExtensions = array_map('trim', $extensions);

        return $this;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Files::setMinFileSize
     *
     * Set minimum file size
     *
     * @param int    $fileSize Allowed minimum file size.
     * @param string $unit     Allowed minimum file size unit conversion.
     *
     * @return static
     */
    public function setMinFileSize($fileSize, $unit = 'M')
    {
        switch ($unit) {
            case 'B':
                $fileSize = (int)$fileSize;
                break;
            case 'K':
                $fileSize = (int)$fileSize * 1000;
                break;
            case 'M':
                $fileSize = (int)$fileSize * 1000000;
                break;
            case 'G':
                $fileSize = (int)$fileSize * 1000000000;
                break;
        }

        $this->allowedFileSize[ 'min' ] = (int)$fileSize;

        return $this;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Files::setMaxFileSize
     *
     * Set maximum file size
     *
     * @param int    $fileSize Allowed maximum file size.
     * @param string $unit     Allowed maximum file size unit conversion.
     *
     * @return static
     */
    public function setMaxFileSize($fileSize, $unit = 'M')
    {
        switch ($unit) {
            case 'B':
                $fileSize = (int)$fileSize;
                break;
            case 'K':
                $fileSize = (int)$fileSize * 1000;
                break;
            case 'M':
                $fileSize = (int)$fileSize * 1000000;
                break;
            case 'G':
                $fileSize = (int)$fileSize * 1000000000;
                break;
        }

        $this->allowedFileSize[ 'max' ] = (int)$fileSize;

        return $this;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Files::process
     *
     * @param string|null $field Field offset server uploaded files
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function process($field = null)
    {
        $uploadFiles = input()->files($field);

        if ($uploadFiles instanceof UploadFile) {
            $uploadFiles = [$uploadFiles];
        }

        if (count($uploadFiles)) {
            foreach ($uploadFiles as $file) {
                if ($file instanceof UploadFile) {
                    if (defined('PATH_STORAGE')) {
                        if ($this->path === PATH_STORAGE) {
                            if (strpos($file->getClientMediaType(), 'image') !== false) {
                                $this->path = $this->path . 'images' . DIRECTORY_SEPARATOR;
                            } else {
                                $this->path = $this->path . 'files' . DIRECTORY_SEPARATOR;
                            }
                        }
                    }
                    
                    if ($this->validate($file)) {
                        $file->moveTo($this->path . $file->getClientFilename());

                        if ( ! $file->getError()) {
                            $this->stored[] = $file;
                        } else {
                            $this->errors[] = $file->getError();
                        }
                    }
                }
            }

            if (count($this->errors) == 0) {
                return true;
            }
        }

        return false;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Files::validate
     *
     * @param \O2System\Kernel\Http\Message\UploadFile $file
     *
     * @return bool
     */
    protected function validate(UploadFile $file)
    {
        /* Validate extension */
        if (is_array($this->allowedExtensions) && count($this->allowedExtensions)) {
            if ( ! in_array($file->getExtension(), $this->allowedExtensions)) {
                $this->errors[] = language()->getLine(
                    'UPLOADER_E_ALLOWED_EXTENSIONS',
                    [implode(',', $this->allowedExtensions), $file->getExtension()]
                );
            }
        }

        /* Validate mime */
        if (is_array($this->allowedMimes) && count($this->allowedExtensions)) {
            if ( ! in_array($file->getFileMime(), $this->allowedMimes)) {
                $this->errors[] = language()->getLine(
                    'UPLOADER_E_ALLOWED_MIMES',
                    [implode(',', $this->allowedMimes), $file->getFileMime()]
                );
            }
        }

        /* Validate min size */
        if ($this->allowedFileSize[ 'min' ] > 0) {
            if ($file->getSize() < $this->allowedFileSize[ 'min' ]) {
                $this->errors[] = language()->getLine(
                    'UPLOADER_E_ALLOWED_MIN_FILESIZE',
                    [$this->allowedFileSize[ 'min' ], $file->getSize()]
                );
            }
        }

        /* Validate max size */
        if ($this->allowedFileSize[ 'max' ] > 0) {
            if ($file->getSize() > $this->allowedFileSize[ 'max' ]) {
                $this->errors[] = language()->getLine(
                    'UPLOADER_E_ALLOWED_MAX_FILESIZE',
                    [$this->allowedFileSize[ 'max' ], $file->getSize()]
                );
            }
        }

        if (count($this->errors) == 0) {
            return true;
        }

        return false;
    }

    // ------------------------------------------------------------------------
    
    /**
     * Files::store
     *
     * @param string      $index
     * @param string|null $path
     *                         
     * @return bool
     */
    public function store($index = null, $path = null)
    {
        if(isset($path)) {
            $this->setPath($path);
        }

        if(isset($index)) {
            $this->process($index);
        } else {
            $files = $this->getKeys();
            
            foreach($files as $file) {
                $this->process($file);
            }
        }
        
        return $this->getErrors() ? false : true;
    }

    // ------------------------------------------------------------------------

    /**
     * Files::getStored
     *
     * @param string $offset Stored files offset
     *                       
     * @return array|\O2System\Spl\Iterators\ArrayIterator
     */
    public function getStored($offset = null)
    {
        if(isset($offset)) {
            return $this->stored[$offset];
        }
        
        return $this->stored;
    }
}
