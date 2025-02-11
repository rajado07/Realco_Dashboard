<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;

class DateRangeService
{
    public function getDateRange(Request $request)
    {
        $startDateInput = $request->input('start_date');
        $endDateInput   = $request->input('end_date');
        $startDate2Input = $request->input('start_date_2');
        $endDate2Input   = $request->input('end_date_2');

        if (!$startDateInput && !$endDateInput) {
            $startDate = Carbon::now()->subMonth()->startOfDay();
            $endDate   = Carbon::now()->endOfDay();
        } else {
            try {
                $startDate = Carbon::parse($startDateInput)->startOfDay();
                $endDate   = Carbon::parse($endDateInput)->endOfDay();
            } catch (\Exception $e) {
                throw new \InvalidArgumentException('Invalid date format for start_date or end_date');
            }
        }

        // Cek apakah start_date_2 dan end_date_2 tersedia
        if ($startDate2Input && $endDate2Input) {
            try {
                $previousStartDate = Carbon::parse($startDate2Input)->startOfDay();
                $previousEndDate   = Carbon::parse($endDate2Input)->endOfDay();
            } catch (\Exception $e) {
                throw new \InvalidArgumentException('Invalid date format for start_date_2 or end_date_2');
            }
        } else {
            // Jika start_date_2 dan end_date_2 tidak ada, hitung previous range berdasarkan start_date dan end_date
            $diffInDays = $startDate->diffInDays($endDate) + 1;

            $previousStartDate = $startDate->copy()->subDays($diffInDays)->startOfDay();
            $previousEndDate   = $startDate->copy()->subDays(1)->endOfDay();
        }

        return [
            'current'  => [$startDate, $endDate],
            'previous' => [$previousStartDate, $previousEndDate],
        ];
    }
}
