<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\Input;

#[AsCallback(table: 'tl_ds_faq_question', target: 'config.onload')]
class SetQuestionTypeListener
{
    public function __invoke(DataContainer $dc): void
    {
        if (Input::get('act') != 'create')
        {
            return;
        }

        if((int)Input::get('pid') === 0)
        {
            $GLOBALS['TL_DCA']['tl_ds_faq_question']['fields']['type']['default'] = 'root';
        }
    }
}