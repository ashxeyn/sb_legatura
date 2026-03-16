<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;

trait WithAtomicLock
{
    /**
     * Execute a callback inside an atomic cache lock.
     *
     * @param  string     
     * @param  callable   
     * @param  int        
     * @return mixed      
     */
    protected function withLock(string $key, callable $callback, int $ttl = 10): mixed
    {
        $lock = Cache::lock($key, $ttl);

        if (!$lock->get()) {
            return response()->json([
                'success' => false,
                'message' => 'Another request is already being processed. Please try again.',
            ], 409);
        }

        try {
            return $callback();
        } finally {
            $lock->release();
        }
    }
}
