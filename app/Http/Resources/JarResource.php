<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class JarResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $totalBalance = $this->additional['total_balance'] ?? 0;
        $percentage = (float) $this->percentage;

        return [
            'id' => $this->id,
            'key' => $this->name,
            'label' => $this->full_name ?? $this->name,
            'percentage' => $percentage,
            'balance' => $this->balance,
            'allocated' => $totalBalance > 0 ? round(($percentage / 100) * $totalBalance, 2) : 0.0,
        ];
    }
}
