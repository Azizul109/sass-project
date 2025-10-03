<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ScrapingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ScrapingController extends Controller
{
    public function __construct(private ScrapingService $scrapingService)
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        // Check if user can view scraped data
        Gate::authorize('viewAny', \App\Models\ScrapedData::class);

        try {
            $data = $this->scrapingService->getScrapedData(
                $request->user()->company,
                $request->only(['source', 'search', 'type'])
            );

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to load scraped data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function scrape(Request $request): JsonResponse
    {
        // Check if user can initiate scraping
        Gate::authorize('scrape', \App\Models\ScrapedData::class);

        try {
            $request->validate([
                'type' => 'required|in:news,ecommerce,all'
            ]);

            $company = $request->user()->company;

            switch ($request->type) {
                case 'news':
                    $this->scrapingService->scrapeNews($company);
                    break;
                case 'ecommerce':
                    $this->scrapingService->scrapeEcommerce($company);
                    break;
                case 'all':
                    $this->scrapingService->scrapeNews($company);
                    $this->scrapingService->scrapeEcommerce($company);
                    break;
            }

            return response()->json(['message' => 'Scraping completed successfully']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Scraping failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportCsv(Request $request)
    {
        // Check if user can export data
        Gate::authorize('export', \App\Models\ScrapedData::class);

        try {
            $data = $this->scrapingService->getScrapedDataForExport(
                $request->user()->company,
                $request->only(['source', 'search', 'type'])
            );

            $fileName = 'scraped_data_' . now()->format('Y_m_d_His') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ];

            $callback = function () use ($data) {
                $file = fopen('php://output', 'w');
                
                fputcsv($file, ['Title', 'Source', 'URL', 'Description', 'Price', 'Published At', 'Type']);
                
                foreach ($data as $item) {
                    fputcsv($file, [
                        $item->title,
                        $item->source,
                        $item->url,
                        $item->description,
                        $item->price ? '$' . number_format($item->price, 2) : 'N/A',
                        $item->published_at->format('Y-m-d H:i:s'),
                        $item->metadata['type'] ?? 'unknown'
                    ]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }
}