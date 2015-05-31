<?php

namespace ApaiIO\ResponseTransformer;

class ObjectToResult extends ObjectToArray implements ResponseTransformerInterface {

    public function transform($response)
    {
        $data = array();
        $response = $this->buildArray( $response );

        if( !isset( $response['Items']['Item'] ) )
        {
            return $data;
        }

        foreach( $response['Items']['Item'] as $item )
        {
            if( !isset( $item['ItemAttributes']['Title'] ) )
            {
                continue;
            }

            $row = array();

            $row['asin'] = $item['ASIN'];
            $row['title'] = strip_tags( $item['ItemAttributes']['Title'] );

            $row['category'] = $this->get_category($item);

            $row['price'] = $this->get_price($item);

            if( isset( $item['LargeImage']['URL'] ) )
            {
                $row['large_image'] = $item['LargeImage']['URL'];
            }

            if( isset( $item['MediumImage']['URL'] ) )
            {
                $row['medium_image'] = $item['MediumImage']['URL'];
            }

            if( isset( $item['SmallImage']['URL'] ) )
            {
                $row['small_image'] = $item['SmallImage']['URL'];
            }

            if( isset( $item['ItemAttributes']['ISBN'] ) )
            {
                $row['isbn'] = $item['ItemAttributes']['ISBN'];
            }

            if( isset( $item['ItemAttributes']['Edition'] ) )
            {
                $row['edition'] = $item['ItemAttributes']['Edition'];
            }

            if( isset( $item['ItemAttributes']['Author'] ) )
            {
                $author = $item['ItemAttributes']['Author'];
                if( ! is_array( $author ) )
                {
                    $author = array($author);
                }
                $row['author'] = $author;
            }

            $data[] = $row;
        }

        return $data;
    }

    private function get_price($item)
    {
        $list_price = isset( $item['ItemAttributes']['ListPrice']['Amount'] ) ?
            $item['ItemAttributes']['ListPrice']['Amount'] : NULL;

        $lowest_new_price = isset( $this->item['OfferSummary']['LowestNewPrice']['Amount'] ) ? $this->item['OfferSummary']['LowestNewPrice']['Amount'] : NULL;

        $amazon_price = isset( $item['Offers']['Offer']['OfferListing']['Price']['Amount'] ) ?
            $item['Offers']['Offer']['OfferListing']['Price']['Amount'] : NULL;

        $saved = isset( $item['Offers']['Offer']['OfferListing']['AmountSaved'] ) ?
            $item['Offers']['Offer']['OfferListing']['AmountSaved']['Amount'] : NULL;

        $price = ($lowest_new_price) ? $lowest_new_price : ($list_price) ? $list_price : ($amazon_price ? ($amazon_price + $saved) : NULL );
        return ($price) ? $price : 0;
    }

    private function get_category($item)
    {
        if( isset($item['BrowseNodes']['BrowseNode']) AND is_array($item['BrowseNodes']['BrowseNode']) )
	    {
	        if( isset($item['BrowseNodes']['BrowseNode'][0]) )
            {
                $node = $item['BrowseNodes']['BrowseNode'][0];
            }
            else
            {
                $node = $item['BrowseNodes']['BrowseNode'];
            }
            return $this->get_ancestor( $node );
        }
    }

    private function get_ancestor($node)
    {
        if(isset($node['Ancestors']) AND is_array( $node['Ancestors'] ))
        {
            return $this->get_ancestor($node['Ancestors']['BrowseNode']);
        }
        else
        {
            return isset($node['Name']) ? $node['Name']: (isset($node['BrowseNodeId']) ? $node['BrowseNodeId'] : '');
        }
    }


}
