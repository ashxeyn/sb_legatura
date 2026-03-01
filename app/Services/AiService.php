<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AiService — Business logic for AI predictions and analytics.
 *
 * Provides a clean OOP interface for:
 * - Fetching AI usage stats from the Python service
 * - Running predictions on projects
 * - Retrieving prediction history (scoped by contractor or global for admin)
 * - Validating project ownership for contractors
 */
class AiService
{
    /**
     * Base URL for the Python AI service.
     */
    protected string $aiServiceUrl;

    /**
     * Timeout for AI service requests (seconds).
     */
    protected int $timeout;

    public function __construct()
    {
        $this->aiServiceUrl = config('services.ai.url', 'http://127.0.0.1:5001');
        $this->timeout = config('services.ai.timeout', 10);
    }

    /* =====================================================================
     * SYSTEM STATUS
     * ===================================================================== */

    /**
     * Get AI system health and feature availability.
     *
     * @return array{status: string, features: array}
     */
    public function getSystemStatus(): array
    {
        $aiData = [
            'status'   => 'Offline',
            'features' => [],
        ];

        try {
            $response = Http::timeout(5)->get("{$this->aiServiceUrl}/system-status");

            if ($response->successful()) {
                $data = $response->json();
                $aiData['status']   = $data['service_status'] ?? 'Offline';
                $aiData['features'] = $data['active_features'] ?? [];
            }
        } catch (\Exception $e) {
            Log::warning('AI Service unavailable', ['error' => $e->getMessage()]);
        }

        return $aiData;
    }

    /* =====================================================================
     * PREDICTIONS
     * ===================================================================== */

    /**
     * Run AI prediction for a specific project.
     *
     * @param int $projectId
     * @return array{success: bool, message: string, data?: array}
     */
    public function runPrediction(int $projectId): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->aiServiceUrl}/predict/{$projectId}");

            if ($response->failed()) {
                return [
                    'success' => false,
                    'message' => 'AI Service Unavailable',
                ];
            }

            $data = $response->json();

            // Check if Python returned an error
            if (isset($data['error'])) {
                return [
                    'success' => false,
                    'message' => $data['error'],
                ];
            }

            // Save prediction to database
            $this->savePredictionLog($projectId, $data);

            return [
                'success' => true,
                'message' => 'Analysis Complete',
                'data'    => $data,
            ];
        } catch (\Exception $e) {
            Log::error('AI prediction failed', [
                'project_id' => $projectId,
                'error'      => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Save prediction result to the ai_prediction_logs table.
     *
     * @param int $projectId
     * @param array $data
     * @return void
     */
    protected function savePredictionLog(int $projectId, array $data): void
    {
        DB::table('ai_prediction_logs')->insert([
            'project_id'           => $projectId,
            'prediction'           => $data['prediction']['prediction'] ?? null,
            'delay_probability'    => $data['prediction']['delay_probability'] ?? null,
            'weather_severity'     => $data['weather_severity'] ?? null,
            'ai_response_snapshot' => json_encode($data),
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);
    }

    /* =====================================================================
     * PREDICTION HISTORY
     * ===================================================================== */

    /**
     * Get all prediction logs (for admin).
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllPredictionLogs(int $perPage = 10)
    {
        return DB::table('ai_prediction_logs')
            ->join('projects', 'ai_prediction_logs.project_id', '=', 'projects.project_id')
            ->select(
                'ai_prediction_logs.*',
                'projects.project_title',
                'projects.project_location'
            )
            ->orderBy('ai_prediction_logs.created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get prediction logs for a specific contractor (scoped by their projects).
     *
     * @param int $contractorId
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getContractorPredictionLogs(int $contractorId, int $perPage = 10)
    {
        return DB::table('ai_prediction_logs')
            ->join('projects', 'ai_prediction_logs.project_id', '=', 'projects.project_id')
            ->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->where('project_relationships.selected_contractor_id', $contractorId)
            ->select(
                'ai_prediction_logs.*',
                'projects.project_title',
                'projects.project_location'
            )
            ->orderBy('ai_prediction_logs.created_at', 'desc')
            ->paginate($perPage);
    }

    /* =====================================================================
     * PROJECT QUERIES
     * ===================================================================== */

    /**
     * Get all projects (for admin analysis dropdown).
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllProjects()
    {
        return DB::table('projects')
            ->select('project_id', 'project_title', 'project_status')
            ->orderBy('project_title', 'asc')
            ->get();
    }

    /**
     * Get projects assigned to a specific contractor.
     *
     * Returns projects where the contractor is the selected_contractor_id.
     *
     * @param int $contractorId
     * @return \Illuminate\Support\Collection
     */
    public function getContractorProjects(int $contractorId)
    {
        return DB::table('projects')
            ->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->where('project_relationships.selected_contractor_id', $contractorId)
            ->select(
                'projects.project_id',
                'projects.project_title',
                'projects.project_status'
            )
            ->orderBy('projects.project_title', 'asc')
            ->get();
    }

    /* =====================================================================
     * AUTHORIZATION
     * ===================================================================== */

    /**
     * Check if a contractor owns a specific project.
     *
     * @param int $contractorId
     * @param int $projectId
     * @return bool
     */
    public function contractorOwnsProject(int $contractorId, int $projectId): bool
    {
        return DB::table('projects')
            ->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->where('projects.project_id', $projectId)
            ->where('project_relationships.selected_contractor_id', $contractorId)
            ->exists();
    }

    /**
     * Get contractor record by user ID.
     *
     * Handles both direct contractors and contractor_users (team members).
     *
     * @param int $userId
     * @return object|null
     */
    public function getContractorByUserId(int $userId)
    {
        // Direct contractor lookup
        $contractor = DB::table('contractors')
            ->where('user_id', $userId)
            ->first();

        if ($contractor) {
            return $contractor;
        }

        // Fallback: Check contractor_users (team member)
        $contractorUser = DB::table('contractor_users')
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->first();

        if ($contractorUser) {
            return DB::table('contractors')
                ->where('contractor_id', $contractorUser->contractor_id)
                ->first();
        }

        return null;
    }

    /* =====================================================================
     * STATISTICS
     * ===================================================================== */

    /**
     * Get AI usage statistics for a contractor.
     *
     * @param int $contractorId
     * @return array
     */
    public function getContractorAiStats(int $contractorId): array
    {
        $logs = DB::table('ai_prediction_logs')
            ->join('projects', 'ai_prediction_logs.project_id', '=', 'projects.project_id')
            ->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->where('project_relationships.selected_contractor_id', $contractorId)
            ->select('ai_prediction_logs.*')
            ->get();

        $totalAnalyses = $logs->count();
        $delayedCount = $logs->where('prediction', 'DELAYED')->count();
        $onTimeCount = $logs->where('prediction', 'ON_TIME')->count();
        $avgDelayProbability = $totalAnalyses > 0 
            ? round($logs->avg('delay_probability') * 100, 1) 
            : 0;

        return [
            'total_analyses'       => $totalAnalyses,
            'delayed_predictions'  => $delayedCount,
            'on_time_predictions'  => $onTimeCount,
            'avg_delay_probability' => $avgDelayProbability,
        ];
    }
}
