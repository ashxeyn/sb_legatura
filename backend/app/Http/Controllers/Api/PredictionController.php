<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class PredictionController extends Controller
{
    public function predict(Request $request)
    {
        $input = $request->only([
            'avg_temp', 'avg_humidity', 'avg_wind', 'total_rain',
            'duration_days', 'contractor_experience_years',
            'rain_events', 'flooding', 'work_suspension_hours',
            'low_visibility', 'storm_warning'
        ]);

        $jsonInput = json_encode($input);

        $isWindows = stripos(PHP_OS, 'WIN') === 0;
        $defaultPython = $isWindows ? 'python' : 'python3';
        $pythonCmd = env('PREDICTOR_PYTHON', $defaultPython);
        $scriptPath = base_path('storage/app/ai/predict.py');

        if ($isWindows) {
            $cmd = escapeshellcmd($pythonCmd) . ' "' . $scriptPath . '"';
        } else {
            $cmd = escapeshellcmd($pythonCmd) . ' ' . escapeshellarg($scriptPath);
        }

        $cwd = base_path();
        $process = proc_open(
            $cmd,
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w']
            ],
            $pipes,
            $cwd
        );

        if (!is_resource($process)) {
            Log::error('Prediction process failed to start');
            return response()->json(['error' => 'Failed to start prediction process'], 500);
        }

        fwrite($pipes[0], $jsonInput);
        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        if (empty($output)) {
            Log::error('AI prediction returned empty output', ['stderr' => $stderr]);
            return response()->json(['error' => 'AI returned no output', 'stderr' => $stderr], 500);
        }

        $result = json_decode($output, true);
        if ($result === null) {
            $err = json_last_error_msg();
            Log::error('AI prediction returned invalid JSON', ['output' => $output, 'stderr' => $stderr, 'json_error' => $err]);
            return response()->json(['error' => 'Invalid AI response', 'raw_output' => $output, 'stderr' => $stderr, 'json_error' => $err], 500);
        }

        return response()->json($result);
    }
}
