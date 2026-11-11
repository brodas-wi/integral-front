<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AgencyController extends Controller
{
    public function list(): JsonResponse
    {
        $data = Cache::remember('agencies.public-list', 300, function () {
            $phones = DB::table('agency_phones')
                ->orderBy('id')
                ->get()
                ->groupBy('agency_id');

            return Agency::active()
                ->orderBy('zone')
                ->orderBy('name')
                ->get()
                ->map(fn ($agency) => [
                    'id' => $agency->id,
                    'name' => $agency->name,
                    'address' => $agency->address,
                    'municipality' => $agency->municipality,
                    'department' => $agency->department,
                    'zone' => $agency->zone,
                    'schedule' => $agency->schedule,
                    'latitude' => $agency->latitude !== null ? (float) $agency->latitude : null,
                    'longitude' => $agency->longitude !== null ? (float) $agency->longitude : null,
                    'phones' => ($phones[$agency->id] ?? collect())
                        ->pluck('phone')
                        ->filter()
                        ->values()
                        ->all(),
                ])
                ->values()
                ->all();
        });

        return response()->json($data);
    }
}