<template>
    <div>
        <h2>Data Scraping</h2>

        <!-- Scraping Controls -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Start Scraping</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <button class="btn btn-primary w-100 mb-2" @click="startScraping('news')" :disabled="loading">
                            <span v-if="loading === 'news'" class="spinner-border spinner-border-sm"></span>
                            Scrape Hacker News
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-success w-100 mb-2" @click="startScraping('ecommerce')" :disabled="loading">
                            <span v-if="loading === 'ecommerce'" class="spinner-border spinner-border-sm"></span>
                            Scrape Web scraper Best Sellers
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-info w-100 mb-2" @click="startScraping('all')" :disabled="loading">
                            <span v-if="loading === 'all'" class="spinner-border spinner-border-sm"></span>
                            Scrape All Sources
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <small class="text-muted">
                            Sources: Hacker News (News) & Web scraper Best Sellers (E-commerce)
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Filter by Source</label>
                        <select class="form-select" v-model="filters.source" @change="loadScrapedData()">
                            <option value="">All Sources</option>
                            <option value="hacker_news">Hacker News</option>
                            <option value="webscraper_test">Web scraper Best Sellers</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filter by Type</label>
                        <select class="form-select" v-model="filters.type" @change="loadScrapedData()">
                            <option value="">All Types</option>
                            <option value="news">News</option>
                            <option value="product">Products</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <div class="input-group">
                            <input type="text" class="form-control" v-model="filters.search" 
                                   placeholder="Search titles...">
                            <button class="btn btn-outline-secondary" @click="loadScrapedData()">
                                Search
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Controls -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Export Data</h5>
                    <button class="btn btn-outline-primary" @click="exportToCsv" :disabled="scrapedData.length === 0">
                        <i class="fas fa-download"></i> Export to CSV
                    </button>
                </div>
            </div>
        </div>

        <!-- Scraped Data -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Scraped Data ({{ pagination.total || 0 }} items)</h5>
                <button class="btn btn-sm btn-outline-primary" @click="loadScrapedData()">
                    Refresh
                </button>
            </div>
            <div class="card-body">
                <div v-if="scrapedData.length === 0" class="text-center py-4">
                    <p>No scraped data yet. Start scraping to see results here.</p>
                    <p class="text-muted">Try scraping from BBC News or Amazon Best Sellers.</p>
                </div>
                <div v-else class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Source</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Published</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in scrapedData" :key="item.id">
                                <td>
                                    <a :href="item.url" target="_blank" class="text-decoration-none" :title="item.title">
                                        {{ truncateText(item.title, 60) }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge" :class="getSourceClass(item.source)">
                                        {{ getSourceName(item.source) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge" :class="getTypeClass(item.metadata?.type)">
                                        {{ item.metadata?.type || 'unknown' }}
                                    </span>
                                </td>
                                <td>{{ truncateText(item.description, 80) }}</td>
                                <td>
                                    <span v-if="item.price" class="text-success fw-bold">
                                        ${{ parseFloat(item.price).toFixed(2) }}
                                    </span>
                                    <span v-else class="text-muted">N/A</span>
                                </td>
                                <td>{{ formatDate(item.published_at) }}</td>
                                <td>
                                    <a :href="item.url" target="_blank" class="btn btn-sm btn-outline-primary">
                                        Visit
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav v-if="pagination.meta && pagination.meta.last_page > 1">
                    <ul class="pagination">
                        <li class="page-item" :class="{ disabled: !pagination.links?.prev }">
                            <button class="page-link" @click="loadPage(pagination.meta.current_page - 1)">Previous</button>
                        </li>
                        <li class="page-item" v-for="page in pagination.meta.last_page" :key="page"
                            :class="{ active: page === pagination.meta.current_page }">
                            <button class="page-link" @click="loadPage(page)">{{ page }}</button>
                        </li>
                        <li class="page-item" :class="{ disabled: !pagination.links?.next }">
                            <button class="page-link" @click="loadPage(pagination.meta.current_page + 1)">Next</button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Scraping',
    data() {
        return {
            scrapedData: [],
            pagination: {},
            loading: false,
            filters: {
                source: '',
                type: '',
                search: ''
            }
        }
    },
    async mounted() {
        await this.loadScrapedData();
    },
    methods: {
        async loadScrapedData(page = 1) {
            try {
                const params = new URLSearchParams({
                    page: page,
                    ...this.filters
                }).toString();

                const response = await axios.get(`/api/scraping?${params}`);
                this.scrapedData = response.data.data || [];
                this.pagination = response.data;
            } catch (error) {
                console.error('Failed to load scraped data:', error);
                alert('Failed to load scraped data: ' + error.response?.data?.message);
            }
        },
        async loadPage(page) {
            await this.loadScrapedData(page);
        },
        async startScraping(type) {
            this.loading = type;
            try {
                const response = await axios.post('/api/scraping/scrape', { type });
                alert('Scraping completed successfully!');
                await this.loadScrapedData();
            } catch (error) {
                console.error('Failed to start scraping:', error);
                alert('Failed to start scraping: ' + error.response?.data?.message);
            } finally {
                this.loading = false;
            }
        },
        async exportToCsv() {
            try {
                const params = new URLSearchParams(this.filters).toString();
                const response = await axios.get(`/api/scraping/export-csv?${params}`, {
                    responseType: 'blob'
                });
                
                // Create download link
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `scraped-data-${new Date().toISOString().split('T')[0]}.csv`);
                document.body.appendChild(link);
                link.click();
                link.remove();
                
                alert('CSV export started successfully!');
            } catch (error) {
                console.error('Failed to export CSV:', error);
                alert('Failed to export CSV: ' + error.response?.data?.message);
            }
        },
        getSourceClass(source) {
            const classes = {
                'bbc_news': 'bg-primary',
                'amazon_best_sellers': 'bg-success'
            };
            return classes[source] || 'bg-secondary';
        },
        getSourceName(source) {
            const names = {
                'bbc_news': 'BBC News',
                'amazon_best_sellers': 'Amazon'
            };
            return names[source] || source;
        },
        getTypeClass(type) {
            const classes = {
                'news': 'bg-info',
                'product': 'bg-warning'
            };
            return classes[type] || 'bg-secondary';
        },
        truncateText(text, length) {
            if (!text) return '';
            return text.length > length ? text.substring(0, length) + '...' : text;
        },
        formatDate(date) {
            if (!date) return 'N/A';
            return new Date(date).toLocaleDateString();
        }
    }
}
</script>