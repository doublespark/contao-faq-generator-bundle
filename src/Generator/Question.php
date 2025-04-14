<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle\Generator;

class Question {

    protected int $id;
    protected string $question;
    protected string $answer;

    public function __construct(int $id, string $question)
    {
        $this->id = $id;
        $this->question = $question;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): void
    {
        $this->answer = $answer;
    }
}