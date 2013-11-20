<?php

namespace ApaiIO\ResponseTransformer;

class ObjectToResult extends ObjectToArray implements ResponseTransformerInterface
{
    public function transform($response)
    {
        $response = $this->buildArray($response);
		
		$data = array();
        if( isset($response['Items']['Item']) )
        {
            foreach( $response['Items']['Item'] as $item )
            {
                $row = array();
				$row['asin'] = $item['ASIN'];
                $row['price'] = isset($item['OfferSummary']['LowestNewPrice']['FormattedPrice']) ? $item['OfferSummary']['LowestNewPrice']['FormattedPrice']:'';
                $row['title'] = $item['ItemAttributes']['Title'];
                $row['image'] = $item['SmallImage']['URL'];
                
                $data[] = $row;
            }
        }   
        return $data;
    }
}
