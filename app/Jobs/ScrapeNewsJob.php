<?php

namespace App\Jobs;

use App\Models\Company;
use App\Services\ScrapingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScrapeNewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    public function __construct(public Company $company)
    {
        // No Redis-specific code
    }

    public function handle(ScrapingService $scrapingService): void
    {
        try {
            $scrapingService->scrapeNews($this->company);
        } catch (\Exception $e) {
            $this->fail($e);
        }
    }
}