<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle\Generator;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;

class AnswerGenerator {

    public function __construct(private ContaoFramework $framework){}

    public function generate(array $arrQuestions): AnswerSet
    {
        return $this->getResponse($arrQuestions);
    }

    public function getResponse(array $arrQuestions): AnswerSet
    {
        $config = $this->framework->getAdapter(Config::class);

        $apiKey = $config->get('fg_openAiApiKey') ?? '';

        $curl = curl_init();

        $input = implode("\n", $arrQuestions);

        $arrBody = [
            'model' => 'gpt-4o',
            'instructions' => 'Answer questions for a website FAQ section. Format answers as markdown. One answer per message.',
            'input' => $input,
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => 'faq_set',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'question' => ['type' => 'string'],
                            'answer' => ['type' => 'string'],
                        ],
                        'required' => ['question', 'answer'],
                        'additionalProperties' => false,
                    ]
                ]
            ]
        ];

        curl_setopt_array($curl, [
              CURLOPT_URL => "https://api.openai.com/v1/responses",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_HTTPHEADER => [
                  "Content-Type: application/json",
                  "Accept: application/json",
                  "Authorization: Bearer $apiKey"
              ],
              CURLOPT_POSTFIELDS => json_encode($arrBody)
        ]);

        $response = curl_exec($curl);

        if(curl_errno($curl))
        {
            $errorMsg = curl_error($curl);
            throw new \Exception('Could not fetch answers: '.$errorMsg);
        }

        $arrResponse = json_decode($response,true);

        $arrResult = [];

        if(isset($arrResponse['status']) && $arrResponse['status'] == 'completed')
        {
            foreach($arrResponse['output'] as $output)
            {
                $questionAnswer = json_decode($output['content'][0]['text'], true);
                $arrResult[] = new Answer($questionAnswer['question'], $questionAnswer['answer']);
            }

            return new AnswerSet($arrResult);
        }
        else
        {
            throw new \Exception('Could not fetch answer content.');
        }
    }
}