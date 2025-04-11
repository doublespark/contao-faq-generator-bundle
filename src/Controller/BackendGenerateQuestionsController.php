<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle\Controller;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Contao\Message;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Environment;

class BackendGenerateQuestionsController {

    public function __construct(
        private ContaoFramework $framework,
        private RequestStack $requestStack,
        private Environment $twig,
        private Connection $connection,
        private CsrfTokenManagerInterface $csrfTokenManager
    ){}

    public function generateQuestionsAction(DataContainer $dc): Response
    {
        $this->framework->initialize();

        $pid = (int)$dc->getCurrentRecord()['pid'] ?? 0;
        $rootPhrase = trim($dc->getCurrentRecord()['phrase'] ?? '');

        $request = $this->requestStack->getCurrentRequest();

        if($pid !== 0)
        {
            $message = $this->framework->getAdapter(Message::class);
            $message->addError('This action can only be performed on root phrases.');

            return new RedirectResponse($this->getBackUrl($request));
        }

        if($request->request->get('FORM_SUBMIT') === $this->getFormId($request))
        {
            if($rootPhrase)
            {
                $this->fetchQuestions((int)$dc->id, $rootPhrase);
            }
        }

        return $this->generateResponse([
            'requestToken' => $this->csrfTokenManager->getDefaultTokenValue(),
            'formId' => $this->getFormId($request),
            'backHref' => $this->getBackUrl($request),
            'rootPhrase' => $rootPhrase,
        ]);
    }

    private function generateResponse(array $arrTemplateVars): Response
    {
        return new Response($this->twig->render('@Contao/backend/generate-questions.html.twig', $arrTemplateVars));
    }

    private function getFormId(Request $request): string
    {
        return 'tl_ds_faq_question_'.$request->query->get('key');
    }

    private function getBackUrl(Request $request): string
    {
        return str_replace('&key='.$request->query->get('key'), '', $request->getRequestUri());
    }

    private function fetchQuestions(int $pid, string $phrase): void
    {
        $config = $this->framework->getAdapter(Config::class);

        $apiKey = $config->get('fg_alsoAskedApiKey') ?? '';
        $apiEnv = $config->get('fg_alsoAskedApiEnv') ?? '';

        $uri = $apiEnv === 'production' ? 'https://alsoaskedapi.com/v1' : 'https://sandbox.alsoaskedapi.com/v1';

        $curl = curl_init();

        $arrOptions = [
            'terms' => [$phrase],
            'language' => 'en',
            'region' => 'gb',
            'depth'=> 2
        ];

        curl_setopt_array($curl, [
              CURLOPT_URL => "$uri/search",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_HTTPHEADER => [
                  "Content-Type: application/json",
                  "Accept: application/json",
                  "X-Api-Key: $apiKey"
              ],
              CURLOPT_POSTFIELDS => json_encode($arrOptions)
        ]);

        $response = curl_exec($curl);

        $arrResponse = json_decode($response,true);

        if(isset($arrResponse['queries']) && is_array($arrResponse['queries']))
        {
            foreach($arrResponse['queries'] as $query)
            {
                foreach($query['results'] as $result)
                {
                    $this->saveResult($result, $pid);
                }
            }
        }
    }

    function saveResult(array $query, int $pid): void
    {
        $this->connection->insert('tl_ds_faq_question', [
            'pid' => $pid,
            'tstamp' => time(),
            'question' => $query['question'],
            'published' => 1,
            'type' => 'question',
        ]);

        $pid = (int)$this->connection->lastInsertId();

        foreach ($query['results'] as $childResult) {
            $this->saveResult($childResult, $pid);
        }
    }
}