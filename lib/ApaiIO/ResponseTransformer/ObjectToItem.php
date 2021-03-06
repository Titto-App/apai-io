<?php

/**
 * 
 */

namespace ApaiIO\ResponseTransformer;

use PHPHtmlParser\Dom;

/**
 * 
 */
class ObjectToItem extends ObjectToArray implements ResponseTransformerInterface {

    /**
     *
     * @var type
     */
    protected $data = array();

    /**
     *
     * @var type
     */
    protected $item = array();

    /**
     * 
     * @param type $response
     * @return type
     */
    public function transform($response)
    {
        if( !$this->get_item( $response ) )
        {
            return array();
        }

        $this->set( 'asin', 'ASIN' );
        $this->set( 'parent_asin', 'ParentASIN' );
        $this->set( 'title', 'ItemAttributes', 'Title' );
        $this->set( 'brand', 'ItemAttributes', 'Brand' );
        $this->set( 'color', 'ItemAttributes', 'Color' );
        $this->set( 'model', 'ItemAttributes', 'Model' );
        $this->set( 'os', 'ItemAttributes', 'OperatingSystem' );
        $this->set( 'isbn', 'ItemAttributes', 'ISBN' );
        $this->set( 'publisher', 'ItemAttributes', 'Publisher' );
        $this->set( 'number_of_pages', 'ItemAttributes', 'NumberOfPages' );
        $this->set( 'number_of_items', 'ItemAttributes', 'NumberOfItems' );
        $this->set( 'number_of_issues', 'ItemAttributes', 'NumberOfIssues' );
        $this->set( 'model', 'ItemAttributes', 'Model' );
        $this->set( 'label', 'ItemAttributes', 'Label' );
        $this->set( 'format', 'ItemAttributes', 'Format' );
        $this->set( 'edition', 'ItemAttributes', 'Edition' );
        $this->set( 'artist', 'ItemAttributes', 'Artist' );
        $this->set( 'description', 'EditorialReviews', 'EditorialReview', 'Content' );
        $this->set( 'lowest_new_price', 'OfferSummary', 'LowestNewPrice', 'Amount' );
        $this->set( 'large_image', 'LargeImage', 'URL' );
        $this->set( 'medium_image', 'MediumImage', 'URL' );
        $this->set( 'small_image', 'SmallImage', 'URL' );
        $this->set_array( 'author', 'ItemAttributes', 'Author' );

        $this->get_item_details();
        $this->get_reviews();
        $this->get_price();
        $this->get_description();
        $this->get_category();
        $this->get_image_sets();
        $this->get_manufacturer();

        return $this->data;
    }

    /**
     *
     * @param type $response
     * @return mixed
     */
    protected function get_item($response)
    {
        $response = $this->buildArray( $response );

        if( isset( $response['Items']['Item'] ) AND is_array( $response['Items']['Item'] ) )
        {
            if( array_key_exists( 1, $response['Items']['Item'] ) )
            {
                return $this->item = $response['Items']['Item'][0];
            }
            else
            {
                return $this->item = $response['Items']['Item'];
            }
        }
        else
        {
            return FALSE;
        }
    }

    /**
     *
     * @param type $data
     * @param type $key1
     * @param type $key2
     * @param type $key3
     */
    protected function set($data, $key1, $key2 = NULL, $key3 = NULL, $key4 = NULL)
    {
        if( $key4 )
        {
            if( isset( $this->item[$key1][$key2][$key3][$key4] ) )
            {
                $this->data[$data] = $this->item[$key1][$key2][$key3][$key4];
            }
        }
        elseif( $key3 )
        {
            if( isset( $this->item[$key1][$key2][$key3] ) )
            {
                $this->data[$data] = $this->item[$key1][$key2][$key3];
            }
        }
        elseif( $key2 )
        {
            if( isset( $this->item[$key1][$key2] ) )
            {
                $this->data[$data] = $this->item[$key1][$key2];
            }
        }
        else
        {
            if( isset( $this->item[$key1] ) )
            {
                $this->data[$data] = $this->item[$key1];
            }
        }
    }

    protected function set_array($data, $key1, $key2 = NULL, $key3 = NULL)
    {
        $this->set( $data, $key1, $key2, $key3 );
        if( isset( $this->data[$data] ) AND !is_array( $this->data[$data] ) )
        {
            $this->data[$data] = array($this->data[$data]);
        }
    }

    private function get_item_details()
    {
        $this->set_array( 'features', 'ItemAttributes', 'Feature' );
        $this->set( 'html_description', 'EditorialReviews', 'EditorialReview', 'Content' );
    }

    private function get_price()
    {
        $list_price = isset( $this->item['ItemAttributes']['ListPrice']['Amount'] ) ? $this->item['ItemAttributes']['ListPrice']['Amount'] : NULL;
        $lowest_new_price = isset( $this->item['OfferSummary']['LowestNewPrice']['Amount'] ) ? $this->item['OfferSummary']['LowestNewPrice']['Amount'] : NULL;
        $amazon_price = isset( $this->item['Offers']['Offer']['OfferListing']['Price']['Amount'] ) ? $this->item['Offers']['Offer']['OfferListing']['Price']['Amount'] : NULL;
        $saved = isset( $this->item['Offers']['Offer']['OfferListing']['AmountSaved'] ) ? $this->item['Offers']['Offer']['OfferListing']['AmountSaved']['Amount'] : NULL;
        $price = ($lowest_new_price) ? $lowest_new_price : (($list_price) ? $list_price : ($amazon_price ? ($amazon_price + $saved) : NULL ));
        $this->data['price'] = ($price) ? $price : (isset($this->data['lowest_new_price']) ? $this->data['lowest_new_price'] : 0);
    }

