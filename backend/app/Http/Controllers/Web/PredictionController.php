<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class PredictionController extends Controller
{
    public function showForm()
    {
        return view('predict');
    }

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
            return redirect()->back()->withErrors(['ai' => 'Failed to start prediction process']);
        }

        fwrite($pipes[0], $jsonInput);
        fclose($pipes[0]);

       $output = stream_get_contents($pipes[1]);
fclose($pipes[1]);

// ✅ NEW: capture Python errors
$errorOutput = stream_get_contents($pipes[2]);
fclose($pipes[2]);

proc_close($process);

// ✅ Log stderr so you can see the real Python error
if (!empty($errorOutput)) {
    Log::error('Python STDERR:', ['stderr' => $errorOutput]);
}

$result = json_decode($output, true);

if (empty($output) || $result === null) {
    $err = json_last_error_msg();
    Log::error('AI prediction failed', [
        'output' => $output,
        'json_error' => $err,
        'stderr' => $errorOutput
    ]);

    $result = [
        'prediction' => 'Error',
        'probability' => 0,
        'weather_severity' => 'AI response invalid',
        'raw_output' => $output,
        'json_error' => $err,
        'stderr' => $errorOutput
    ];
}

return redirect()->back()->with('result', $result)->withInput();

    }
}