<?php

namespace App\Data;

class ElSalvadorGeography
{
    /**
     * Get all departments of El Salvador
     */
    public static function departments(): array
    {
        return [
            'Ahuachapán',
            'Cabañas',
            'Chalatenango',
            'Cuscatlán',
            'La Libertad',
            'La Paz',
            'La Unión',
            'Morazán',
            'San Miguel',
            'San Salvador',
            'San Vicente',
            'Santa Ana',
            'Sonsonate',
            'Usulután',
        ];
    }

    /**
     * Centroids for each department
     */
    private static function departmentCentroids(): array
    {
        return [
            'Ahuachapán'   => ['lat' => 13.9214, 'lng' => -89.8450, 'zoom' => 11],
            'Santa Ana'    => ['lat' => 13.9494, 'lng' => -89.5599, 'zoom' => 11],
            'Sonsonate'    => ['lat' => 13.7186, 'lng' => -89.7244, 'zoom' => 11],
            'Chalatenango' => ['lat' => 14.1000, 'lng' => -89.1667, 'zoom' => 10],
            'La Libertad'  => ['lat' => 13.6769, 'lng' => -89.3200, 'zoom' => 11],
            'San Salvador' => ['lat' => 13.7000, 'lng' => -89.2000, 'zoom' => 12],
            'Cuscatlán'    => ['lat' => 13.7333, 'lng' => -89.0500, 'zoom' => 11],
            'La Paz'       => ['lat' => 13.5000, 'lng' => -88.9500, 'zoom' => 11],
            'Cabañas'      => ['lat' => 13.8667, 'lng' => -88.6333, 'zoom' => 11],
            'San Vicente'  => ['lat' => 13.6333, 'lng' => -88.7833, 'zoom' => 11],
            'Usulután'     => ['lat' => 13.3500, 'lng' => -88.4500, 'zoom' => 10],
            'San Miguel'   => ['lat' => 13.4833, 'lng' => -88.1833, 'zoom' => 10],
            'Morazán'      => ['lat' => 13.7667, 'lng' => -88.1167, 'zoom' => 10],
            'La Unión'     => ['lat' => 13.3372, 'lng' => -87.8433, 'zoom' => 10],
        ];
    }

    /**
     * Get the centroid (lat, lng, zoom) for a single department.
     */
    public static function centroid(string $department): ?array
    {
        return self::departmentCentroids()[$department] ?? null;
    }

    /**
     * Get all department centroids at once.
     */
    public static function centroids(): array
    {
        return self::departmentCentroids();
    }
}
