<?php
include 'InformationApi.php';
/**
 * Created by PhpStorm.
 * User: erm
 * Date: 17-05-17
 * Time: 11:51
 */

class AGLinkConverter_Linkchanger extends AGLinkConverter_InformationApi
{

    private $c_id;
    public function __construct($c_id)
    {
        $this->c_id = $c_id;
    }


    /**
     * @param $string
     * @param null $one
     * @param null $two
     * @return string
     */
   public function stritr($string, $one = NULL, $two = NULL){

        if(  is_string( $one )  ){
            $two = strval( $two );
            $one = substr(  $one, 0, min( strlen($one), strlen($two) )  );
            $two = substr(  $two, 0, min( strlen($one), strlen($two) )  );
            $product = strtr(  $string, ( strtoupper($one) . strtolower($one) ), ( $two . $two )  );
            return $product;
        }
        else if(  is_array( $one )  ){
            $pos1 = 0;
            $product = $string;
            while(  count( $one ) > 0  ){
                $positions = array();
                foreach(  $one as $from => $to  ){
                    if(   (  $pos2 = stripos( $product, $from, $pos1 )  ) === FALSE   ){
                        unset(  $one[ $from ]  );
                    }
                    else{
                        $positions[ $from ] = $pos2;
                    }
                }
                if(  count( $one ) <= 0  )break;
                $winner = min( $positions );
                $key = array_search(  $winner, $positions  );
                $product = (   substr(  $product, 0, $winner  ) . $one[$key] . substr(  $product, ( $winner + strlen($key) )  )   );
                $pos1 = (  $winner + strlen( $one[$key] )  );
            }
            return $product;
        }
        else{
            return $string;
        }
    }/* endfunction stritr */


    /**
     * @param $url
     * @return bool
     */
    public function isHomepageLink($url)
    {

        $url_array = parse_url($url);
        return (isset($url_array['path']) && $url_array['path'] == '/' &&  strpos($url,'cid') !== false );
    }


    public function transformPopulairDest($keyword)
    {
        $api = $this->call_api(array('q'=>$keyword,'t'=>'populair_dest'));
        if(isset($api['url'])) {

        }
        return $keyword;
    }


    /**
     * Lazy match if this is an old hotel url link..
     * @param $url
     * @return bool
     */
    public function isOldHotelLink($url)
    {
        $url_array = parse_url($url);
        if(isset($url_array['path'])) {
            $split_url = explode('/',$url_array['path']);
            return count($split_url) == 5;
        } else {
            return false;
        }
    }

    /**
     * @param $url
     * @return string
     */
    public function transformOldHotelLinks($url)
    {
        $url_array = parse_url($url);
        if(isset($url_array['path'])) {
            $split_url = explode('/',$url_array['path']);
            $hotel_name = ucwords(str_replace('_',' ',$split_url[4]));
            $api = $this->call_api(array('q'=>$hotel_name,'t'=>'hotel'));
            if(!empty($api['hotel_id'])) {
                $url = 'https://www.agoda.com/partners/partnersearch.aspx?cid='.$this->c_id.'&hid='.$api['hotel_id']."&pcs=4";
            }
        }

        return $url;

    }



    /**
     * @param $url
     * @return string
     */
    public function transformHotelLinksNew($url)
    {
        $url_array = parse_url($url);
        if(isset($url_array['path'])) {
            $split_path = explode('/',$url_array['path']);

            if(isset($split_path[1])) {
                $hotel_name = ucwords(str_replace('-',' ',$split_path[1]));
                $api = $this->call_api(array('q'=>$hotel_name,'t'=>'hotel'));
                if(!empty($api['hotel_id'])) {
                    $url = 'https://www.agoda.com/partners/partnersearch.aspx?cid='.$this->c_id.'&hid='.$api['hotel_id'].'&pcs=4';
                }


            }
        }
        return $url;
    }


    /**
     * @param $url
     * @return bool
     */
    public function isLandmarkLink($url)
    {
        return strpos($url,'/attractions/') !== false;
    }


    /**
     * @param $url
     * @return bool
     */
    public function isHotelLinkNew($url)
    {
        return strpos($url,'/hotel/') !== false;

    }


    /**
     * @param $url
     * @return bool
     */
    public function isAreaLink($url)
    {
        return strpos($url,'/maps/') !== false;
    }


    /**
     * @param $content
     * @return array
     */
    public function getCharactersBetween($content)
    {
        $s = explode("</a>",$content);
        $ahrefs = array();
        foreach ($s as $k ){
            if (strpos($k,"href" ) !==FALSE ){
                $ahrefs[] = preg_replace("/^.*href=\".*\">|\">.*/sm","",$k);
            }
        }
        return $ahrefs;
    }


