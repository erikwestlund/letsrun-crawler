<?php

namespace App\Jobs;

use Curl;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use App\Page;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CrawlPage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $page;
    protected $sleep;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Page $page)
    {
        $this->page = $page;
        $this->sleep = config('crawler.pause_seconds');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // curl the page & time it
        $time_start = microtime(true);
        $response = Curl::to($this->page->url)
            ->withOption('USERAGENT', config('crawler.user_agent'))
            ->get();

        $time_elapsed = round(microtime(true) - $time_start, 2);

        // save result
        $this->page->last_crawled = Carbon::now();
        $this->page->seconds_to_load = $time_elapsed;
        $this->page->save();

        // sleep
        sleep($this->sleep);

        // log it
        Log::info('Crawled ' . $this->page->url . ' in ' . $time_elapsed . ' seconds.');
    }
}
