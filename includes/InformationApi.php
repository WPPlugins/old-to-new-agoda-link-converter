<?php

/**
 * Created by PhpStorm.
 * User: erm
 * Date: 17-05-17
 * Time: 14:32
 */
abstract class AGLinkConverter_InformationApi
{

    /**
     * @var string
     */
    private $api_url = 'http://agd-plugin.com/api.php';


    /**
     * @param $params
     * @return array|mixed|object
     */
    protected function call_api($params)
    {
        $url = $this->api_url.'?'.http_build_query($params);
        $response = wp_remote_get($url);
        return json_decode($response['body'],true);


    }

}