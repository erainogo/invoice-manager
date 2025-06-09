<?php

namespace App\Helpers;

use App\Models\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

trait CurrencyConverter 
{
    protected function getRate(string $base, string $target): float
    {
        $today = Carbon::now()->toDateString();
        $cacheKey = "exchange_rate:{$base}:{$target}:{$today}";

        // check Redis cache first
        return Cache::remember($cacheKey, now()->addHours(24), function () use ($base, $target, $today) {
            // check DB if not in cache
            $rateRecord = ExchangeRate::where('base_currency', $base)
                ->where('target_currency', $target)
                ->where('fetched_at', $today)
                ->first();

            if ($rateRecord) {
                return $rateRecord->rate;
            }

            // fetch from external API
            $response = Http::get(config('currency.url'), [
                'base' => $base,
                'symbols' => $target,
            ]);

            if (!$response->ok() || !isset($rate)) {
                throw new \Exception("Exchange rate API error or invalid response");
            }

            $rate = $response->json("rates.$target");

            if (!$rate) {
                throw new \Exception("Rate not found in response");
            }

            // save to DB
            ExchangeRate::create([
                'base_currency' => $base,
                'target_currency' => $target,
                'rate' => $rate,
                'fetched_at' => $today,
            ]);

            return $rate;
        });
    }
}
