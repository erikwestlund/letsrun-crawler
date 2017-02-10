<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use Curl;
use Carbon\Carbon;
use App\Crawler\UpdateArchivePages;
use App\Jobs\CrawlPage;

class CrawlArchives extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:archives';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl the archives';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Starting crawl...' . "\n\n");

        $archiver = new UpdateArchivePages;
        
        // add any new archived pages to the database
        $archiver->updateArchivePageRecords();

        // get the pages that need archiving and archive them
        $pages = $archiver->pagesToCrawl();
        $this->info('Archive page repository updated.');

        foreach($pages as $page) {
            dispatch(new CrawlPage($page));
            Log::info('Dispatched job to crawl ' . $page->url);
        }

        $this->info('All jobs dispatched!');

    }
}
