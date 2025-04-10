<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class FaqGeneratorBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
