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
        // Gunakan last_generated_at sebagai titik awal, atau created_at jika belum ada, pastikan menggunakan Carbon
        $startDate = $generator->last_generated_at 
            ? Carbon::parse($generator->last_generated_at) // Konversi menjadi Carbon jika diperlukan
            : Carbon::parse($generator->created_at); // Konversi menjadi Carbon jika diperlukan

        // Generate tasks yang terlewat berdasarkan frekuensi
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

            default:
                $this->error('Unknown frequency: ' . $generator->frequency);
                break;
        }

        // Update last_generated_at ke waktu sekarang setelah task di-generate
        $generator->last_generated_at = $now;
        $generator->save();
    }

    protected function generateDailyTasks(TaskGenerator $generator, $startDate, $endDate)
    {
        // Pastikan startDate dan endDate adalah objek Carbon
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        // Pastikan semua task harian yang terlewat digenerate
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $scheduledToRun = $date->setTimeFrom(Carbon::parse($generator->run_at));

            // Cek apakah task sudah ada atau belum, lalu generate jika belum ada
            $this->createTaskIfNotExists($generator, $scheduledToRun);
        }
    }

    protected function generateWeeklyTasks(TaskGenerator $generator, $startDate, $endDate)
    {
        // Pastikan startDate dan endDate adalah objek Carbon
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        // Pastikan semua task mingguan yang terlewat digenerate
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addWeek()) {
            $scheduledToRun = $date->setTimeFrom(Carbon::parse($generator->run_at));

            $this->createTaskIfNotExists($generator, $scheduledToRun);
        }
    }

    protected function generateHourlyTasks(TaskGenerator $generator, $startDate, $endDate)
    {
        // Pastikan startDate dan endDate adalah objek Carbon
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        // Pastikan semua task hourly yang terlewat digenerate
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addHour()) {
            $scheduledToRun = $date->setTimeFrom(Carbon::parse($generator->run_at));

            $this->createTaskIfNotExists($generator, $scheduledToRun);
        }
    }

    protected function generateMinutelyTasks(TaskGenerator $generator, $startDate, $endDate)
    {
        // Pastikan startDate dan endDate adalah objek Carbon
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        // Pastikan semua task minutely yang terlewat digenerate
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addMinute()) {
            $scheduledToRun = $date->setTimeFrom(Carbon::parse($generator->run_at));

            $this->createTaskIfNotExists($generator, $scheduledToRun);
        }
    }

    protected function createTaskIfNotExists(TaskGenerator $generator, $scheduledToRun)
    {
        // Tentukan batas satu bulan ke belakang menggunakan Carbon
        $oneMonthAgo = Carbon::now()->subMonth();

        // Cek apakah scheduled_to_run berada dalam rentang satu bulan ke belakang
        if ($scheduledToRun->lt($oneMonthAgo)) {
            $this->info("Task not generated because scheduled time is beyond the 1-month limit.");
            return;
        }

        // Cek apakah task dengan waktu tertentu sudah ada di database
        $existingTask = Task::where('brand_id', $generator->brand_id)
            ->where('market_place_id', $generator->market_place_id)
            ->where('type', $generator->type)
            ->where('link', $generator->link)
            ->where('scheduled_to_run', $scheduledToRun)
            ->first();

        // Jika task belum ada, maka buat task baru
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

            $this->info("Task generated for {$generator->type} scheduled to run at {$scheduledToRun}");
        } else {
            // Jika task sudah ada, tampilkan pesan bahwa task sudah dijadwalkan
            $this->info("Task for {$generator->type} already scheduled to run at {$scheduledToRun}");
        }
    }
}
