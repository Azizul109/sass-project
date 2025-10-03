<?php

namespace App\Services;

use App\Models\Company;
use App\Models\ScrapedData;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class ScrapingService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]);
    }

    public function scrapeNews(Company $company): void
    {
        try {
            Log::info("Starting news scraping for company: " . $company->id);
            
            // Use a simple news website: Hacker News (very simple HTML structure)
            $response = $this->client->get('https://news.ycombinator.com/', [
                'delay' => 1000,
                'timeout' => 30,
            ]);

            $html = $response->getBody()->getContents();
            $crawler = new Crawler($html);
            $scrapedCount = 0;

            Log::info("Hacker News page loaded successfully");

            // Hacker News has very simple structure: .titleline > a
            $crawler->filter('.titleline > a')->each(function (Crawler $node) use ($company, &$scrapedCount) {
                try {
                    $title = $node->text();
                    $url = $node->attr('href');
                    
                    // Make URL absolute if relative
                    if ($url && strpos($url, 'http') !== 0) {
                        $url = 'https://news.ycombinator.com/' . $url;
                    }

                    if (empty($title) || empty($url)) {
                        return;
                    }

                    Log::info("Found news item: " . substr($title, 0, 50) . "...");

                    ScrapedData::create([
                        'company_id' => $company->id,
                        'source' => 'hacker_news',
                        'title' => $this->cleanText($title),
                        'url' => $url,
                        'description' => 'News from Hacker News',
                        'published_at' => now(),
                        'metadata' => [
                            'type' => 'news',
                            'source_url' => 'https://news.ycombinator.com/'
                        ]
                    ]);

                    $scrapedCount++;
                    Log::info("Successfully saved news item: " . $scrapedCount);

                    // Small delay to be respectful
                    usleep(100000); // 0.1 second

                } catch (\Exception $e) {
                    Log::warning('Failed to parse news item: ' . $e->getMessage());
                }
            });

            Log::info("News scraping completed. Saved {$scrapedCount} items");

        } catch (RequestException $e) {
            Log::error("News scraping failed: " . $e->getMessage());
            // Fallback to test data
            $this->scrapeNewsFallback($company);
        } catch (\Exception $e) {
            Log::error("News scraping general error: " . $e->getMessage());
            $this->scrapeNewsFallback($company);
        }
    }

    public function scrapeEcommerce(Company $company): void
    {
        try {
            Log::info("Starting e-commerce scraping for company: " . $company->id);
            
            // Use a simple e-commerce demo site
            $response = $this->client->get('https://webscraper.io/test-sites/e-commerce/allinone', [
                'delay' => 1000,
                'timeout' => 30,
            ]);

            $html = $response->getBody()->getContents();
            $crawler = new Crawler($html);
            $scrapedCount = 0;

            Log::info("E-commerce test site loaded successfully");

            // Scrape products from the test e-commerce site
            $crawler->filter('.thumbnail')->each(function (Crawler $node) use ($company, &$scrapedCount) {
                try {
                    $titleElement = $node->filter('.title')->first();
                    $priceElement = $node->filter('.price')->first();
                    $descriptionElement = $node->filter('.description')->first();

                    if ($titleElement->count() === 0) {
                        return;
                    }

                    $title = $titleElement->attr('title') ?? $titleElement->text();
                    $priceText = $priceElement->count() > 0 ? $priceElement->text() : '';
                    $description = $descriptionElement->count() > 0 ? $descriptionElement->text() : '';

                    // Extract price number
                    $price = $this->extractPrice($priceText);

                    Log::info("Found product: " . substr($title, 0, 50) . "...");

                    ScrapedData::create([
                        'company_id' => $company->id,
                        'source' => 'webscraper_test',
                        'title' => $this->cleanText($title),
                        'url' => 'https://webscraper.io' . $titleElement->attr('href'),
                        'description' => $this->cleanText($description),
                        'price' => $price,
                        'published_at' => now(),
                        'metadata' => [
                            'type' => 'product',
                            'source_url' => 'https://webscraper.io/test-sites/e-commerce/allinone'
                        ]
                    ]);

                    $scrapedCount++;
                    Log::info("Successfully saved product: " . $scrapedCount);

                    // Small delay to be respectful
                    usleep(100000); // 0.1 second

                } catch (\Exception $e) {
                    Log::warning('Failed to parse product: ' . $e->getMessage());
                }
            });

            Log::info("E-commerce scraping completed. Saved {$scrapedCount} products");

        } catch (RequestException $e) {
            Log::error("E-commerce scraping failed: " . $e->getMessage());
            // Fallback to test data
            $this->scrapeEcommerceFallback($company);
        } catch (\Exception $e) {
            Log::error("E-commerce scraping general error: " . $e->getMessage());
            $this->scrapeEcommerceFallback($company);
        }
    }

    /**
     * Fallback method with test data if scraping fails
     */
    private function scrapeNewsFallback(Company $company): void
    {
        Log::info("Using fallback news data for company: " . $company->id);
        
        $newsItems = [
            [
                'title' => 'Breaking: New Technology Revolutionizes Industry',
                'url' => 'https://example.com/news/tech-revolution',
                'description' => 'Groundbreaking technology changes how we work and live.',
                'published_at' => now()->subDays(1),
            ],
            [
                'title' => 'Global Markets Show Strong Growth',
                'url' => 'https://example.com/news/markets-growth',
                'description' => 'Stock markets worldwide experience significant gains.',
                'published_at' => now()->subHours(6),
            ],
            [
                'title' => 'Environmental Summit Reaches Agreement',
                'url' => 'https://example.com/news/environment-summit',
                'description' => 'World leaders agree on new climate change measures.',
                'published_at' => now()->subHours(2),
            ]
        ];

        foreach ($newsItems as $item) {
            ScrapedData::create(array_merge($item, [
                'company_id' => $company->id,
                'source' => 'fallback_news',
                'metadata' => ['type' => 'news']
            ]));
        }

        Log::info("Fallback news data saved: 3 items");
    }

    /**
     * Fallback method with test data if scraping fails
     */
    private function scrapeEcommerceFallback(Company $company): void
    {
        Log::info("Using fallback e-commerce data for company: " . $company->id);
        
        $products = [
            [
                'title' => 'Wireless Bluetooth Headphones Pro',
                'url' => 'https://example.com/products/headphones-pro',
                'description' => 'High-quality wireless headphones with advanced features.',
                'price' => 149.99,
                'published_at' => now(),
            ],
            [
                'title' => 'Smart Fitness Watch Series X',
                'url' => 'https://example.com/products/fitness-watch',
                'description' => 'Advanced fitness tracking with health monitoring.',
                'price' => 249.99,
                'published_at' => now(),
            ],
            [
                'title' => 'Portable Power Bank 20000mAh',
                'url' => 'https://example.com/products/power-bank',
                'description' => 'High-capacity portable charger for all your devices.',
                'price' => 59.99,
                'published_at' => now(),
            ],
            [
                'title' => 'Mechanical Gaming Keyboard',
                'url' => 'https://example.com/products/gaming-keyboard',
                'description' => 'Professional mechanical keyboard for gaming and work.',
                'price' => 89.99,
                'published_at' => now(),
            ]
        ];

        foreach ($products as $product) {
            ScrapedData::create(array_merge($product, [
                'company_id' => $company->id,
                'source' => 'fallback_ecommerce',
                'metadata' => ['type' => 'product']
            ]));
        }

        Log::info("Fallback e-commerce data saved: 4 items");
    }

    private function cleanText(string $text): string
    {
        return trim(preg_replace('/\s+/', ' ', $text));
    }

    private function extractPrice(string $priceText): ?float
    {
        // Extract numeric value from price string like "$25.99" or "£30.50"
        if (preg_match('/[0-9]+\.?[0-9]*/', $priceText, $matches)) {
            return (float) $matches[0];
        }
        return null;
    }

    public function getScrapedData(Company $company, array $filters = [])
    {
        $query = ScrapedData::where('company_id', $company->id);

        if (isset($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        if (isset($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['type'])) {
            $query->where('metadata->type', $filters['type']);
        }

        return $query->orderBy('published_at', 'desc')->paginate(20);
    }

    public function getScrapedDataForExport(Company $company, array $filters = [])
    {
        $query = ScrapedData::where('company_id', $company->id);

        if (isset($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        if (isset($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['type'])) {
            $query->where('metadata->type', $filters['type']);
        }

        return $query->orderBy('published_at', 'desc')->get();
    }
}