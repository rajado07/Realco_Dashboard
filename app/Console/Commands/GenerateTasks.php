<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TaskGenerator;
use App\Models\Task;
use Carbon\Carbon;

class GenerateTasks extends Command
{
    protected $signature = 'generate:tasks';
    protected $description = 'Generate tasks based on task_generators table';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $taskGenerators = TaskGenerator::all();

        foreach ($taskGenerators as $generator) {
            $this->generateTasksForGenerator($generator);
        }
    }

    protected function generateTasksForGenerator(TaskGenerator $generator)
    {
        $now = Carbon::now();
        $maxLookBackPeriod = Carbon::now()->subMonth();
        $creationDate = $generator->created_at->gt($maxLookBackPeriod) ? $generator->created_at : $maxLookBackPeriod;

        switch ($generator->frequency) {
            case 'daily':
                $this->generateDailyTasks($generator, $creationDate, $now);
                break;

            case 'weekly':
                $this->generateWeeklyTasks($generator, $creationDate, $now);
                break;

            case 'hourly':
                $this->generateHourlyTasks($generator, $creationDate, $now);
                break;

            case 'minutely':
                $this->generateMinutelyTasks($generator, $creationDate, $now);
                break;

            default:
                $this->error('Unknown frequency: ' . $generator->frequency);
                break;
        }
    }

    protected function generateDailyTasks(TaskGenerator $generator, $startDate, $endDate)
    {
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $scheduledToRun = $date->setTimeFrom(Carbon::parse($generator->run_at));

            $this->createTaskIfNotExists($generator, $scheduledToRun);
        }
    }

    protected function generateWeeklyTasks(TaskGenerator $generator, $startDate, $endDate)
    {
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addWeek()) {
            $scheduledToRun = $date->setTimeFrom(Carbon::parse($generator->run_at));

            $this->createTaskIfNotExists($generator, $scheduledToRun);
        }
    }

    protected function generateHourlyTasks(TaskGenerator $generator, $startDate, $endDate)
    {
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addHour()) {
            $scheduledToRun = $date->setTimeFrom(Carbon::parse($generator->run_at));

            $this->createTaskIfNotExists($generator, $scheduledToRun);
        }
    }

    protected function generateMinutelyTasks(TaskGenerator $generator, $startDate, $endDate)
    {
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addMinute()) {
            $scheduledToRun = $date->setTimeFrom(Carbon::parse($generator->run_at));

            $this->createTaskIfNotExists($generator, $scheduledToRun);
        }
    }

    protected function createTaskIfNotExists(TaskGenerator $generator, $scheduledToRun)
    {
        $existingTask = Task::where('brand_id', $generator->brand_id)
            ->where('market_place_id', $generator->market_place_id)
            ->where('type', $generator->type)
            ->where('link' , $generator->link)
            ->where('scheduled_to_run', $scheduledToRun)
            ->first();

        if (!$existingTask) {
            Task::create([
                'brand_id' => $generator->brand_id,
                'market_place_id' => $generator->market_place_id,
                'type' => $generator->type,
                'link' => $generator->link,
                'scheduled_to_run' => $scheduledToRun,
                'status' => 1, // ready status
            ]);

            $this->info("Task generated for {$generator->type} scheduled to run at {$scheduledToRun}");
        } else {
            $this->info("Task for {$generator->type} already scheduled to run at {$scheduledToRun}");
        }
    }
}
