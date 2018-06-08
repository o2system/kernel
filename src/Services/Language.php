<?php
/**
 * This file is part of the O2System PHP Framework package.
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
     * Language Packages
     *
     * @var array
     */
    protected $packages = [];

    /**
     * Active Locale
     *
     * @type string
     */
    protected $defaultLocale = 'en';

    /**
     * Active Ideom
     *
     * @type string
     */
    protected $defaultIdeom = 'US';

    /**
     * List of loaded language files
     *
     * @access  protected
     *
     * @var array
     */
    protected $isLoaded = [];

    /**
     * Languages Lines
     *
     * @var array
     */
    protected $lines = [];

    // ------------------------------------------------------------------------

    /**
     * Class Constructor
     *
     * @access  public
     */
    public function __construct()
    {
        $this->setFileDirName('Languages');
        $this->addFilePath(PATH_KERNEL);
    }

    // ------------------------------------------------------------------------

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

        return $this;
    }

    // ------------------------------------------------------------------------

    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    // ------------------------------------------------------------------------

    /**
     * Set Locale
     *
     * Set active language locale
     *
     * @param string $defaultLocale
     *
     * @access  public
     * @return  Language
     */
    public function setDefaultLocale($defaultLocale)
    {
        $this->defaultLocale = strtolower($defaultLocale);
        $this->defaultIdeom = strtoupper($defaultLocale);

        return $this;
    }

    // ------------------------------------------------------------------------

    public function getDefaultIdeom()
    {
        return $this->defaultIdeom;
    }

    // ------------------------------------------------------------------------

    /**
     * Set Ideom
     *
     * Set active language ideom
     *
     * @param   string $defaultIdeom
     *
     * @access  public
     * @return  Language
     */
    public function setDefaultIdeom($defaultIdeom)
    {
        $this->defaultIdeom = strtoupper($defaultIdeom);

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Load
     *
     * Load language file into collections
     *
     * @param string $filename
     *
     * @return static
     */
    public function loadFile($filename)
    {
        $filename = is_string($filename) ? [$filename] : $filename;
        $default = $this->getDefault();

        if (empty($filename)) {
            return $this;
        }

        foreach ($filename as $file) {
            if (is_file($file)) {
                $this->parseFile($file);
            } else {
                foreach ($this->filePaths as $filePath) {
                    $filePaths = [
                        $filePath . $default . DIRECTORY_SEPARATOR . $file . '.ini',
                        $filePath . dash($file) . '_' . $default . '.ini',
                        $filePath . dash($file) . '-' . $default . '.ini',
                        $filePath . dash($file) . '.ini',
                    ];

                    foreach ($filePaths as $filePath) {
                        if (is_file($filePath) AND ! in_array($filePath, $this->isLoaded)) {
                            $this->parseFile($filePath);
                            break;
                            break;
                        }
                    }
                }
            }
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    public function getDefault()
    {
        return implode('-', [$this->defaultLocale, $this->defaultIdeom]);
    }

    // ------------------------------------------------------------------------

    /**
     * Parse File
     *
     * Parse INI language file into collections
     *
     * @param string $filePath Language INI filePath
     */
    protected function parseFile($filePath)
    {
        $lines = parse_ini_file($filePath, true, INI_SCANNER_RAW);

        if ( ! empty($lines)) {
            $this->isLoaded[ pathinfo($filePath, PATHINFO_FILENAME) ] = $filePath;

            $this->lines = array_merge($this->lines, $lines);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Line
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

    public function isExists($localeIdeom)
    {
        return false;
    }

    // ------------------------------------------------------------------------

    /**
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