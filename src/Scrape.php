<?php

namespace App;

use Symfony\Component\CssSelector\CssSelectorConverter;
use Symfony\Component\DomCrawler\Crawler;

require 'vendor/autoload.php';

class Scrape
{
    private array $products = [];



    public function run(): void
    {
        $document = ScrapeHelper::fetchDocument('https://www.magpiehq.com/developer-challenge/smartphones');
        $aTags = $document->filter("#pages div a");
        $availablePages = [];
        for($aTagsIndex =0; $aTagsIndex < $aTags->count(); $aTagsIndex++){
            if( strpos($aTags->eq($aTagsIndex)->attr("href"), "?page=") !== false )
                $availablePages[] = $aTags->eq($aTagsIndex)->attr("href");
        }

        //first page
        $this->products = ScrapeHelper::extractProducts($document);

        //other consecutive pages
        for($page =1; $page < count($availablePages); $page++){

            $document = ScrapeHelper::fetchDocument( str_replace("..", ScrapeHelper::SITE_BASE_URL, $availablePages[$page]) );
            $pageProducts = ScrapeHelper::extractProducts($document);

            //merge and remove duplicates if any by checking name, colour and capacity
            $this->products = array_unique(array_merge($this->products,$pageProducts), SORT_STRING );
        }

        file_put_contents('output.json', json_encode($this->products, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) );

    }

}

$scrape = new Scrape();
$scrape->run();
