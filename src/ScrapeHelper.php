<?php

namespace App;

use GuzzleHttp\Client;
use Symfony\Component\CssSelector\CssSelectorConverter;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeHelper
{
    const SITE_BASE_URL = "https://www.magpiehq.com/developer-challenge";
    const PRICE_INDEX = 0;
    const AVAILABILITY_INDEX = 1;
    const SHIPPING_INDEX = 2;
    const MEGABYTES_IN_GIGABYTE = 1000;

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function fetchDocument(string $url): Crawler
    {
        $client = new Client();

        $response = $client->get($url);

        return new Crawler($response->getBody()->getContents(), $url);
    }

    /**
     * Extract Product objects from a Crawler document and returns an array of them
     * @param Crawler $document
     * @return array
     */
    public static function extractProducts(Crawler $document): array
    {
        $products = [];
        $converter = new CssSelectorConverter(true);

        //css selectors for filtering document to get various product information
        $productsXPath = $converter->toXPath("div.product");
        $namesXPath = $converter->toXPath("span.product-name");
        $capacitiesXPath = $converter->toXPath("span.product-capacity");
        $imagesXPath = $converter->toXPath(".product img");
        $coloursXPath = $converter->toXPath("span[data-colour]");

        $productsCrawler = $document->filterXPath($productsXPath);

        //loop through each .product element in the document
        for($productsIndex =0; $productsIndex < $productsCrawler->count(); $productsIndex++){
            $productCrawler = $productsCrawler->eq($productsIndex);
            $name = $productCrawler->filterXPath($namesXPath)->text();
            $capacity = $productCrawler->filterXPath($capacitiesXPath)->text();
            $image = $productCrawler->filterXPath($imagesXPath)->extract(["src"])[0];

            //get colours as an array
            $colours = $productCrawler->filterXPath($coloursXPath)
                ->extract(["data-colour"]);

            //other information does not have specific css classes but is positioned the same for each product
            $productDivs = $productCrawler->filterXPath($converter->toXPath("div.rounded-md"));
            $otherDetails = [];
            foreach($productDivs as $node){
                $divIndex =0;
                foreach($node->childNodes as $node){
                    if($node->nodeName == "div"){
                        //price, availability and shipping details divs start from the second div
                        if($divIndex >0){
                            $otherDetails[] = trim($node->nodeValue);
                        }
                        $divIndex++;
                    }
                }
            }


            $availability = $otherDetails[self::AVAILABILITY_INDEX];
            $availabilityText = trim(str_replace("Availability:","", $availability));

            $isAvailable = strpos($availability,"In Stock") !== false;
            $shippingText = $otherDetails[self::SHIPPING_INDEX] ?? "";

            //create n product objects based on number of colours we found
            foreach($colours as $colour){
                $product = new Product();
                $product->setTitle($name);
                $product->setPrice( (float) str_replace("£","", $otherDetails[self::PRICE_INDEX] ) );
                $product->setImageUrl( str_replace("..",self::SITE_BASE_URL, $image));
                $product->setCapacityMB( self::calculateProductCapacity($capacity) );
                $product->setColour($colour);
                $product->setAvailabilityText($availabilityText);
                $product->setIsAvailable($isAvailable);
                $product->setShippingText($shippingText);
                $product->setShippingDate(self::getDateFromText($shippingText));

                $products[] = $product;
            }

        }

        return $products;
    }

    /**
     * Calculates capacity of product in MB
     * @param string $rawCapacity
     * @return int
     */
    public static function calculateProductCapacity(string $rawCapacity) : int
    {
        //check if it's already in MB
        if( strpos($rawCapacity, "MB") !== false ){
            return (int) trim(str_replace("MB", "", $rawCapacity));
        }

        //convert GB to MB
        $capacityInGB = (int) trim(str_replace("GB", "", $rawCapacity));
        return $capacityInGB * self::MEGABYTES_IN_GIGABYTE;
    }

    /**
     * @param string $text sentence that might contain a date
     * @return string a date in the format of YYYY-MM-DD or empty string
     */
    public static function getDateFromText(string $text) : string
    {
        //check if there is tomorrow in the text
        if( strpos($text, "tomorrow") !==  false )
            return date("Y-m-d", strtotime("tomorrow"));

        /*
         * date text is not standard, we will keep adding separators that determine text
         * that comes before the actual date
         */

        //todo: add more separators, there is room for improvement here
        $textSeparators = ["from","on","by","delivers","delivery","have it"];
        $text = strtolower($text);
        foreach($textSeparators as $separator){

            //check if the provided date text includes any of the separator strings
            if(strpos( $text, $separator ) !== false){

                //get the substring we assume is the date
                $tmpArray = explode($separator, $text);
                if(isset($tmpArray[1])){
                    //relying on strtotime function to check if substring is a date
                    if (($timestamp = strtotime(trim($tmpArray[1]))) !== false) {
                        return date("Y-m-d", $timestamp);
                    }
                }
            }
        }

        return "";
    }
}
