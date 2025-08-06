<?php

namespace App\Http\Resources\Billing\Plan;

use App\Constants\General\CurrencyConstants;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'plan_code' => $this->plan_code,
            'description' => $this->description,
            'amount' => $this->formatAmountWithSymbol(),
            'currency' => $this->currency->short_name,
            'interval' => $this->interval,
            'is_active' => $this->is_active ? true : false,
            'created_at' => formatDate($this->created_at),
            'updated_at' => formatDate($this->updated_at),
            'features' => PlanFeatureResource::collection($this->whenLoaded('features')),
        ];
    }

    protected function formatAmountWithSymbol(): string
{
    $symbol = CurrencyConstants::CURRENCY_SYMBOLS[$this->currency->name] ?? '';
    $amountInMajorUnits = $this->amount;

    return $symbol . number_format($amountInMajorUnits, 2);
}

}
