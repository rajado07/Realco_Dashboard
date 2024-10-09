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

        // Use last_generated_at as the starting point, or created_at if not available
        $startDate = $generator->last_generated_at 
            ? Carbon::parse($generator->last_generated_at) 
            : Carbon::parse($generator->created_at);

        // Limit the generation of past tasks to 2 months ago, starting from the beginning of the month
        $twoMonthsAgo = $now->copy()->subMonths(2)->startOfMonth();
        if ($startDate->lt($twoMonthsAgo)) {
            $startDate = $twoMonthsAgo;
        }

        // Generate missed tasks based on frequency
        switch ($generator->frequency) {
            case 'daily':
                $this->generateDailyTasks($generator, $startDate, $now);
                break;

            case 'weekly':
                $this->generateWeeklyTasks($generator, $startDate, $now);
                break;

            case 'hourly':
                $this->generateHourlyTasks($generator, $startDate, $now);
                break;

            case 'minutely':
                $this->generateMinutelyTasks($generator, $startDate, $now);
                break;

            case 'three_times_weekly':
                $this->generateThreeTimesWeeklyTasks($generator, $startDate, $now);
                break;

            default:
                break;
        }

        // Update last_generated_at to now after generating tasks
        $generator->last_generated_at = $now;
        $generator->save();
    }

    protected function generateDailyTasks(TaskGenerator $generator, $startDate, $endDate)
    {
        // Ensure startDate and endDate are Carbon instances
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        // Generate all missed daily tasks
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $scheduledToRun = $date->setTimeFrom(Carbon::parse($generator->run_at));
            // Check if the task already exists, then generate if it doesn't
            $this->createTaskIfNotExists($generator, $scheduledToRun);
        }
    }

    protected function generateWeeklyTasks(TaskGenerator $generator, $startDate, $endDate)
    {
        // Ensure startDate and endDate are Carbon instances
        $startDate = Carbon::parse($startDate)->startOfWeek();
        $endDate = Carbon::parse($endDate);

        // Generate all missed weekly tasks
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addWeek()) {
            $scheduledToRun = $date->setTimeFrom(Carbon::parse($generator->run_at));
            $this->createTaskIfNotExists($generator, $scheduledToRun);
        }
    }

    protected function generateHourlyTasks(TaskGenerator $generator, $startDate, $endDate)
    {
        // Ensure startDate and endDate are Carbon instances
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        // Generate all missed hourly tasks
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addHour()) {
            $scheduledToRun = $date->setTimeFrom(Carbon::parse($generator->run_at));
            $this->createTaskIfNotExists($generator, $scheduledToRun);
        }
    }

    protected function generateMinutelyTasks(TaskGenerator $generator, $startDate, $endDate)
    {
        // Ensure startDate and endDate are Carbon instances
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        // Generate all missed minutely tasks
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addMinute()) {
            $scheduledToRun = $date->setTimeFrom(Carbon::parse($generator->run_at));
            $this->createTaskIfNotExists($generator, $scheduledToRun);
        }
    }

    protected function generateThreeTimesWeeklyTasks(TaskGenerator $generator, $startDate, $endDate)
    {
        // Ensure startDate and endDate are Carbon instances
        $startDate = Carbon::parse($startDate)->startOfWeek();
        $endDate = Carbon::parse($endDate);

        // Generate tasks on Monday, Wednesday, and Friday of each week
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addWeek()) {
            foreach ([Carbon::MONDAY, Carbon::WEDNESDAY, Carbon::FRIDAY] as $dayOfWeek) {
                $scheduledToRun = $date->copy()->next($dayOfWeek)->setTimeFrom(Carbon::parse($generator->run_at));
                // Ensure the scheduled date is not beyond the endDate
                if ($scheduledToRun->lte($endDate)) {
                    $this->createTaskIfNotExists($generator, $scheduledToRun);
                }
            }
        }
    }

    protected function createTaskIfNotExists(TaskGenerator $generator, $scheduledToRun)
    {
        // Limit generation to two months ago, starting from the beginning of the month
        $twoMonthsAgo = Carbon::now()->subMonths(2)->startOfMonth();

        // Check if scheduled_to_run is within the two-month limit
        if ($scheduledToRun->lt($twoMonthsAgo)) {
            return;
        }

        // Check if a task with the specific time already exists in the database
        $existingTask = Task::where('brand_id', $generator->brand_id)
            ->where('market_place_id', $generator->market_place_id)
            ->where('type', $generator->type)
            ->where('link', $generator->link)
            ->where('scheduled_to_run', $scheduledToRun)
            ->first();

        // If the task does not exist, create a new task
        if (!$existingTask) {
            Task::create([
                'brand_id' => $generator->brand_id,
                'market_place_id' => $generator->market_place_id,
                'type' => $generator->type,
                'link' => $generator->link,
                'scheduled_to_run' => $scheduledToRun,
                'status' => 1, // status ready
                'task_generator_id' => $generator->id,
            ]);
        }
    }
}