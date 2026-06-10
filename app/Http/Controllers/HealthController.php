<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HealthController extends Controller
{
    public function live(): JsonResponse
    {
        return response()->json(['status' => 'ok']);
    }

    public function ready(): JsonResponse
    {
        $checks = [
            'database' => $this->check(fn () => DB::select('select 1')),
            'cache' => $this->check(fn () => Cache::store()->put('healthcheck', 'ok', 5)),
            'storage' => $this->check(fn () => Storage::disk('local')->put('healthcheck.txt', 'ok')),
        ];

        return response()->json([
            'status' => in_array(false, $checks, true) ? 'degraded' : 'ok',
            'checks' => $checks,
        ], in_array(false, $checks, true) ? 503 : 200);
    }

    private function check(callable $callback): bool
    {
        try {
            $callback();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
