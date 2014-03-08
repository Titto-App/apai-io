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
                $row['author'] = $item['ItemAttributes']['Author'];
            }

            $data[] = $row;
        }

        return $data;
    }

}
