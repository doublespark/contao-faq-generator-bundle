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
        private Connection $connection
    ){}

    public function generateQuestionsAction(DataContainer $dc): Response
    {
        $this->framework->initialize();

        $pid = (int)$dc->getCurrentRecord()['pid'] ?? 0;
        $rootPhrase = trim($dc->getCurrentRecord()['phrase'] ?? '');

        $request = $this->requestStack->getCurrentRequest();

        /**
         * @var Message $message
         */
        $message = $this->framework->getAdapter(Message::class);

        if($pid !== 0)
        {
            $message->addError('This action can only be performed on root phrases.');

            return new RedirectResponse($this->getBackUrl($request));
        }

        if($rootPhrase)
        {
            try {

                $this->fetchQuestions((int)$dc->id, $rootPhrase);
                $message->addConfirmation('Successfully fetched questions for: ' . $rootPhrase);

            } catch (\Exception $e) {
                $message->addError($e->getMessage());
            }
        }
        else
        {
            $message->addError('The root phrase/question cannot be empty');
        }

        return new RedirectResponse($this->getBackUrl($request));
    }

    private function getBackUrl(Request $request): string
    {
        return str_replace('&key='.$request->query->get('key'), '', $request->getRequestUri());
    }

    private function fetchQuestions(int $pid, string $phrase): void
    {
        $config = $this->framework->getAdapter(Config::class);

        $apiKey = $config->get('fg_alsoAskedApiKey') ?? '';
        $apiEnv = $config->get('fg_alsoAskedApiEnv') ?? 'production';

        if(empty($apiKey))
        {
            throw new \Exception('API Key is required, please set the AlsoAsked API key under Settings.');
        }

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

        if(curl_errno($curl))
        {
            $errorMsg = curl_error($curl);

            throw new \Exception('Could not fetch questions: '.$errorMsg);
        }

        $arrResponse = json_decode($response,true);

        $arrQuestions = [];

        if(isset($arrResponse['queries']) && is_array($arrResponse['queries']))
        {
            foreach($arrResponse['queries'] as $query)
            {
                foreach($query['results'] as $result)
                {
                    $question = $result['question'];

                    // Ignore any rows that do not have sub-results
                    if(count($result['results']) === 0)
                    {
                        continue;
                    }

                    // Skip any duplicates
                    if(!in_array($question, $arrQuestions))
                    {
                        $arrQuestions[] = $result['question'];
                        $this->saveResult($result, $pid);
                    }
                }
            }
        }
        else
        {
            if(isset($arrResponse['message']))
            {
                throw new \Exception('Could not fetch questions: '.$arrResponse['message']);
            }
            elseif(isset($arrResponse['status']))
            {
                throw new \Exception('Could not fetch questions: '.$arrResponse['status']);
            }
            else
            {
                throw new \Exception('Unexpected response from AlsoAsked');
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