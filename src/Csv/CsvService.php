<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle\Csv;

use Doublespark\FaqGeneratorBundle\Generator\QuestionSet;
use Symfony\Component\HttpKernel\KernelInterface;

class CsvService {

    protected bool $loggingEnabled = true;

    public function __construct(private readonly KernelInterface $kernel) {}

    public function questionSetToCsv(QuestionSet $questionSet): string
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

    public function csvToArray(string $csv): array
    {
        if(empty($csv))
        {
            return [];
        }

        $originalCSV = $csv;

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
            if($this->loggingEnabled)
            {
                $filename = $this->saveCsvData($originalCSV);

                throw new \Exception("Chat GPT returned invalid CSV data, see var/logs/$filename");
            }
            else
            {
                throw new \Exception("Chat GPT returned invalid CSV data");
            }
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
                    if($this->loggingEnabled)
                    {
                        $filename = $this->saveCsvData($originalCSV);

                        throw new \Exception("CSV header and columns count did not match on row $index. See var/logs/$filename");
                    }
                    else
                    {
                        throw new \Exception("CSV header and columns count did not match on row $index");
                    }
                }

                $data[] = array_combine($header, $fields);
            }
        }

        return $data;
    }

    public function disableLogging(): void
    {
        $this->loggingEnabled = false;
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