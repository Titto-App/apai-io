<?php
/**
 * 
 */
namespace ApaiIO\ResponseTransformer;

/**
 * 
 */
class ObjectToItem extends ObjectToArray implements ResponseTransformerInterface
{
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
		if( ! $this->get_item( $response ) )
		{
			return array();
		}
		
		$this->set('asin', 'ASIN');
		$this->set('title', 'ItemAttributes', 'Title');
		$this->set('manufacturer', 'ItemAttributes', 'Manufacturer');
		$this->set('features', 'ItemAttributes', 'Feature');
		$this->set('price', 'OfferSummary', 'LowestNewPrice', 'FormattedPrice');
		$this->set('large_image', 'LargeImage', 'URL');
		$this->set('medium_image', 'MediumImage', 'URL');
		$this->set('small_image', 'SmallImage', 'URL');
                
        return $this->data;
    }
	
	/**
	 * 
	 * @param type $response
	 * @return mixed
	 */
	protected function get_item($response)
	{
        $response = $this->buildArray($response);	
		
		if( isset($response['Items']['Item']) AND is_array($response['Items']['Item']) )
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
	protected function set($data, $key1, $key2=NULL, $key3=NULL)
	{
		if($key3)
		{
			if( isset($this->item[$key1][$key2][$key3]) )
			{
				$this->data[$data] = $this->item[$key1][$key2][$key3];
			}
		}
		elseif($key2)
		{
			if( isset($this->item[$key1][$key2]) )
			{
				$this->data[$data] = $this->item[$key1][$key2];
			}
		}
		else
		{
			if( isset($this->item[$key1]) )
			{
				$this->data[$data] = $this->item[$key1];
			}			
		}
	}
}