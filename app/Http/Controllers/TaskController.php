<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function index()
    {
        $data = Task::all();
        return response()->json($data);
    }

    public function getTaskStatusCount()
    {
        // Mengambil jumlah uploads berdasarkan status yang ditentukan
        $statusCounts = Task::select('status', DB::raw('count(*) as total'))
            ->whereIn('status', [1, 2, 3, 4, 5])
            ->groupBy('status')
            ->get()
            ->keyBy('status')
            ->map(function ($item) {
                return $item->total;
            });

        $allStatusCounts = collect([1, 2, 3, 4, 5])->mapWithKeys(function ($status) use ($statusCounts) {
            return [$status => $statusCounts->get($status, 0)];
        });

        return response()->json($allStatusCounts);
    }

    public function updateStatus(Request $request)
    {
        $ids = $request->input('ids');
        $type = $request->input('type');
        $status = $request->input('status');

        $updateCount = 0;

        try {
            foreach ($ids as $id) {
                $model = Task::find($id);
                if ($model) {
                    $model->status = $status;
                    $model->save();
                    $updateCount++;
                }
            }

            $successMessage = "Updated successfully.";
            if ($type == 'start') {
                $successMessage = "$updateCount Task started successfully.";
            } elseif ($type == 'archived') {
                $successMessage = "$updateCount Task archived successfully.";
            }

            return response()->json([
                'message' => $successMessage,
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function getExceptionDetails(Request $request)
    {
        // Get the task ID from the request
        $id = $request->input('id');

        // Return an error if the ID is not provided
        if (!$id) {
            return response()->json(['error' => 'ID is required'], 400);
        }

        // Find the task and include the brand relation
        $task = Task::with('brand')->find($id);

        // Return an error if the task is not found
        if (!$task) {
            return response()->json(['error' => 'Task record not found'], 404);
        }

        // Get the fast API URL from the brand, default to localhost if not found
        $fastApiUrl = optional($task->brand)->fast_api_url ?? "http://127.0.0.1:8001";

        // Build the FastAPI endpoint URL for the screenshot
        $fastApiEndpoint = "{$fastApiUrl}/screenshot/{$task->type}/{$id}";

        try {
            // Send a GET request to the FastAPI endpoint
            $response = Http::get($fastApiEndpoint);

            // Prepare the data to return
            $data = [
                'exceptionMessage' => $task->message, // Get the exception message from the task
            ];

            // Check if the FastAPI response was successful and contains an imageUrl
            if ($response->successful() && isset($response->json()['imageUrl'])) {
                $imageUrl = $response->json()['imageUrl'];

                // Prepend the base URL to the image path
                $completeImageUrl = $fastApiUrl . '/' . ltrim($imageUrl, '/');

                // Add the complete imageUrl to the response data
                $data['imageUrl'] = $completeImageUrl;
            } else {
                // If no imageUrl was found, set it to null
                $data['imageUrl'] = null;
            }

            // Return the data as JSON
            return response()->json($data);
        } catch (\Exception $e) {
            // Return an error message if there was an exception while making the request
            return response()->json(['error' => 'Exception occurred while fetching data from FastAPI', 'message' => $e->getMessage()], 500);
        }
    }
}
