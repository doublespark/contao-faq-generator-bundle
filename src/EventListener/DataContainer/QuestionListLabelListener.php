<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doublespark\FaqGeneratorBundle\Options\GenerationStatusOptions;

#[AsCallback(table: 'tl_ds_faq_question', target: 'list.label.label')]
class QuestionListLabelListener
{
    public function __invoke(array $row, string $label, DataContainer $dc, string $imageAttribute = '', bool $returnImage = false, bool|null $isProtected = null): string
    {
        if((int)$row['pid'] === 0)
        {
            $status = GenerationStatusOptions::getOptionLabel($row['status']);

            return "<span class=\"icn icn-phrase\"></span>".$row['phrase']."<span class=\"faq-sts\">AI Content: ".$status."</span>";
        }

        return "<span class=\"icn icn-question\"></span>".$row['question'];
    }
}