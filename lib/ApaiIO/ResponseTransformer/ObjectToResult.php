<?php

namespace ApaiIO\ResponseTransformer;

class ObjectToResult extends ObjectToArray implements ResponseTransformerInterface
{
    public function transform($response)
    {
        $data = array();
		$response = $this->buildArray($response);
		
        if( !isset($response['Items']['Item']) )
        {
			return $data;
		}	
		
		foreach( $response['Items']['Item'] as $item )
		{
			if( !isset($item['ItemAttributes']['Title']) ) 
			{
				continue;
			}

			$row = array();
			
			$row['asin'] = $item['ASIN'];
			$row['price'] = isset($item['OfferSummary']['LowestNewPrice']['Amount']) ? $item['OfferSummary']['LowestNewPrice']['Amount']:'';
			$row['title'] = $item['ItemAttributes']['Title'];

            if( isset($item['EditorialReviews']['EditorialReview']['Content']) )
			{
				$row['description'] = $item['EditorialReviews']['EditorialReview']['Content'];
			}

			if( isset($item['LargeImage']['URL']) )
			{
				$row['large_image'] = $item['LargeImage']['URL'];
			}
			
			if( isset($item['MediumImage']['URL']) )
			{
				$row['medium_image'] = $item['MediumImage']['URL'];
			}
			
			if( isset($item['SmallImage']['URL']) )
			{
				$row['small_image'] = $item['SmallImage']['URL'];
			}
			
			$data[] = $row;
		}
         
        return $data;
    }
}
