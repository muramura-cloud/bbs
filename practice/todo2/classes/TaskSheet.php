<?php

require_once(__DIR__ . '/Task.php');

class TaskSheet
{
    public $tasks = [];

    public function addTask(Task $task): void
    {
        $this->tasks[] = $task;
        echo $task->name . 'を追加しました。' . PHP_EOL;
    }

    public function show(): void
    {
        foreach ($this->tasks as $task) {
            if ($task->isCompleted()) {
                echo '<b>' . $task->name . '</b>';
            } else {
                echo $task->name;
            }
            echo PHP_EOL;
        }
    }
}
