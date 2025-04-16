<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle\Schema;

class FaqSchemaGeneratorFactory {
    public function create(): FaqSchemaGenerator {
        return new FaqSchemaGenerator();
    }
}