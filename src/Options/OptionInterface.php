<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle\Options;

interface OptionInterface {

    /**
     * Returns an array of option key / value pairs
     * @return array
     */
    public static function getOptions(): array;

    /**
     * Returns the label for an option
     * @param string|int $k
     * @return string
     */
    public static function getOptionLabel(string|int $k): string;
}