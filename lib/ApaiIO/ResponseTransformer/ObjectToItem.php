<?php

namespace ApaiIO\ResponseTransformer;

class ObjectToItem extends ObjectToArray implements ResponseTransformerInterface
{
    public function transform($response)
    {
        $response = $this->buildArray($response);
		
		$item = $response['Items']['Item'];				
		$data = array();
		$data['id'] = $item['ASIN'];
		$data['title'] = $item['ItemAttributes']['Title'];
		$data['manufacturer'] = $item['ItemAttributes']['Manufacturer'];
		$data['features'] = $item['ItemAttributes']['Feature'];
		$data['price'] = isset($item['OfferSummary']['LowestNewPrice']['FormattedPrice']) ? $item['OfferSummary']['LowestNewPrice']['FormattedPrice']:'';
		$data['image'] = $item['LargeImage']['URL'];
                
        return $data;
    }
}