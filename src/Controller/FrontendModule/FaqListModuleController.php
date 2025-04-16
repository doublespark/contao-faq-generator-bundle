<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Contao\StringUtil;
use Doublespark\FaqGeneratorBundle\Model\FaqQuestionModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(category: 'miscellaneous', type: FaqListModuleController::TYPE, template: '@Contao/frontend_module/faq-list')]
class FaqListModuleController extends AbstractFrontendModuleController
{
    public const TYPE = 'fg-faq-list';

    public function __construct(private ContaoFramework $framework) {}

    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        /* @var FaqQuestionModel $faqQuestionModel */
        $faqQuestionModel = $this->framework->getAdapter(FaqQuestionModel::class);

        /* @var StringUtil $strUtilAdapter */
        $strUtilAdapter = $this->framework->getAdapter(StringUtil::class);

        $arrSections = $strUtilAdapter->deserialize($model->fg_faqSections);

        $objSections = $faqQuestionModel->findMultipleByIds($arrSections);

        if($objSections->count() < 0)
        {
            return new Response('');
        }

        $arrQuestions = [];

        foreach($objSections as $objSection)
        {
            if($objSection instanceof FaqQuestionModel)
            {
                if((int)$objSection->published !== 1)
                {
                    continue;
                }

                $arrChildren = $objSection->getChildren();

                if($model->fg_faqListMode === 'flat')
                {
                    foreach($arrChildren as $objQuestion)
                    {
                        $this->flattenChildren($objQuestion, $arrQuestions);
                    }

                    $this->flattenChildren($objSection, $arrQuestions);
                }
                else
                {
                    $arrQuestions = $this->nestedChildren($objSection);
                }
            }
        }

        $template->set('questions', $arrQuestions);

        return $template->getResponse();
    }

    protected function nestedChildren(FaqQuestionModel $objQuestion, int|null $level=null): array
    {
        $children = [];

        if(is_null($level))
        {
            $level = 1;
        }

        if(isset($objQuestion->children) && is_array($objQuestion->children))
        {
            foreach($objQuestion->children as $objQuestionChild)
            {
                $children[$objQuestionChild->id] = [
                    'id' => $objQuestionChild->id,
                    'question' => $objQuestionChild->question,
                    'answer' => $objQuestionChild->answer,
                    'level' => $level,
                    'children' => $this->nestedChildren($objQuestionChild, $level+1),
                ];
            }
        }

        return $children;
    }

    protected function flattenChildren(FaqQuestionModel $objQuestion, &$arrQuestions): void
    {
        $arrQuestions[$objQuestion->id] = [
            'id' => $objQuestion->id,
            'question' => $objQuestion->question,
            'answer' => $objQuestion->answer,
            'level' => 1,
            'children' => []
        ];

        if(isset($objQuestion->children) && is_array($objQuestion->children))
        {
            foreach ($objQuestion->children as $childQuestion)
            {
                $this->flattenChildren($childQuestion, $arrQuestions);
            }
        }
    }
}