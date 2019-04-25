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

namespace O2System\Kernel\Services;

// ------------------------------------------------------------------------

use O2System\Spl\Traits\Collectors\FilePathCollectorTrait;
use Traversable;

/**
 * O2System Language
 *
 * This class is a collection, loader and manage of default languages data from O2System and User Applications.
 *
 * @package O2System\Kernel
 */
class Language implements \IteratorAggregate
{
    use FilePathCollectorTrait;

    /**
     * Language::$defaultLocale
     *
     * Default language locale.
     *
     * @var string
     */
    protected $defaultLocale = 'en';

    /**
     * Language::$defaultIdeom
     *
     * Default language ideom.
     *
     * @var string
     */
    protected $defaultIdeom = 'US';

    /**
     * Language::$loaded
     *
     * List of loaded language files.
     *
     * @var array
     */
    protected $loaded = [];

    /**
     * Language::$lines
     *
     * Languages Lines
     *
     * @var array
     */
    protected $lines = [];

    // ------------------------------------------------------------------------

    /**
     * Language::__construct
     */
    public function __construct()
    {
        $this->setFileDirName('Languages');
        $this->addFilePath(PATH_KERNEL);
    }

    // ------------------------------------------------------------------------

    /**
     * Language::setDefault
     *
     * Sets default language.
     *
     * @param string $default
     *
     * @return static
     */
    public function setDefault($default)
    {
        $xDefault = explode('-', $default);

        if (count($xDefault) == 2) {
            list($locale, $ideom) = $xDefault;
            $this->setDefaultLocale($locale);
            $this->setDefaultIdeom($ideom);
        } elseif (count($xDefault) == 1) {
            $this->setDefaultLocale(reset($xDefault));
        }

        if (class_exists('O2System\Framework', false) or class_exists('\O2System\Reactor', false)) {
            if (services()->has('session')) {
                session()->set('language', $this->getDefault());
            }

            if (count($this->loaded)) {
                foreach ($this->loaded as $fileIndex => $filePath) {
                    unset($this->loaded[ $fileIndex ]);
                    $this->loadFile($fileIndex);
                }
            }
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Language::getDefault
     *
     * Get default language with ideom
     *
     * @return string
     */
    public function getDefault()
    {
        return implode('-', [$this->defaultLocale, $this->defaultIdeom]);
    }

    // ------------------------------------------------------------------------

    /**
     * Language::load
     *
     * Load language file into collections
     *
     * @param string|array $filenames
     *
     * @return static
     */
    public function loadFile($filenames)
    {
        $filenames = is_string($filenames) ? [$filenames] : $filenames;

        if (empty($filenames)) {
            return $this;
        }

        foreach ($filenames as $filename) {
            $filename = dash($filename);
            if ( ! $this->isLoaded($filename)) {
                if (is_file($filename)) {
                    $this->parseFile($filename);
                    break;
                } elseif (false !== ($filePath = $this->findFile($filename))) {
                    $this->parseFile($filePath);
                    break;
                }
            }
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Language::isLoaded
     *
     * Checks if the language file has been loaded.
     *
     * @param string $filePath
     *
     * @return bool
     */
    public function isLoaded($filePath)
    {
        return array_key_exists($this->getFileIndex($filePath), $this->loaded);
    }

    // ------------------------------------------------------------------------

    /**
     * Language::getFileIndex
     *
     * Gets filepath index key.
     *
     * @param string $filePath
     *
     * @return string
     */
    protected function getFileIndex($filePath)
    {
        $fileIndex = pathinfo($filePath, PATHINFO_FILENAME);
        $fileIndex = str_replace('_' . $this->getDefault(), '', $fileIndex);

        return $fileIndex;
    }

    // ------------------------------------------------------------------------

    /**
     * Language::parseFile
     *
     * Parse INI language file into collections
     *
     * @param string $filePath Language INI filePath
     *
     * @return bool
     */
    protected function parseFile($filePath)
    {
        $lines = parse_ini_file($filePath, true, INI_SCANNER_RAW);

        if (is_array($lines)) {
            if (count($lines)) {
                $this->loaded[ $this->getFileIndex($filePath) ] = $filePath;

                $this->lines = array_merge($this->lines, $lines);

                return true;
            }
        }

        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Language::findFile
     *
     * Find language file.
     *
     * @param string $filename
     *
     * @return string|bool Returns FALSE if failed.
     */
    protected function findFile($filename)
    {
        $default = $this->getDefault();

        foreach ($this->filePaths as $filePath) {
            $filePaths = [
                $filePath . $default . DIRECTORY_SEPARATOR . $filename . '.ini',
                $filePath . $filename . '_' . $default . '.ini',
                $filePath . $filename . '-' . $default . '.ini',
                $filePath . $filename . '.ini',
            ];

            foreach ($filePaths as $filePath) {
                if (is_file($filePath) AND ! in_array($filePath, $this->loaded)) {
                    return $filePath;
                    break;
                    break;
                }
            }

            unset($filePath);
        }

        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Language::getDefaultLocale
     *
     * Gets default language locale.
     *
     * @return string
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    // ------------------------------------------------------------------------

    /**
     * Language::setDefaultLocale
     *
     * Sets default language locale.
     *
     * @param string $defaultLocale
     *
     * @return  static
     */
    public function setDefaultLocale($defaultLocale)
    {
        $this->defaultLocale = strtolower($defaultLocale);
        $this->defaultIdeom = strtoupper($defaultLocale);

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Language::getDefaultIdeom
     *
     * Gets default language ideom
     *
     * @return string
     */
    public function getDefaultIdeom()
    {
        return $this->defaultIdeom;
    }

    // ------------------------------------------------------------------------

    /**
     * Langauge::setDefaultIdeom
     *
     * Sets default language ideom
     *
     * @param   string $defaultIdeom
     *
     * @return  static
     */
    public function setDefaultIdeom($defaultIdeom)
    {
        $this->defaultIdeom = strtoupper($defaultIdeom);

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Language::getLine
     *
     * Parse single language line of text
     *
     * @param string $line    Language line key
     * @param array  $context Language line context
     *
     * @return mixed|null
     */
    public function getLine($line, array $context = [])
    {
        $lineOffset = strtoupper($line);

        if (empty($context)) {
            $lineContent = isset($this->lines[ $lineOffset ]) ? $this->lines[ $lineOffset ] : $line;
        } else {
            $line = isset($this->lines[ $lineOffset ]) ? $this->lines[ $lineOffset ] : $line;
            array_unshift($context, $line);

            $lineContent = @call_user_func_array('sprintf', $context);
        }

        return str_replace(['PHP_EOL', 'PHP_EOL '], PHP_EOL, $lineContent);
    }

    // ------------------------------------------------------------------------

    /**
     * Language::getIterator
     *
     * Retrieve an external iterator
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     *        <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->lines);
    }
}