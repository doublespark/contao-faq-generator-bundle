<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Doublespark\FaqGeneratorBundle\Model\FaqQuestionModel;

#[AsCallback(table: 'tl_module', target: 'fields.fg_faqSections.options')]
class FaqSectionOptionsCallbackListener {

    public function __construct(private ContaoFramework $framework) {}

    public function __invoke(DataContainer $dc): array
    {
        /**
         * @var FaqQuestionModel $faqQuestionModel
         */
        $faqQuestionModel = $this->framework->getAdapter(FaqQuestionModel::class);

        $objSections = $faqQuestionModel->findBy('pid', 0);

        $arrOptions = [];

        if($objSections)
        {
            foreach($objSections as $objSection)
            {
                $arrOptions[$objSection->id] = $objSection->phrase;
            }
        }

        return $arrOptions;
    }

}