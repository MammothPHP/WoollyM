<?php

declare(strict_types=1);

/**
 * Contains the HTML class.
 * @package   DataFrame
 * @author    Howard Gehring <hwgehring@gmail.com>
 * @copyright 2015 Howard Gehring <hwgehring@gmail.com>
 * @license   https://github.com/HWGehring/CondorcetPHP\Oliphant/blob/master/LICENSE BSD-3-Clause
 * @link      https://github.com/HWGehring/CondorcetPHP\Oliphant
 * @since     0.1.0
 */

namespace CondorcetPHP\Oliphant\IO;

use CondorcetPHP\Oliphant\Exceptions\NotYetImplementedException;
use Gajus\Dindent\Indenter;

/**
 * The HTML class contains implementation details for transforming two-dimensional arrays into HTML tables.
 * @package   CondorcetPHP\Oliphant\IO
 * @author    Howard Gehring <hwgehring@gmail.com>
 * @copyright 2015 Howard Gehring <hwgehring@gmail.com>
 * @license   https://github.com/HWGehring/CondorcetPHP\Oliphant/blob/master/LICENSE BSD-3-Clause
 * @link      https://github.com/HWGehring/CondorcetPHP\Oliphant
 * @since     0.1.0
 */
final class HTML
{
    private $defaultOptions = [
        'pretty' => false,
        'class' => null,
        'id' => null,
        'quote' => "'",
        'datatable' => null,
        'colorColumns' => null,
        'limit' => 5000,
        'offset' => 0,
    ];

    public function __construct(public readonly array $data)
    {
    }

    /**
     * Assembles a two-dimensional array as an HTML table, where row element keys are header/footer columns,
     * and row element values form the individual cells of the table.
     * Options include:
     *      pretty:    Will "prettify" the rendered HTML (default: false)
     *      class:     Specify the CSS class of the HTML table (default: null)
     *      id:        Specify the CSS id of the HTML table (default: null)
     *      quote:     Specify the character to use for quoting table CSS class and/or CSS id (default: ')
     *      datatable: Options for rendering the table as a DataTable (@see: http://datatables.net) (default: null)
     * @param  array $options
     * @return string
     * @throws NotYetImplementedException
     * @throws \CondorcetPHP\Oliphant\Exceptions\UnknownOptionException
     * @since  0.1.0
     */
    public function assembleTable(array $options): string
    {
        $data = $this->data;
        $options = Options::setDefaultOptions($options, $this->defaultOptions);
        $prettyOpt = $options['pretty'];
        $classOpt = $options['class'];
        $idOpt = $options['id'];
        $quoteOpt = $options['quote'];
        $datatableOpt = $options['datatable'];

        $colorColumnsOpt = $options['colorColumns'];
        $limitOpt = $options['limit'];
        $offsetOpt = $options['offset'];

        $columns = current($data);
        $columns = array_keys($columns);

        // Create a uuid HTML id if user wants a datatable but hasn't provided an HTML id
        if ($datatableOpt !== null && $idOpt === null) {
            $idOpt = uniqid();
        }

        $table = $this->assembleOpeningTableTag($classOpt, $idOpt, $quoteOpt);
        $fnTable = $this->fnWrapText($table, '</table>');
        $fnTHead = $this->fnWrapText('<thead>', '</thead>');
        $fnTFoot = $this->fnWrapText('<tfoot>', '</tfoot>');
        $fnTBody = $this->fnWrapText('<tbody>', '</tbody>');

        $fnTRTH = $this->fnWrapArray('<tr><th>', '</th><th>', '</th></tr>');
        $fnTR = $this->fnWrapText('<tr>', '</tr>');

        $columns = $fnTRTH($columns);

        if ($offsetOpt > 0 && $offsetOpt < \count($data)) {
            $data = \array_slice($data, $offsetOpt);
        }

        if ($limitOpt > 0 && $limitOpt < \count($data)) {
            $data = \array_slice($data, 0, $limitOpt);
        }

        foreach ($data as &$row) {
            foreach ($row as $i => &$col) {
                $opt = $colorColumnsOpt[$i] ?? '';
                $opt = $opt !== '' ? " bgcolor='{$opt}'" : '';
                $col = "<td{$opt}>{$col}</td>";
            }
            $row = $fnTR($row);
        }

        $data = $fnTable(
            $fnTHead($columns).
            $fnTFoot($columns).
            $fnTBody($data)
        );

        if ($datatableOpt !== null && $datatableOpt !== false) {
            $data .= $this->assembleDataTableScript($datatableOpt, $idOpt, $quoteOpt);
        }

        if ($prettyOpt === true) {
            $indenter = new Indenter;
            $data = $indenter->indent($data);
        }

        return $data;
    }

    /**
     * Assembles the <table> tag with CSS class, CSS id, and/or quote options provided.
     * @internal
     * @param  $class
     * @param  $id
     * @param  $quote
     * @return string
     * @return string
     * @since  0.1.1
     */
    private function assembleOpeningTableTag($class, $id, $quote)
    {

        $fnQuoted = $this->fnWrapText($quote, $quote);

        if ($class !== null) {
            $class = ' class='.$fnQuoted($class);
        }

        if ($id !== null) {
            $id = ' id='.$fnQuoted($id);
        }

        return '<table'.$class.$id.'>';
    }

    /**
     * Assembles DataTable JavaScript for the rendered HTML table.
     * @param  $datatableOpt string JSON options for passing into the DataTable. It is the responsibility of the user
     * to create these strings as I do not wish to impose any particular JSON parser on the result.
     * @param  $idOpt
     * @param  $quoteOpt
     * @return string
     * @since  0.1.1
     */
    private function assembleDataTableScript($datatableOpt, $idOpt, $quoteOpt)
    {
        if ($datatableOpt === true) {
            $datatableOpt = '';
        }

        $fnScript = $this->fnWrapText('<script>', '</script>');
        $fnDocumentReady = $this->fnWrapText('$(document).ready(function() {', '});');
        $fnQuoted = $this->fnWrapText($quoteOpt, $quoteOpt);

        $datatableID = $fnQuoted('#'.$idOpt);
        $jQueryFunction = $fnDocumentReady('$('.$datatableID.').DataTable('.$datatableOpt.');');

        return $fnScript($jQueryFunction);
    }

    /**
     * Returns a function which implodes and wraps an array around the specified HTML tags.
     * @param  $leftTag
     * @param  $implodeTag
     * @param  $rightTag
     * @return \Closure
     * @since  0.1.0
     */
    private function fnWrapArray($leftTag, $implodeTag, $rightTag)
    {
        return function (array $data) use ($leftTag, $implodeTag, $rightTag) {
            $wrap = $this->fnWrapText($leftTag, $rightTag);

            return $wrap(implode($implodeTag, $data));
        };
    }

    /**
     * Returns a function which wraps a string or an array around the specified HTML tags.
     * @param  $leftTag
     * @param  $rightTag
     * @return \Closure
     * @since  0.1.0
     */
    private function fnWrapText($leftTag, $rightTag)
    {
        return static function ($data) use ($leftTag, $rightTag) {
            if (\is_array($data) === true) {
                $data = implode('', $data);
            }

            return $leftTag.$data.$rightTag;
        };
    }
}
