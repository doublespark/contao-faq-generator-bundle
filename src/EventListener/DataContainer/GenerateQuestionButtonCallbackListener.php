<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle\EventListener\DataContainer;

use Contao\Backend;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\StringUtil;

#[AsCallback(table: 'tl_ds_faq_question', target: 'list.operations.generateQuestions.button')]
class GenerateQuestionButtonCallbackListener
{
    public function __invoke(array $row, ?string $href, string $label, string $title, ?string $icon, string $attributes): string
    {
        if((int)$row['pid'] === 0)
        {
            return sprintf(
                '<a href="%s" title="%s"%s>%s</a> ',
                Backend::addToUrl($href . '&amp;id=' . $row['id']),
                StringUtil::specialchars($title),
                $attributes,
                '[Q] '
            );
        }

        return '';
    }
}