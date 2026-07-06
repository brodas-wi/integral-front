<?php

namespace App\Http\Controllers;

use App\Data\ElSalvadorGeography;
use App\Models\Agency;
use App\Models\PaymentPoint;
use Illuminate\Http\JsonResponse;

class MapLocationController extends Controller
{
    /**
     * Combined data for the "Map with Filters" block on the public site
     */
    public function index(): JsonResponse
    {
        $departments = collect(ElSalvadorGeography::departments())
            ->map(function (string $name) {
                $centroid = ElSalvadorGeography::centroid($name);
                return [
                    'name' => $name,
                    'lat'  => $centroid['lat'] ?? null,
                    'lng'  => $centroid['lng'] ?? null,
                    'zoom' => $centroid['zoom'] ?? 10,
                ];
            })
            ->values();

        $agencies = Agency::active()
            ->withCoordinates()
            ->get()
            ->map(fn(Agency $agency) => [
                'id'         => $agency->id,
                'type'       => 'agency',
                'name'       => $agency->name,
                'address'    => $agency->address,
                'schedule'   => $agency->schedule,
                'department' => $agency->department,
                'lat'        => (float) $agency->latitude,
                'lng'        => (float) $agency->longitude,
            ])
            ->values();

        $paymentPoints = PaymentPoint::active()
            ->withCoordinates()
            ->get()
            ->map(fn(PaymentPoint $point) => [
                'id'            => $point->id,
                'type'          => 'payment_point',
                'name'          => trim("{$point->affiliate} - {$point->branch}", ' -'),
                'address'       => $point->address,
                'correspondent' => $point->correspondent,
                'department'    => $point->department,
                'lat'           => (float) $point->latitude,
                'lng'           => (float) $point->longitude,
            ])
            ->values();

        return response()->json([
            'success'        => true,
            'departments'    => $departments,
            'agencies'       => $agencies,
            'payment_points' => $paymentPoints,
        ]);
    }
}
