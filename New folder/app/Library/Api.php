<?php

/**
 * Send APi handler
 * @author Pravin S <webreak.pravin@gmail.com>
 *
 */

namespace App\Library;

use \GuzzleHttp\Client as HttpClient;

class Api
{
    /**
     * [__construct description]
     *
     *
     */
    public function __construct()
    {

    }

    /**
     * send request to api
     * @param [type] GET | POST request type
     * @param [point] url start with slash
     * @param [params] post fields
     * @author Pravin S <webreak.pravin@gmail.com>
     */

    public static function sendRequest($type, $point, $params = [], $append = [])
    {
        $client = new HttpClient();

        $response = $client->request($type, env("API_SERVICE_ENDPOINT") . $point, [
            "form_params" => $params,
        ]);

        if ($response->getStatusCode() == 200) {
            return (json_decode((string) $response->getBody(),true));
        }

        \Log::erorr($response);

        return ["code" => 500, "data" => [], "message" => "Oops, something went wrong"];
    }

    /**
     * send withfile request to api
     * @param [type] GET | POST request type
     * @param [point] url start with slash
     * @param [params] post fields
     * @author Pravin S <webreak.pravin@gmail.com>
     */

    public static function sendWithFileRequest($type, $point, $params = [], $append = [])
    {
        $client = new HttpClient();

        $response = $client->request($type, env("API_SERVICE_ENDPOINT") . $point, [
            "multipart" => $params,
        ]);

        if ($response->getStatusCode() == 200) {
            return (json_decode((string) $response->getBody(),true));
        }

        \Log::erorr($response);

        return ["code" => 500, "data" => [], "message" => "Oops, something went wrong"];
    }

}
