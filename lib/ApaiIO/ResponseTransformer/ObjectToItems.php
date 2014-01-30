<?php
/**
 * 
 */
namespace ApaiIO\ResponseTransformer;

/**
 * 
 */
class ObjectToItems extends ObjectToArray implements ResponseTransformerInterface
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
	protected $items = array();
	
	/**
	 * 
	 * @param type $response
	 * @return type
	 */
	public function transform($response)
    {
		if( ! $this->get_items( $response ) )
		{
			return array();
		}
        $c = count( $this->items );
        for( $i=0; $i < $c; $i++ )
        {
            $this->set($i, 'asin', 'ASIN');
            $this->set($i, 'title', 'ItemAttributes', 'Title');
            $this->set($i, 'manufacturer', 'ItemAttributes', 'Manufacturer');
            $this->set_array($i, 'features', 'ItemAttributes', 'Feature');
            $this->set($i, 'author', 'ItemAttributes', 'Author');
            $this->set($i, 'publisher', 'ItemAttributes', 'Publisher');
            $this->set($i, 'number_of_pages', 'ItemAttributes', 'NumberOfPages');
            $this->set($i, 'number_of_items', 'ItemAttributes', 'NumberOfItems');
            $this->set($i, 'number_of_issues', 'ItemAttributes', 'NumberOfIssues');
            $this->set($i, 'model', 'ItemAttributes', 'Model');
            $this->set($i, 'label', 'ItemAttributes', 'Label');
            $this->set($i, 'format', 'ItemAttributes', 'Format');
            $this->set($i, 'edition', 'ItemAttributes', 'Edition');
            $this->set($i, 'artist', 'ItemAttributes', 'Artist');
            $this->set($i, 'description', 'EditorialReviews', 'EditorialReview', 'Content');
            $this->set($i, 'price', 'OfferSummary', 'LowestNewPrice', 'Amount');
            $this->set($i, 'large_image', 'LargeImage', 'URL');
            $this->set($i, 'medium_image', 'MediumImage', 'URL');
            $this->set($i, 'small_image', 'SmallImage', 'URL');
            $this->set($i, 'reviews', 'CustomerReviews', 'IFrameURL');

            $this->get_category($i);
            $this->get_image_sets($i);
        }
        return $this->data;
    }
	
	/**
	 * 
	 * @param type $response
	 * @return mixed
	 */
	protected function get_items($response)
	{
        $response = $this->buildArray($response);	
		
		if( isset($response['Items']['Item']) AND is_array($response['Items']['Item']) )
		{
			return $this->items = $response['Items']['Item'];
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
	protected function set($i, $data, $key1, $key2=NULL, $key3=NULL)
	{
		if($key3)
		{
			if( isset($this->items[$i][$key1][$key2][$key3]) )
			{
				$this->data[$i][$data] = $this->items[$i][$key1][$key2][$key3];
			}
		}
		elseif($key2)
		{
			if( isset($this->items[$i][$key1][$key2]) )
			{
				$this->data[$i][$data] = $this->items[$i][$key1][$key2];
			}
		}
		else
		{
			if( isset($this->items[$i][$key1]) )
			{
				$this->data[$i][$data] = $this->items[$i][$key1];
			}			
		}
	}
	
    protected function set_array($i, $data, $key1, $key2=NULL, $key3=NULL)
    {
        $this->set($i, $data, $key1, $key2, $key3);
        if(isset($this->data[$i][$data]) AND !is_array( $this->data[$i][$data]))
        {
            $this->data[$i][$data] = array($this->data[$i][$data]);
        }
    }

    /**
	 * 
	 */
	private function get_image_sets($i)
	{
		if( isset($this->items[$i]['ImageSets']['ImageSet'])
				AND is_array($this->items[$i]['ImageSets']['ImageSet']) )
		{
			$this->data[$i]['image_sets'] = array();
			
			$sets = $this->items[$i]['ImageSets'];
			
			foreach( $sets as $set )
			{
				$row = array();
				if( isset($set['MediumImage']['URL']) )
				{
					$row['medium_image'] = $set['MediumImage']['URL'];
				}
				if( isset($set['LargeImage']['URL']) )
				{
					$row['large_image'] = $set['LargeImage']['URL'];
				}				
				$this->data[$i]['image_sets'][] = $row;
			}
		}
	}

    private function get_category($i)
    {
        if( isset($this->items[$i]['BrowseNodes']['BrowseNode'])
				AND is_array($this->items[$i]['BrowseNodes']['BrowseNode']) )
		{
			$this->data[$i]['category'] = $this->get_ancestor(
                    $this->items[$i]['BrowseNodes']['BrowseNode'][0]
            );
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
            return $node['Name'];
        }
    }
}