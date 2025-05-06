<?php

namespace Doublespark\FaqGeneratorBundle\Tests\Unit\Csv;

use Doublespark\FaqGeneratorBundle\Csv\CsvService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class CsvServiceTest extends TestCase {

    protected function getCsvService(): CsvService
    {
        $csvService = new CsvService($this->createMock(KernelInterface::class));
        $csvService->disableLogging();

        return $csvService;
    }

    public function testParsesCsv()
    {
        $csvService = $this->getCsvService();

        $arrTestFiles = [
            'test-a.csv',
            'test-b.csv'
        ];

        foreach($arrTestFiles as $testFile)
        {
            $csv = file_get_contents(__DIR__ . '/../../data/csv/' . $testFile);

            $result = $csvService->csvToArray($csv);

            $this->assertIsArray($result);
        }
    }

}