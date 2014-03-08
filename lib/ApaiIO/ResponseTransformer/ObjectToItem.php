<?php

/**
 * 
 */

namespace ApaiIO\ResponseTransformer;

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
        $this->set( 'title', 'ItemAttributes', 'Title' );
        $this->set( 'manufacturer', 'ItemAttributes', 'Manufacturer' );
        $this->set( 'author', 'ItemAttributes', 'Author' );
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
        $this->set( 'reviews', 'CustomerReviews', 'IFrameURL' );
        $this->set_array( 'features', 'ItemAttributes', 'Feature' );
        
        $this->get_price();
        $this->get_description();
        $this->get_category();
        $this->get_image_sets();

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
            return $this->item = $response['Items']['Item'];
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
    protected function set($data, $key1, $key2 = NULL, $key3 = NULL)
    {
        if( $key3 )
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

    private function get_price()
    {
        $list_price = isset( $this->item['ItemAttributes']['ListPrice']['Amount'] ) ? $this->item['ItemAttributes']['ListPrice']['Amount'] : NULL;
        $amazon_price = isset( $this->item['Offers']['Offer']['OfferListing']['Price']['Amount'] ) ? $this->item['Offers']['Offer']['OfferListing']['Price']['Amount'] : NULL;
        $saved = isset( $this->item['Offers']['Offer']['OfferListing']['AmountSaved'] ) ? $this->item['Offers']['Offer']['OfferListing']['AmountSaved']['Amount'] : NULL;
        $price = ($list_price) ? $list_price : ($amazon_price ? ($amazon_price + $saved) : NULL );
        $this->data['price'] = ($price) ? $price : $this->data['lowest_new_price'];
    }

    /**
     *
     * @param type $i
     */
    private function get_description()
    {
        $this->set( 'description', 'EditorialReviews', 'EditorialReview', 'Content' );
        if( isset( $this->data['description'] ) )
        {
            $this->data['description'] = strip_tags( $this->data['description'] );
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

            $sets = $this->item['ImageSets'];

            foreach( $sets as $set )
            {
                $row = array();
                if( isset( $set['MediumImage']['URL'] ) )
                {
                    $row['medium_image'] = $set['MediumImage']['URL'];
                }
                if( isset( $set['LargeImage']['URL'] ) )
                {
                    $row['large_image'] = $set['LargeImage']['URL'];
                }
                $this->data['image_sets'][] = $row;
            }
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
