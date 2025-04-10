<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;

#[AsCallback(table: 'tl_ds_faq_question', target: 'fields.type.options')]
class QuestionTypeOptionsListener
{
    public function __invoke(DataContainer $dc): array
    {
        $pid = $dc->getCurrentRecord()['pid'] ?? 0;

        if($pid === 0)
        {
            return [
                'root' => 'Root question'
            ];
        }

        return [
            'question' => 'Question'
        ];
    }
}