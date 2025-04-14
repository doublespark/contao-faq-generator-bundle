<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle\Generator;

use Iterator;

class AnswerSet implements Iterator {

    /**
     * @var Answer[]
     */
    private array $answers;

    private int $index = 0;

    public function __construct(array $answers)
    {
        $this->answers = $answers;
    }

    public function current(): Answer
    {
        return $this->answers[$this->index];
    }

    public function next(): void
    {
        $this->index++;
    }

    public function key(): int
    {
        return $this->index;
    }

    public function valid(): bool
    {
        return isset($this->answers[$this->key()]);
    }

    public function rewind(): void
    {
        $this->index = 0;
    }
}