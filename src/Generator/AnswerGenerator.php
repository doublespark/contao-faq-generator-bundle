<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle\Generator;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Doublespark\FaqGeneratorBundle\Csv\CsvService;

class AnswerGenerator {

    public function __construct(
        private ContaoFramework $framework,
        private CsvService $csvService
    ){}

    public function generate(QuestionSet $questionSet): QuestionSet
    {
        $csv = $this->csvService->questionSetToCsv($questionSet);

        $csv = $this->getResponse($csv);

        $arrQuestions = $this->csvService->csvToArray($csv);

        foreach($arrQuestions as $question)
        {
            $questionSet->getQuestion((int)$question['ID'])?->setAnswer($question['Answer']);
        }

        return $questionSet;
    }

    protected function getResponse(string $questionsCsv): string
    {
        $config = $this->framework->getAdapter(Config::class);

        $apiKey = $config->get('fg_openAiApiKey') ?? '';
        $model = $config->get('fg_openAiApiModel') ?? 'gpt-4o-mini';

        $curl = curl_init();

        $arrBody = [
            'model' => $model,
            'instructions' => 'You will be given a set of questions in CSV format, update the CSV to answer each question and then return the updated CSV content. The "Question" column can be omitted from the returned CSV. Try to write at least 200 words per answer. Use British English. Use GBP for all prices. Only respond with the CSV content.',
            'input' => $questionsCsv
        ];

        curl_setopt_array($curl, [
              CURLOPT_URL => "https://api.openai.com/v1/responses",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 300,
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

        if(isset($arrResponse['status']) && $arrResponse['status'] == 'completed')
        {
            // Should only be one message with the answer content
            foreach($arrResponse['output'] as $output)
            {
                return $output['content'][0]['text'];
            }
        }
        else
        {
            throw new \Exception('Could not fetch answer content.');
        }

        throw new \Exception('Could not fetch answer content.');
    }
}