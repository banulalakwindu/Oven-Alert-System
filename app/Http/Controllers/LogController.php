<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LogController extends Controller
{
    public function store()
    {
        Log::create();

        // Return a response
        return response()->json([
            'status' => 'success',
            'message' => 'Datetime logged successfully',
        ], 200);
    }

    public function welcome()
    {
        return view('welcome');
    }

    public function viewLog()
    {
        $url = env('SENSOR_ADDRESS') . '/logs';

        try {
            $response = Http::get($url);

            if ($response->successful()) {
                $data = $response->json();

                foreach ($data as $log) {
                    $time = Carbon::createFromFormat('d/m/Y H:i:s', $log);
                    $timestamp = Carbon::now()->format('d/m/Y H:i:s');
                    Log::create([
                        'interrupt_time' => $timestamp
                    ]);
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }

        $logs = Log::all();
        return view('logs', compact('logs'));
    }
}
