<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle\Generator;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;

class AnswerGenerator {

    public function __construct(private ContaoFramework $framework){}

    public function generate(QuestionSet $questionSet): QuestionSet
    {
        $csv = $this->convertToCsv($questionSet);

        $csv = $this->getResponse($csv);

        $arrQuestions = $this->csvToArray($csv);

        foreach($arrQuestions as $question)
        {
            $questionSet->getQuestion((int)$question['ID'])?->setAnswer($question['Answer']);
        }

        return $questionSet;
    }

    protected function convertToCsv(QuestionSet $questionSet): string
    {
        $fh = fopen('php://temp', 'r+');

        fputcsv($fh, ['ID','Question','Answer'], ',', '"');

        foreach ($questionSet as $question)
        {
            fputcsv($fh, [$question->getId(), $question->getQuestion(), ''], ',', '"');
        }

        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return $csv;
    }

    protected function csvToArray(string $csv): array
    {
        if(empty($csv))
        {
            return [];
        }

        $rows = explode("\n", $csv);
        $rows = array_filter($rows, 'trim');

        $header = null;

        $data = [];

        foreach ($rows as $row)
        {
            $fields = str_getcsv($row, ",", '"');

            if(!$header)
            {
                $header = $fields;
            }
            else
            {
                $data[] = array_combine($header, $fields);
            }
        }

        return $data;
    }

    protected function getResponse(string $questionsCsv): string
    {
        // Allow this to run for 2 mins
        set_time_limit(120);

        $config = $this->framework->getAdapter(Config::class);

        $apiKey = $config->get('fg_openAiApiKey') ?? '';
        $model = $config->get('fg_openAiApiModel') ?? 'gpt-4o-mini';

        $curl = curl_init();

        $arrBody = [
            'model' => $model,
            'instructions' => 'You will be given a series of questions in CSV format, update the CSV to answer each question and then return the updated CSV content. The "Question" column can be omitted from the returned CSV. Try to write at least 200 words per answer.',
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