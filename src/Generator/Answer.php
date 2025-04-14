<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle\Generator;

class Answer {

    protected string $question;
    protected string $answer;

    public function __construct(string $question, string $answer)
    {
        $this->answer = $answer;
        $this->question = $question;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }
}