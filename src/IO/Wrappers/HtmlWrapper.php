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
    public function toHTML(
        bool $pretty = true,
        ?int $limit = null,
        ?int $offset = 0,
        ?string $class = null,
        ?string $id = null
    ): string {
        return HTML::convertDataFrameToHtml(df: $this, pretty: $pretty, limit: $limit, offset: $offset, class: $class, id: $id);
    }
}
