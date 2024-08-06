<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TemperatureController extends Controller
{
    public function getTemp()
    {
        $url = env('SENSOR_ADDRESS') . '/temperature';

        try {
            $response = Http::get($url);

            if ($response->successful()) {
                $data = $response->json();

                return response()->json([
                    'status' => 'success',
                    'data' => $data,
                ]);
            } else {
                $currentTimeUtc = Carbon::now('UTC');
                $currentTime = $currentTimeUtc->setTimezone('Asia/Kolkata');
                $formattedTime = $currentTime->toDateTimeString();
                $data = [
                    "temperature" => "0",
                    "time" => $formattedTime
                ];
                return response()->json([
                    'status' => 'error',
                    'data' => $data
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function cutoff()
    {
        $url = env('SENSOR_ADDRESS') . '/cutoff';

        try {
            $response = Http::get($url);
            if ($response->successful()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'LED turned off successfully',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to turn off LED',
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function poweron()
    {
        $url = env('SENSOR_ADDRESS') . '/poweron';

        try {
            $response = Http::get($url);
            if ($response->successful()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'LED turned on successfully',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to turn on LED',
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