    private function get_description()
    {
        if(isset( $this->item['EditorialReviews']['EditorialReview'][1] ) )
        {
            $this->set( 'description', 'EditorialReviews', 'EditorialReview', 0, 'Content' );
        }
        else
        {
            $this->set( 'description', 'EditorialReviews', 'EditorialReview', 'Content' );
        }
        if( isset( $this->data['description'] ) )
        {
            $this->data['description'] = $this->html2txt( $this->data['description'] );
        }
    }

    /**
     * Parses the Amazon reviews iframe to get precise numeric review metrics
     */
    private function get_reviews()
    {
        if( isset( $this->item['CustomerReviews']['IFrameURL'] ) )
        {
            // Load the iFrame HTML from the URL returned by the API
            $text = file_get_contents( $this->item['CustomerReviews']['IFrameURL'] );

            /*
            $dom = new Dom;
            $dom->load( $text );
            $a = $dom->getElementsByClass('crIFrameReviewList');
            $b = $dom->getElementsByTag('text');
            */

            // Clean up the HTML removing scripts and other unneeded data
            $text = preg_replace( '/(<style>.+?)+(<\/style>)/i', '', $text );
            $search = array('@<script[^>]*?>.*?</script>@si',  // javascript
                            '@<style[^>]*?>.*?</style>@siU',   // style tags properly
                            '@<![\s\S]*?--[ \t\n\r]*>@',       // multi-line comments including CDATA
                            '/^\n+|^[\t\s]*\n+/m',             // empty lines
            );
            $text = trim( preg_replace( $search, '', $text ) );

            // Load HTML into SimpleXML for parsing
            libxml_use_internal_errors( TRUE );
            $dom = new \DOMDocument();
            $dom->strictErrorChecking = FALSE;
            $dom->recover = TRUE;
            $dom->loadHTML( $text );
            $xml = simplexml_import_dom( $dom );

            // Get the total amount of reviews
            $stars = $xml->xpath( "//span[@class='crAvgStars']" );
            $total_reviews = isset( $stars[0] ) ? (int)preg_replace( '/[^\d]/', '', $stars[0]->a ) : 0;

            // Get the average rating
            $summary = $xml->xpath( "//span[@class='asinReviewsSummary']" );
            if( isset($summary[0]) )
            {
                $img = $summary[0]->xpath('a/img');
                $summary = preg_replace( '/[^\d]/', '', (string)$img[0]->attributes()->alt );
                $summary = (float)((int)substr( $summary, 0, strlen($summary)-1 ))/10;
            }
            else
            {
                $summary = 0;
            }

            // Set data in response
            $this->data['reviews'] = array(
                'total' => $total_reviews,
                'average' => $summary,
            );
        }
    }

    /**
     *
     */
    private function get_image_sets()
    {
        if( isset( $this->item['ImageSets']['ImageSet'] ) AND is_array( $this->item['ImageSets']['ImageSet'] ) )
        {
            $this->data['image_sets'] = array();

            $sets = $this->item['ImageSets']['ImageSet'];
            if ( isset( $sets["Category"] ) )
            {
                $sets = array( $sets );
            }
            $dup_checker = array();
            foreach( $sets as $set )
            {
                $row = array();
                if( isset( $set['LargeImage']['URL'] ) && !in_array($set['LargeImage']['URL'], $dup_checker))
                {
                    $dup_checker[] = $set['LargeImage']['URL'];
                    $row['large_image'] = $set['LargeImage']['URL'];
                    $this->data['image_sets'][] = $row;
                }
            }
        }
    }

    private function get_manufacturer()
    {
        $this->set( 'manufacturer', 'ItemAttributes', 'Manufacturer' );
        if ( !isset($this->data['manufacturer']) || $this->data['manufacturer'] == '????')
        {
            $this->set( 'manufacturer', 'ItemAttributes', 'Brand' );
        }
    }

    private function get_category()
    {
        if( isset( $this->item['BrowseNodes']['BrowseNode'] ) AND is_array( $this->item['BrowseNodes']['BrowseNode'] ) )
        {
            if( isset( $this->item['BrowseNodes']['BrowseNode'][0] ) )
            {
                $node = $this->item['BrowseNodes']['BrowseNode'][0];
            }
            else
            {
                $node = $this->item['BrowseNodes']['BrowseNode'];
            }
            $this->data['category'] = $this->get_ancestor( $node );
        }
    }

    private function get_ancestor($node)
    {
        if( isset( $node['Ancestors'] ) AND is_array( $node['Ancestors'] ) )
        {
            return $this->get_ancestor( $node['Ancestors']['BrowseNode'] );
        }
        else
        {
            return isset( $node['Name'] ) ? $node['Name'] : 'Uncategorized';
        }
    }

}
