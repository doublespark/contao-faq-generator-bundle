<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle\Options;

class GenerationStatusOptions implements OptionInterface
{
    // Option constants
    const NOT_STARTED = 0;
    const REQUESTED = 1;
    const WORKING = 2;
    const COMPLETE = 3;
    const FAILED = 4;

    /**
     * Returns an array of option key / value pairs
     * @return array
     */
    public static function getOptions(): array
    {
        return array(
            static::NOT_STARTED => 'Not started',
            static::REQUESTED => 'Requested',
            static::WORKING => 'Working',
            static::COMPLETE => 'Complete',
            static::FAILED => 'Failed'
        );
    }

    /**
     * Returns the label for an option
     * @param string|int $k
     * @return string
     */
    public static function getOptionLabel(string|int $k): string
    {
        $arrOptions = static::getOptions();

        if(isset($arrOptions[$k]))
        {
            return $arrOptions[$k];
        }

        return '';
    }
}