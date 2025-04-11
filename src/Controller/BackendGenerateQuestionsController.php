<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Contao\Message;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class BackendGenerateQuestionsController {

    public function __construct(
        private ContaoFramework $framework,
        private RequestStack $requestStack,
        private Environment $twig
    ){}

    public function generateQuestionsAction(DataContainer $dc): Response
    {
        $this->framework->initialize();

        $pid = (int)$dc->getCurrentRecord()['pid'] ?? 0;

        if($pid !== 0)
        {
            $message = $this->framework->getAdapter(Message::class);
            $message->addError('This action can only be performed on root phrases.');

            return new RedirectResponse($this->getBackUrl($this->requestStack->getCurrentRequest()));
        }

        return $this->generateResponse([
            'backHref' => $this->getBackUrl($this->requestStack->getCurrentRequest()),
            'rootPhrase' => $dc->getCurrentRecord()['phrase'] ?? '',
        ]);
    }

    private function generateResponse(array $arrTemplateVars): Response
    {
        return new Response($this->twig->render('@Contao/backend/generate-questions.html.twig', $arrTemplateVars));
    }

    private function getBackUrl(Request $request): string
    {
        return str_replace('&key='.$request->query->get('key'), '', $request->getRequestUri());
    }
}