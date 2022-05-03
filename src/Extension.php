<?php

declare(strict_types=1);

namespace Positron48\CommentExtension;

use Bolt\Extension\BaseExtension;

class Extension extends BaseExtension
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Simple comments';
    }
}