    /**
     * @param $content
     * @return string
     */
    public function transformPopulairDestinations($content,$max_links_per_page=0)
    {



        $populair_destinations = $this->getPopulairDestinations();
        $items_found = 0;
        $search_content = strtolower($content);
        $counter = 0;

        foreach($populair_destinations['country'] as $dests) {

            $populair_destinations['url'][$counter] =  str_replace('XXXXXX',$this->c_id, $populair_destinations['url'][$counter]);
            if($max_links_per_page  > 0  && $items_found >= $max_links_per_page) {

                break;
            }
           if(strpos($search_content,$dests) !== false) {
               $items_found ++;
           }
           $content = preg_replace("/(".$dests.")(?=[^>]*(<|$))/", '<a href="'.$populair_destinations['url'][$counter].'" target="_blank" rel="nofollow">'.ucwords($dests).'</a>', $content);
            $counter ++;
        }


       return $content;
    }

    /**
     * @return array
     */
    public function getPopulairDestinations()
    {
        $results = $this->call_api(array('q'=>'','t'=>'get_populair_dest'));
        $destinations = array();
        $counter =0;
        foreach($results['data'] as $res) {
            $destinations['country'][$counter] = trim($res[0]);
            $destinations['url'][$counter] = $res[1]."&pcs=4";
            $counter++;
        }
        return $destinations;


    }

    /**
     * @param $url
     * @return bool
     */
    public function isCityLink($url)
    {
        return strpos($url,'/city/') !== false;
    }


    /**
     * Transform the Areay link
     * @param $url
     * @return string
     */
    public function transformArealink($url)
    {

        $explode_url = explode('/',$url);
        $area_name = (isset($explode_url[3]) ? ucwords(str_replace('-',' ',$explode_url[3])) : '');

        $api = $this->call_api(array('q'=>$area_name,'t'=>'area'));

        if(!empty($api['area_id'])) {
            $url = 'https://www.agoda.com/partners/partnersearch.aspx?cid='.$this->c_id.'&area='.$api['area_id']."&pcs=4";
        }
        return $url;
    }


    /**
     * @param $url
     * @return string
     */
    public function transformLandmarkLink($url)
    {

        $explode_url = explode('/',$url);
        $landmark = (isset($explode_url[3]) ? preg_replace('/hotels-near-/','',$explode_url[3]) : '');
        $landmark = preg_replace('/-/',' ',ucwords($landmark));

        $api = $this->call_api(array('q'=>$landmark,'t'=>'landmark'));

        if(!empty($api['city_id'])) {
            $url = 'https://www.agoda.com/partners/partnersearch.aspx?cid='.$this->c_id.'&poi='.$api['city_id']."&pcs=4";
        }

        return $url;

    }


    /**
     * @param $url
     * @return string
     */
    public function transformCityLink($url)
    {
        $explode_url = explode('/',$url);
        $city = (isset($explode_url[4]) ? $explode_url[4] : '');

        if(empty($city)) {
            return $url;
        }
        $city_extract = explode('.',$city);
        if(isset($city_extract[0])) {
            $city_extract[0] = trim(substr( $city_extract[0],0,-3));
            $city = str_replace('-',' ',ucwords($city_extract[0]));
            $api = $this->call_api(array('q'=>$city,'t'=>'city'));
            if(!empty($api['city_id'])) {
                $url = 'https://www.agoda.com/partners/partnersearch.aspx?cid='.$this->c_id.'&city='.$api['city_id']."&pcs=4";
            }

        }

        return $url;
    }


    /**
     * Container changer factory
     * @param $url
     * @return string
     */
    public function contentChangerFactory($url)
    {
        $href_attributes = ' rel=nofollow';

        // return homepage link
        if($this->isHomepageLink($url)) {
            $url =  'https://www.agoda.com/partners/partnersearch.aspx?cid='.$this->c_id."&pcs=4";
            $url = $url."\"{$href_attributes}";
            return $url;
        // city link
        } elseif($this->isCityLink($url)) {
            return $this->transformCityLink($url)."\"{$href_attributes}";
        // areay
        } elseif($this->isAreaLink($url)) {
            return $this->transformArealink($url)."\"{$href_attributes}";
        // landmark
        } elseif($this->isLandmarkLink($url)) {

            return $this->transformLandmarkLink($url)."\"{$href_attributes}";
        }elseif($this->isHotelLinkNew($url)) {
           return $this->transformHotelLinksNew($url)."\"{$href_attributes}";
        // show default link
        } elseif($this->isOldHotelLink($url)) {

            return $this->transformOldHotelLinks($url)."\"{$href_attributes}";
        } else {
            return $url;
        }

    }




}