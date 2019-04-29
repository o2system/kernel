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

namespace O2System\Kernel\Cli\Writers;

// ------------------------------------------------------------------------

/**
 * Class Table
 *
 * Table generator for PHP command line interface (cli).
 * Based on LucidFrame\Console\ConsoleTable class.
 *
 * @author  Sithu K. <cithukyaw@gmail.com>
 *          Steeve Andrian Salim
 *
 * @package O2System\Kernel\Cli\Writers
 */
class Table
{
    /**
     * Table::HEADER_INDEX
     *
     * @var int
     */
    const HEADER_INDEX = -1;
    /**
     * Table::$isShowBorder
     *
     * Table show border flag.
     *
     * @var bool
     */
    public $isShowBorder = true;
    /**
     * Table::$rows
     *
     * Array of table rows.
     *
     * @var array
     */
    protected $rows = [];
    /**
     * Table::$padding
     *
     * Numbers of table padding.
     *
     * @var int
     */
    protected $padding = 1;

    /**
     * Table::$leftMargin
     *
     * Numbers of table left margin.
     *
     * @var int
     */
    protected $leftMargin = 0;

    /**
     * Table::$rowIndex
     *
     * Table row indexing numbers.
     *
     * @var int
     */
    private $rowIndex = -1;

    /**
     * Table::$columnsWidths
     *
     * Cache list of table column widths.
     *
     * @var array
     */
    private $columnWidths = [];

    // ------------------------------------------------------------------------

    /**
     * Table::setHeaders
     *
     * Adds the headers for the columns
     *
     * @param  array $headers of header cell content
     *
     * @return static
     */
    public function setHeaders(array $headers)
    {
        $this->rows[ self::HEADER_INDEX ] = $headers;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Table::addHeader
     *
     * Adds a column to the table header
     *
     * @param mixed $content Table column cell content.
     *
     * @return static
     */
    public function addHeader($content = '')
    {
        $this->rows[ self::HEADER_INDEX ][] = $content;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Table::getHeaders
     *
     * Get the row of header.
     *
     * @return mixed
     */
    public function getHeaders()
    {
        return isset($this->rows[ self::HEADER_INDEX ])
            ? $this->rows[ self::HEADER_INDEX ]
            : null;
    }

    // ------------------------------------------------------------------------

    /**
     * Table::addRow
     *
     * Adds a row to the table.
     *
     * @param  array $data The row data to add
     *
     * @return static
     */
    public function addRow($data = null)
    {
        $this->rowIndex++;
        if (is_array($data)) {
            foreach ($data as $column => $content) {
                $this->rows[ $this->rowIndex ][ $column ] = $content;
            }
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Adds a column to the table
     *
     * @param  mixed $content     The data of the column
     * @param  int   $columnIndex The column index to populate
     * @param  int   $rowIndex    The row index, if the row isn't starting from zero, specify it here.
     *
     * @return static
     */
    public function addColumn($content, $columnIndex = null, $rowIndex = null)
    {
        $rowIndex = $rowIndex === null
            ? $this->rowIndex
            : $rowIndex;
        if ($columnIndex === null) {
            $columnIndex = isset($this->rows[ $rowIndex ])
                ? count($this->rows[ $rowIndex ])
                : 0;
        }
        $this->rows[ $rowIndex ][ $columnIndex ] = $content;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Table::setPadding
     *
     * Set padding for each cell.
     *
     * @param  int $numbers The numbers of padding, the default numbers is 1.
     *
     * @return static
     */
    public function setPadding($numbers = 1)
    {
        $this->padding = $numbers;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Table::setLeftMargin
     *
     * Set left indentation for the table.
     *
     * @param  int $numbers The numbers of left margin, the default numbers is 0.
     *
     * @return static
     */
    public function setLeftMargin($numbers = 0)
    {
        $this->leftMargin = $numbers;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Table::__toString
     *
     * Implementation __toString magic method so that when the class is converted to a string
     * automatically performs the rendering process.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    // ------------------------------------------------------------------------

    /**
     * Table::render
     *
     * Render the table.
     *
     * @return string
     */
    public function render()
    {
        $this->calculateColumnWidth();
        $output = $this->isShowBorder
            ? $this->renderBorder()
            : '';
        foreach ($this->rows as $rowIndex => $row) {
            foreach ($row as $cellIndex => $cell) {
                $output .= $this->renderCell($cellIndex, $row);
            }
            $output .= "\n";
            if ($rowIndex === self::HEADER_INDEX) {
                $output .= $this->renderBorder();
            }
        }
        $output .= $this->isShowBorder
            ? $this->renderBorder()
            : '';

        return $output;
    }

    // ------------------------------------------------------------------------

    /**
     * Table::calculateColumnWidth
     *
     * Calculate maximum width of each column
     *
     * @return array
     */
    private function calculateColumnWidth()
    {
        foreach ($this->rows as $rowIndex => $row) {
            foreach ($row as $columnIndex => $column) {
                if ( ! isset($this->columnWidths[ $columnIndex ])) {
                    $this->columnWidths[ $columnIndex ] = strlen($column);
                } else {
                    if (strlen($column) > $this->columnWidths[ $columnIndex ]) {
                        $this->columnWidths[ $columnIndex ] = strlen($column);
                    }
                }
            }
        }

        return $this->columnWidths;
    }

    // ------------------------------------------------------------------------

    /**
     * Table::renderBorder
     *
     * Render the table border.
     *
     * @return string
     */
    private function renderBorder()
    {
        $output = '';
        $columnCount = count($this->rows[ 0 ]);
        for ($col = 0; $col < $columnCount; $col++) {
            $output .= $this->renderCell($col);
        }
        if ($this->isShowBorder) {
            $output .= '+';
        }
        $output .= "\n";

        return $output;
    }

    // ------------------------------------------------------------------------

    /**
     * Table::renderCell
     *
     * Render the table cell content.
     *
     * @param int   $index The column index.
     * @param array $row   The table row.
     *
     * @return string
     */
    private function renderCell($index, $row = null)
    {
        $cell = $row
            ? $row[ $index ]
            : '-';
        $width = $this->columnWidths[ $index ];
        $pad = $row
            ? $width - strlen($cell)
            : $width;
        $padding = str_repeat(
            $row
                ? ' '
                : '-',
            $this->padding
        );
        $output = '';
        if ($index === 0) {
            $output .= str_repeat(' ', $this->leftMargin);
        }
        if ($this->isShowBorder) {
            $output .= $row
                ? '|'
                : '+';
        }
        $output .= $padding; # left padding
        $output .= str_pad(
            $cell,
            $width,
            $row
                ? ' '
                : '-'
        ); # cell content
        $output .= $padding; # right padding

        $row = is_array($row) ? $row : [];
        if ($index == count($row) - 1 && $this->isShowBorder) {
            $output .= $row
                ? '|'
                : '+';
        }

        return $output;
    }
}