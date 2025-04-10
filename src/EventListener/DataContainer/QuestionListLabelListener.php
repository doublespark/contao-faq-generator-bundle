<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;

#[AsCallback(table: 'tl_ds_faq_question', target: 'list.label.label')]
class QuestionListLabelListener
{
    public function __invoke(array $row, string $label, DataContainer $dc, string $imageAttribute = '', bool $returnImage = false, bool|null $isProtected = null): string
    {
        if((int)$row['pid'] === 0)
        {
            return "<span class=\"icn icn-phrase\"></span>".$row['phrase'];
        }

        return "<span class=\"icn icn-question\"></span>".$row['question'];
    }
}