<?php

namespace App\Crawler;

use Carbon\Carbon;
use App\Page;

class UpdateArchivePages {
    
    protected $archive_prefix;
    protected $start;
    protected $today;

    public function __construct()
    {
        $this->expiry = Carbon::now()->subDays(config('crawler.expire_days'));
        $this->archive_prefix = config('crawler.archive_url_prefix');
        $this->start = Carbon::parse(config('crawler.archives_start'));
        $this->end = Carbon::now()->subDays(config('crawler.days_to_ignore'));
    }

    public function pagesToCrawl()
    {
        $expiry = $this->expiry;

        $pages = Page::archive()
            ->where(function($query) use ($expiry) {
                $query->whereNull('last_crawled')
                    ->orWhere('last_crawled', '<', $expiry);
            })        
            ->get();

        return $pages;
    }

    /**
     * Update the database with any new archive pages since the last time the crawler ran.
     * 
     * @return void
     */
    public function updateArchivePageRecords()
    {
        $last_page = Page::orderBy('date', 'desc')
            ->first();

        if(empty($last_page)) {
            $last_page = $this->start->subDays(1);
            $last_page_timestamp = $last_page->timestamp;
        } else {
            $last_page_timestamp = Carbon::parse($last_page->date)->timestamp;
        }

        
        
        for ($i = $last_page_timestamp; $i <= $this->end->timestamp; $i+=86400) {  
            $date = date("Y/m/d", $i);

            Page::firstOrCreate([
                'date' => Carbon::createFromTimestamp($i),
                'archive' => true,
                'url' => $this->archive_prefix . $date . '/'
            ]);
            
        }          
    }

}