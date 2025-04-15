<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Contao\Message;
use Doublespark\FaqGeneratorBundle\Model\FaqQuestionModel;
use Doublespark\FaqGeneratorBundle\Options\GenerationStatusOptions;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class BackendGenerateAnswersController {

    public function __construct(
        private ContaoFramework $framework,
        private RequestStack $requestStack
    ){}

    public function generateAnswersAction(DataContainer $dc): Response
    {
        $this->framework->initialize();

        /**
         * @var FaqQuestionModel $faqQuestionModel
         */
        $faqQuestionModel = $this->framework->getAdapter(FaqQuestionModel::class);

        $objQuestion = $faqQuestionModel->findByPk($dc->id);

        $request = $this->requestStack->getCurrentRequest();
        $message = $this->framework->getAdapter(Message::class);

        $rootPhrase = trim($dc->getCurrentRecord()['phrase'] ?? '');

        // Question exists and is complete, not started or failed
        if($objQuestion && in_array((int)$objQuestion->status, [GenerationStatusOptions::COMPLETE, GenerationStatusOptions::NOT_STARTED, GenerationStatusOptions::FAILED]))
        {
            $objQuestion->status = GenerationStatusOptions::REQUESTED;
            $objQuestion->save();

            $message->addConfirmation("Answer generation requested for '$rootPhrase', please check back in a few minutes.");
        }
        else
        {
            if($objQuestion)
            {
                $message->addError("Answer content is already being processed");
            }
            else
            {
                $message->addError("Question not found");
            }
        }

        return new RedirectResponse($this->getBackUrl($request));
    }

    private function getBackUrl(Request $request): string
    {
        return str_replace('&key='.$request->query->get('key'), '', $request->getRequestUri());
    }
}