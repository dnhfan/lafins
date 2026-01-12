<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class JarResource extends JsonResource
{
    protected float $totalBalance;

    public function __construct($resource, float $totalBalance = 0)
    {
        parent::__construct($resource);
        $this->totalBalance = $totalBalance;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $percentage = (float) $this->percentage;

        return [
            'id' => $this->id,
            'key' => $this->name,
            'label' => $this->full_name ?? $this->name,
            'percentage' => $percentage,
            'balance' => $this->balance,
            'allocated' => $this->totalBalance > 0 ? round(($percentage / 100) * $this->totalBalance, 2) : 0.0,
        ];
    }
}
