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
        $percentage = (float) $this->percentage;
        $totalBalance = (float) $request->input('total_amount', 0);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'label' => $this->full_name ?? $this->name,
            'key' => $this->name, // Add key field for icon mapping
            'percentage' => (float) $percentage,
            'balance' => (float) $this->balance,
            'allocated' => $totalBalance > 0 ? round(($percentage / 100) * $totalBalance, 2) : 0.0,
        ];
    }
}
