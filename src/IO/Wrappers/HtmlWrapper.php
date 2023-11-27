<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO\Wrappers;

use MammothPHP\WoollyM\IO\HTML;

trait HtmlWrapper
{
    /**
     * Outputs a DataFrame to an HTML string.
     * @throws \MammothPHP\WoollyM\Exceptions\NotYetImplementedException
     */
    public function toHTML($options = []): string
    {
        $html = new HTML($this->toArray());

        return $html->assembleTable($options);
    }
}
