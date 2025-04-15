<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle\Generator;

use Iterator;

class QuestionSet implements Iterator {

    /**
     * @var Question[]
     */
    private array $questions = [];

    private array $keys = [];

    private int $index = 0;

    public function __construct(array $questions=[])
    {
        foreach ($questions as $q)
        {
            $this->addQuestion($q);
        }
    }

    public function addQuestion(Question $question): void
    {
        $this->questions[$question->getId()] = $question;
        $this->keys[] = $question->getId();
    }

    public function getQuestion(int $id): ?Question
    {
        return $this->questions[$id] ?? null;
    }

    public function current(): Question
    {
        return $this->questions[$this->keys[$this->index]];
    }

    public function next(): void
    {
        $this->index++;
    }

    public function key(): int
    {
        return $this->keys[$this->index];
    }

    public function valid(): bool
    {
        return isset($this->keys[$this->index]);
    }

    public function rewind(): void
    {
        $this->index = 0;
    }

    public function count(): int
    {
        return count($this->questions);
    }

    public function clear(): void
    {
        $this->questions = [];
        $this->keys = [];
        $this->index = 0;
    }
}