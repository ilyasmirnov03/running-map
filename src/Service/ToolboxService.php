<?php

namespace App\Service;

class ToolboxService
{
    public function find_closest(array $array, $date): array
    {
        foreach ($array as $coord) {
            $interval[] = abs($date - $coord["date"]);
        }
        asort($interval);
        $closest = key($interval);
        return $array[$closest];
    }

    public function calculate_distance(array $coords): float
    {
        $distance = 0;
        for ($i = 0; $i < count($coords) - 1; $i++) {
            $earthRadius = 6371000;
            $latFrom = deg2rad($coords[$i]['latitude']);
            $lonFrom = deg2rad($coords[$i]['longitude']);
            $latTo = deg2rad($coords[$i + 1]['latitude']);
            $lonTo = deg2rad($coords[$i + 1]['longitude']);

            $latDelta = $latTo - $latFrom;
            $lonDelta = $lonTo - $lonFrom;

            $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
            $distance += $angle * $earthRadius;
        }
        return $distance;
    }
}
