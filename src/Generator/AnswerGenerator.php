<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle\Generator;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Component\HttpKernel\KernelInterface;

class AnswerGenerator {

    public function __construct(
        private ContaoFramework $framework,
        private KernelInterface $kernel
    ){}

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

        // ChatGPT has included formatting metadata, extract the CSV content
        if(str_contains($csv, '```csv'))
        {
            $startsAt = strpos($csv, "```csv") + strlen("```csv");
            $endsAt = strpos($csv, "```", $startsAt);
            $csv = substr($csv, $startsAt, $endsAt - $startsAt);
        }

        // Check if ChatGPT surrounded the CSV with ```
        if(str_starts_with($csv, '```'))
        {
            $csv = str_replace('```', '', $csv);
        }

        $csv = trim($csv);

        if(!str_starts_with($csv, 'ID,Answer'))
        {
            $filename = $this->saveCsvData($csv);

            throw new \Exception("Chat GPT returned invalid CSV data, see var/logs/$filename");
        }

        $rows = explode("\n", $csv);
        $rows = array_filter($rows, 'trim');

        $header = null;

        $data = [];

        foreach ($rows as $index => $row)
        {
            // Remove any trailing comma
            $row = rtrim($row,',');

            $fields = str_getcsv($row, ",", '"');

            if(!$header)
            {
                $header = $fields;
            }
            else
            {
                if(count($header) !== count($fields))
                {
                    $filename = $this->saveCsvData($csv);

                    throw new \Exception("CSV header and columns count did not match on row $index. See var/logs/$filename");
                }

                $data[] = array_combine($header, $fields);
            }
        }

        return $data;
    }

    protected function getResponse(string $questionsCsv): string
    {
        $config = $this->framework->getAdapter(Config::class);

        $apiKey = $config->get('fg_openAiApiKey') ?? '';
        $model = $config->get('fg_openAiApiModel') ?? 'gpt-4o-mini';

        $curl = curl_init();

        $arrBody = [
            'model' => $model,
            'instructions' => 'You will be given a set of questions in CSV format, update the CSV to answer each question and then return the updated CSV content. The "Question" column can be omitted from the returned CSV. Try to write at least 200 words per answer. Only respond with the CSV content.',
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

    protected function saveCsvData(string $csvData): string
    {
        $id = uniqid();
        $date = date('Y-m-d-H-i-s');
        $filename =  "faq-$id-$date.csv";

        file_put_contents($this->kernel->getLogDir().'/'.$filename, $csvData);

        return $filename;
    }
}