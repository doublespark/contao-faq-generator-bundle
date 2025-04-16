<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle\Schema;

class FaqSchemaGenerator {

    protected array $questions = [];

    public function addQuestion(string $question, string $answer): void
    {
        $this->questions[] = [
            'question' => $question,
            'answer' => $answer
        ];
    }

    public function generate(): string
    {
        $arrQuestionSchema = [];

        foreach($this->questions as $question)
        {
            // Don't include if we are missing content
            if(empty($question['answer']) || empty($question['question']))
            {
                continue;
            }

            $arrQuestionSchema[] = [
                '@type' => 'Question',
                'name' => strip_tags($question['question']),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $question['answer'],
                ]
            ];
        }

        // No questions
        if(count($arrQuestionSchema) < 1)
        {
            return '';
        }

        $schema = json_encode([
            '@context' => 'http://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $arrQuestionSchema
        ]);
        
        return "<script type=\"application/ld+json\">$schema</script>";
    }

}