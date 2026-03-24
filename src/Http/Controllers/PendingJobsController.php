<?php

declare(strict_types=1);

namespace APB\HorizonUI\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Horizon\Http\Controllers\PendingJobsController as BasePendingJobsController;

class PendingJobsController extends BasePendingJobsController
{
    /**
     * Get all of the pending jobs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function index(Request $request): array
    {
        $query = $request->query('query');

        if ($query) {
            return $this->searchByName($request, $query);
        }

        return parent::index($request);
    }

    /**
     * Search pending jobs by name.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $query
     * @return array
     */
    protected function searchByName(Request $request, string $query): array
    {
        $limit = (int) $request->query('limit', 50);
        $startingAt = $request->query('starting_at', -1);
        $matched = collect();
        $cursor = $startingAt;
        $maxIterations = 20;
        $iterations = 0;

        while ($matched->count() < $limit && $iterations < $maxIterations) {
            $batch = $this->jobs->getPending($cursor);

            if ($batch->isEmpty()) {
                break;
            }

            $filtered = $batch->filter(function ($job) use ($query) {
                return stripos($job->name, $query) !== false;
            });

            $matched = $matched->merge($filtered);

            $cursor = $batch->last()->index;
            $iterations++;
        }

        $jobs = $matched->take($limit)->map(function ($job) {
            $job->payload = json_decode($job->payload);

            return $job;
        })->values();

        return [
            'jobs' => $jobs,
            'total' => $jobs->count(),
        ];
    }
}
