<?php

namespace App\Filament\Resources\Orders\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class OrderStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('New Orders', Order::where('status', 'new')->count()),

            Stat::make('Processing Orders', Order::where('status', 'processing')->count()),

            Stat::make('Order Shipped', Order::where('status', 'shipped')->count()),

            Stat::make('Average Price (per currency)', $this->getAveragePriceText()),
        ];
    }

    private function getAveragePriceText(): string
    {
        $currencies = ['USD', 'EUR', 'INR', 'GBP'];

        $lines = [];

        foreach ($currencies as $currency) {

            $avg = Order::where('currency', $currency)->avg('grand_total');

            if ($avg !== null) {
                $lines[] = $currency . ': ' . Number::currency($avg, $currency);
            }
        }

        return implode("\n", $lines);
    }
}
