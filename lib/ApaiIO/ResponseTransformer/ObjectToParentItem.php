<?php

/**
 * 
 */

namespace ApaiIO\ResponseTransformer;

/**
 * 
 */
class ObjectToParentItem extends ObjectToArray implements ResponseTransformerInterface {

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

        // Sometimes there's no variation even there's a valid parentASIN
        if ( !isset( $this->item['Variations']['VariationDimensions']) )
        {
            return array();
        }
        $this->set( $this->item, $this->data, 'asin', 'ASIN' );
        $this->set_array( $this->item, $this->data, 'variation_dimensions', 'Variations', 'VariationDimensions', 'VariationDimension');
        $this->get_items();
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
    protected function set($source, &$dest, $data, $key1, $key2 = NULL, $key3 = NULL, $key4 = NULL)
    {
        if( $key4 )
        {
            if( isset( $source[$key1][$key2][$key3][$key4] ) )
            {
                $dest[$data] = $source[$key1][$key2][$key3][$key4];
            }
        }
        elseif( $key3 )
        {
            if( isset( $source[$key1][$key2][$key3] ) )
            {
                $dest[$data] = $source[$key1][$key2][$key3];
            }
        }
        elseif( $key2 )
        {
            if( isset( $source[$key1][$key2] ) )
            {
                $dest[$data] = $source[$key1][$key2];
            }
        }
        else
        {
            if( isset( $source[$key1] ) )
            {
                $dest[$data] = $source[$key1];
            }
        }
    }

    protected function set_array($source, &$dest, $data, $key1, $key2 = NULL, $key3 = NULL)
    {
        $this->set( $source, $dest, $data, $key1, $key2, $key3 );
        $this->make_array_if_singular($dest[$data]);
    }

    /**
     * Parses the Amazon reviews iframe to get precise numeric review metrics
     */
    private function get_items()
    {
        $items = array();
        if( isset( $this->item['Variations']['Item'] ) )
        {
            $source_items = $this->item['Variations']['Item'];
            $this->make_array_if_singular($source_items);

            foreach( $source_items as $item )
            {
                $new_item = array();
                $this->set($item, $new_item, 'asin', 'ASIN' );
                //$this->get_price_for_item($item, $new_item);
                $this->set_variation_attributes($item, $new_item);
                $items[] = $new_item;
            }
        }
        $this->data['variation_items'] = $items;
    }

    private function set_variation_attributes($source, &$dest)
    {
        if ( isset( $source['VariationAttributes']['VariationAttribute'] ) )
        {
            $source_variation_attributes = $source['VariationAttributes']['VariationAttribute'];
            $this->make_array_if_singular($source_variation_attributes);
            $variation_attributes = array();
            foreach( $source_variation_attributes as $variation )
            {
                $variation_attributes[] =
                    array( $variation['Name'] => $variation['Value'] );
            }
            $dest['variation_attributes'] = $variation_attributes;
        }


        $dest['variation_attributes'] = $variation_attributes;
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

    private function get_price_for_item($source, &$dest)
    {
        $list_price = isset( $source['ItemAttributes']['ListPrice']['Amount'] ) ? $source['ItemAttributes']['ListPrice']['Amount'] : NULL;
        $amazon_price = isset( $source['Offers']['Offer']['OfferListing']['Price']['Amount'] ) ? $source['Offers']['Offer']['OfferListing']['Price']['Amount'] : NULL;
        $saved = isset( $source['Offers']['Offer']['OfferListing']['AmountSaved'] ) ? $source['Offers']['Offer']['OfferListing']['AmountSaved']['Amount'] : NULL;
        $price = ($list_price) ? $list_price : ($amazon_price ? ($amazon_price + $saved) : NULL );
        $dest['price'] = ($price) ? $price : (isset($dest['lowest_new_price']) ? $dest['lowest_new_price'] : 0);
    }

    /**
     * @param $dest
     * @param $data
     */
    private function make_array_if_singular(&$object)
    {
        if ( isset($object) )
        {
            if ( !is_array($object) || !isset( $source_items[0] )) {
                $object = array($object);
            }
        }
    }
}
