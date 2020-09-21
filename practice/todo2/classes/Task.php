<?php

class Task
{
    public $name;
    public $priority;
    public $progress;

    public function __construct($name)
    {
        $this->name = $name;
        $this->priority = 1;
        $this->progress = 0;
    }

    public function isCompleted(): bool
    {
        return $this->progress >= 100;
    }
}
