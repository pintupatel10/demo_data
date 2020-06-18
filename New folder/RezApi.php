<?php

/**
 * core class of the call services for the product insert get and update
 * @author Pravin S <iipl.pravins@gmail.com>
 * @date 07-17-2017
 **/


namespace App\Libraries;
use Session;
use Request;
use Illuminate\Support\Facades\Config;

class RezApi {
    
    private $connection = null;
    public $productDomain = null;
    public  $logger = null;
    private $apiKey = null;


    public function __construct($needApiLog = true)
    {
        $handler = [];
        if ($needApiLog) {
            $handler = ['handler' => $this->createLoggingHandlerStack([
                    '{method} {uri} HTTP/{version} {req_body}',
                    'RESPONSE: {code} - {res_body}',
                ])
            ];
        }
        $this->connection = new \GuzzleHttp\Client($handler);

        $this->productDomain = config("services.PRODUCT_SERVICE_END_POINT");
        $this->orderDomain = config("services.ORDER_SERVICE_END_POINT");
		$this->searchDomain = config("services.SEARCH_SERVICE_END_POINT");
		$this->affiliateDomain = config("services.AFFILIATE_SERVICE_END_POINT");
		$this->imgServiceDomain = config("services.IMAGE_UPLOAD_SERVICE_END_POINT");
        $this->mailServiceDomain = config("services.MAIL_UPLOAD_SERVICE_END_POINT");
        $this->externalServiceDomain = config("services.EXTERNAL_SERVICE_END_POINT");
    }

    public function setAPIKey($configApiKey = null)
    {
        if(!empty($configApiKey)){
     		$this->apiKey = (env("APP_ENV") == "prod") ? $configApiKey->api_key : $configApiKey->sandbox_api_key;
        }
    }

    public function getStaffId()
    {
        return !empty(config('app.currentStaffId')) ? config('app.currentStaffId') : Config::get('currentStaffId');
    }

    private function getCommonHeader(array $extHeaders = [])
    {
        return array_merge([
            "current-staff-id" => $this->getStaffId(),
        ], $extHeaders);
    }

    public function affiliateGetRequest($url, $attributes = [], $type = "GET")
    {
		$attributesSend = "";
        if(!empty($attributes)){
            $attributesSend = "?".http_build_query($attributes);
         }
        
        $res = $this->connection->request(
            $type,
            $this->affiliateDomain.$url.$attributesSend,
            [
				"headers" => $this->getCommonHeader(["api-key" => \Config::get('apiKey')])
			]
        );
        
        $var =  $res->getBody();
        
        return json_decode($var);
        
    }

	public function affiliatePostRequest( $url, $requests = [] )
	{
		$res = $this->connection->request(
			'POST',
			$this->affiliateDomain.$url,
			[
				"form_params" => $requests,
				"headers" => $this->getCommonHeader(["api-key" => \Config::get('apiKey')])
				
			]
		);
		$var = $res->getBody();
		return json_decode($var);
	}

    public function searchProducts($url, $attributes = [], $type = "GET")
    {
        //print_r($this->searchDomain.$url.$attributesSend);exit;
        $attributesSend = "";
        if (!empty($attributes)) {
            $attributesSend = "?".http_build_query($attributes);
        }

        if (!empty(config('app.apiKey'))) {
            $configApiKey = config('app.apiKey');
        } else {
            $configApiKey = \Config::get('apiKey');
        }

        $res = $this->connection->request(
            $type,
            $this->searchDomain.$url.$attributesSend,
            [
                "headers" => $this->getCommonHeader(["api-key" => $configApiKey])
            ]
        );

        $var =  $res->getBody();

        return json_decode($var);
    }

    public function updateCnProducts($id)
    {
        $attributesSend = "";
        if(!empty($attributes)){
            $attributesSend = "?".http_build_query($attributes);
        }

        //dd($this->searchDomain.'/generate/563234');
        $res = $this->connection->request(
            'GET',
            $this->searchDomain.'/cn/generate/'.$id.'?needLog=false',
            [
                "headers" => $this->getCommonHeader(["api-key" => config('app.apiKey')])
            ]
        );

        $var =  $res->getBody();

        return json_decode($var);

    }

	
    public function refreshProductDB($id = 0)
    {
		return $this->_sendGetRequest("/event/notify/".$id);
    }
	
	public function syncToT4f($productId = 0,$isDiffrentPrice = 1)
	{
		if ((int)$productId > 0) {
			return $this->_sendGetRequest("/sync-to-t4f/".$productId."/".$isDiffrentPrice);
		}
	}
    
    public function findProducts($attributes = [])
    {
        return $this->_sendGetRequest("/tours",$attributes);
    }
	
	public function getPassengerInfo($id = 0, $is_provider = false)
	{
		$resRoute = ($is_provider) ? "/provider/".$id."/passenger" : "/product-information/".$id."/passenger";
		return $this->_sendGetRequest($resRoute);
	}
	
	public function setPassengerInfo($requests, $id = 0, $is_provider = false)
	{
		$resRoute = ($is_provider) ?  "/provider/".$id."/passenger" : "/product-information/".$id."/passenger";
		return $this->_sendPostRequest($resRoute,$requests);
	}
	
    /*
	* use for the get request
	* @author Pravin Solanki <iipl.pravins@gmail.com>
	* @params url [string]
	* @params attributes [array, mixed]
	**/
    
    public function _sendGetRequest($url, $attributes = [], $type = "GET")
    {
		$attributesSend = "";
        $prefix = trim($url,"/");
        $prefix = explode("/", $prefix);
        if (!empty($prefix[0]) && !empty($_REQUEST['cookie_language']) && in_array($prefix[0], ['provider-product-request', 'product-information'])) {
            $attributes['cookie_language'] = $_REQUEST['cookie_language'];
        }
        
        if(!empty($attributes)){
            $query = parse_url($url, PHP_URL_QUERY);
            $attributesSend = (empty($query) ? "?" : "&") . http_build_query($attributes);
        }
        
        if(empty($apiKey)){
              //This condition apply bcz call access product service url from order service - CreateOrder Process
              if(!empty(config('app.apiKey'))){
                              $configApiKey = config('app.apiKey');
              }else{
                       $configApiKey = \Config::get('apiKey');
              }
        }
        else {
            $configApiKey = $apiKey;
        }

        $res = $this->connection->request(
            $type,
            $this->productDomain.$url.$attributesSend,
            [
		    	"headers" => $this->getCommonHeader(["api-key" => $configApiKey])
			]
        );
        
        $var =  $res->getBody();
        
        return json_decode($var);
        
    }

    public function _sendExternalGetRequest($url, $attributes = [], $type = "GET")
    {
		$attributesSend = "";
        if(!empty($attributes)){
            $attributesSend = "?".http_build_query($attributes);
        }
        
        $res = $this->connection->request(
            $type,
            $this->externalServiceDomain.$url.$attributesSend
        );
        
        $var =  $res->getBody();
     
        return json_decode($var);
        
    }
	
	/* Product Upgrade API Start */
	
	public function getUpgrade($id = 0)
	{
		
		if ((int)$id > 0) {
			
			$res = $this->connection->request(
				
				'GET',
				
				$this->productDomain."/product-information/".$id."/upgrade",
				
				[
					'headers'	=> ['Connection' => 'keep-alive'],
					'curl'		=> [CURLOPT_TCP_NODELAY => true],
					"headers" => ["api-key" => \Config::get('apiKey')]
				]
				
			);
			
			$var = $res->getBody();
            
            return json_decode($var);
			
		}
		
	}
	
	public function setUpgrade($id = 0,$requests)
	{
		if ((int)$id > 0) {
			
			$res = $this->connection->request(
				
				'POST',
				
				$this->productDomain."/product-information/".$id."/upgrade/save",
				
				[
					"form_params" => $requests,
					"headers" => $this->getCommonHeader(["api-key" => \Config::get('apiKey')])
				]
				
			);
			
			$var = $res->getBody();
            
            return json_decode($var);
			
		}
		
	}
	
	public function deleteUpgrade($id = 0,$upgrade_id,$option_id)
	{
		if ((int)$id > 0) {
			
			$res = $this->connection->request(
				
				'POST',
				
				$this->productDomain."/product-information/".$id."/upgrade/".$upgrade_id."/option/".$option_id."/delete",
				[
					'headers' => $this->getCommonHeader(["api-key" => \Config::get('apiKey')])
				]
				
			);
			
			$var = $res->getBody();
            
            return json_decode($var);
			
		}
		
	}
	
	/* Product Upgrade API End */
	
	/* Default Product Create */
	
	public function productCreate($product_line ,$provider_id)
	{
		if ( !empty($product_line) && !empty($provider_id) ) {
			
			$res = $this->connection->request(
				
				'POST',
				
				$this->productDomain."/product/".$product_line."/create/".$provider_id,
				[
					'headers' => $this->getCommonHeader(["api-key" => \Config::get('apiKey')])
				]
				
			);
			
			$var = $res->getBody();
            
            return json_decode($var);
			
		}
		
	}
	
	/* Default Product End */
	
	/* get price Calculation Start */
	
	public function getPriceCalculation($id = 0,$requests)
	{
		if ((int)$id > 0) {
			
			$res = $this->connection->request(
				
				'POST',
				
				$this->productDomain."/price/".$id."/price-calculate",
				
				[
					"form_params" => $requests,
					"headers" => $this->getCommonHeader(["api-key" => \Config::get('apiKey')])
				]
				
			);
			
			$var = $res->getBody();
            
            return json_decode($var);
			
		}
		
	}
	
	/* get price Calculation End */
	
	/* PROMO Start */
	
	public function savePromo($requests)
	{
		$res = $this->connection->request(
			'POST',
			$this->productDomain."/promo/save",
			[
				"form_params" => $requests,
				"headers" => ["api-key" => \Config::get('apiKey')]
			]
		);
		$var = $res->getBody();
		return json_decode($var);
	}
	public function getPromoList()
	{
		$res = $this->connection->request(
			'GET',
			$this->productDomain."/promo/list/",
			["headers" => ["api-key" => \Config::get('apiKey')]]
		);
		$var =  $res->getBody();
		return json_decode($var);
	}
	
	public function getPromo( $promoId = 0 )
	{
		if( $promoId > 0  ) {
            $res = $this->connection->request(
                'GET',
                $this->productDomain."/promo/".$promoId,
				["headers" => ["api-key" => \Config::get('apiKey')]]
            );
            $var =  $res->getBody();
            return json_decode($var);
        }
	}
	public function deletePromo( $promoId = 0 )
	{
		if( $promoId > 0  ) {
            $res = $this->connection->request(
                'GET',
                $this->productDomain."/promo/delete/".$promoId,
				["headers" => ["api-key" => \Config::get('apiKey')]]
            );
            $var =  $res->getBody();
            return json_decode($var);
        }
	}
	/* PROMO End */

	/* Set customer Start */
	public function saveCustomer($requests)
	{
		$res = $this->connection->request(
			'POST',
			$this->productDomain."/customer/save",
			[
				"form_params" => $requests,
				"headers" => $this->getCommonHeader(["api-key" => \Config::get('apiKey')])
			]
		);
		$var = $res->getBody();
		return json_decode($var);
	}
	
	public function getCustomerList($relation)
	{
		if( !empty( $relation ) ) {
            $res = $this->connection->request(
                'GET',
                $this->productDomain."/customer/list/".$relation,
                ["headers" => ["api-key" => \Config::get('apiKey')]]
            );
            $var =  $res->getBody();
            return json_decode($var);
        }
	}
	
	public function getCustomer( $customerId = 0,$relation = 'provider' )
	{
		if( !empty( $relation ) ) {
            $res = $this->connection->request(
                'GET',
                $this->productDomain."/customer/".$customerId."/".$relation,
                ["headers" => $this->getCommonHeader(["api-key" => \Config::get('apiKey')])]
            );
            $var =  $res->getBody();
            return json_decode($var);
        }
	}

	public function deleteCustomer( $customerId = 0,$relation = 'provider' )
	{
		if( !empty( $relation ) ) {
            $res = $this->connection->request(
                'GET',
                $this->productDomain."/customer/delete/".$customerId."/".$relation,
                ["headers" => $this->getCommonHeader(["api-key" => \Config::get('apiKey')])]
            );
            $var =  $res->getBody();
            return json_decode($var);
        }
	}
	/* Set customer End */
	
	/*
	* use for the post request
	* @author Pravin Solanki <iipl.pravins@gmail.com>
	* @params url [string]
	* @params request [array, mixed]
	**/
	
	public function _sendPostRequest( $url, $requests = [], $postAsJson = false)
	{
        if(empty($apiKey)){
		if(!empty(config('app.apiKey'))){
			$configApiKey = config('app.apiKey');
                }else{
                        $configApiKey = \Config::get('apiKey');
                }
        }
        else
        {
            $configApiKey = $apiKey;
        }
        $postKey = $postAsJson ? "json" : "form_params";
        
        $prefix = trim($url,"/");
        $prefix = explode("/", $prefix);
        
        if (!empty($prefix[0]) && !empty($_REQUEST['cookie_language']) && in_array($prefix[0], ['provider-product-request', 'product-information'])) {
            $requests['cookie_language'] = $_REQUEST['cookie_language'];
        }

		$res = $this->connection->request(
			'POST',
			$this->productDomain.$url,
			[
				$postKey => $requests,
				"headers" => $this->getCommonHeader(["api-key" => $configApiKey])
			]
		);
		$var = $res->getBody();
		return json_decode($var);
	}

	/*
	* use for the order post request
	* @author Nayan Makwana <nayanm.bipl@gmail.com>
	* @params url [string]
	* @params request [array, mixed]
	**/
	
	public function _sendOrderGetRequest($url, $attributes = [], $type = "GET")
    {
		$attributesSend = "";
        if(!empty($attributes)){
            $attributesSend = "?".http_build_query($attributes);
         }

        if (!empty($attributes['book_platform']) && $attributes['book_platform'] == 'system_operator' && $this->apiKey != null) {
        	$res = $this->connection->request(
            	$type,
            	$this->orderDomain.$url.$attributesSend,
            	["headers" => ["api-key" => $this->apiKey]]
        	);
        }
        else{
        	$res = $this->connection->request(
            	$type,
            	$this->orderDomain.$url.$attributesSend,
            	["headers" => ["api-key" => \Config::get('apiKey')]]
        	);
        }
        
        $var =  $res->getBody();
        
        return json_decode($var);
        
    }

	public function _sendOrderPostRequest( $url, $requests = [] )
	{

		if(!empty(config('app.apiKey'))){
			$configApiKey = config('app.apiKey');
        }else{
    		$configApiKey = \Config::get('apiKey');
        }
        if (!empty($requests['book_platform']) && $requests['book_platform'] == 'system_operator' && $this->apiKey != null) {
        	$configApiKey = $this->apiKey;
        }
		$res = $this->connection->request(
			'POST',
			$this->orderDomain.$url,
			[
				"form_params" => $requests,
				"headers" => $this->getCommonHeader(["api-key" => $configApiKey])
				
			]
		);
		$var = $res->getBody();
		return json_decode($var);
	}

    public function uploadLocalFile($filePath, $isPrivate = 0, $prefixPath = '')
    {
        if (!is_file($filePath)) {
            throw new \Exception($filePath . " is an invalid file path");
        }

        $body = [
            'file'       => class_exists('\CURLFile') ? new \CURLFile(realpath($filePath)) : '@'.realpath($filePath),
            'isPrivate'  => $isPrivate,
            'prefixPath' => $prefixPath
        ];
		$res = $this->connection->request(
			'POST',
			$this->imgServiceDomain."/upload",
			[
				"form_params" => $body,
				"headers" => ["api-key" => \Config::get('apiKey'), 'content-type' => 'multipart/form-data']
			]
		);
		$var = $res->getBody();
		return json_decode($var);

    }

    public function uploadFile($fileAreaName,$isFileLocal = 0, $allowValidation = 'yes')
    {
        if (!$fileAreaName) {
            throw new \Exception("fileAreaname is required!");
        }
        if ($isFileLocal == 0) {
        	 if (!$_FILES || !isset($_FILES[$fileAreaName]) || $_FILES[$fileAreaName]['error']) {
            	throw new \Exception($fileAreaName . ' not found in $_FILES');
        	}
        	$fileInfo = $_FILES[$fileAreaName];
        	$resultUploadFile = $this->uploadFileStream(
           	 	file_get_contents($fileInfo['tmp_name']),
            	$this->getFileExt($fileInfo['name']),
            	$this->getFileName($fileInfo['name']),
            	$allowValidation
        	);
        }
        else{
        	$filepath = (base_path('public/').$fileAreaName);
        	$resultUploadFile = $this->uploadFileStream(
            	file_get_contents($filepath),
            	$this->getFileExt($fileAreaName),
            	$this->getFileName($fileAreaName),
            	$allowValidation
        	);
        }
       
        return $resultUploadFile;
    }
    
    public function uploadFiles($fileAreaName, $allowValidation = 1)
    {
        if (!$fileAreaName) {
            throw new \Exception("fileAreaname is required!");
        }
        
        $total = count($_FILES[$fileAreaName]['name']);
        $resultUploadFile ='';
        $imageNames = Array();
        $fileInfo = $_FILES[$fileAreaName];

        for( $i=0 ; $i < $total ; $i++ ) {
            $resultUploadFile = $this->uploadFileStream(
                file_get_contents($fileInfo['tmp_name'][$i]),
                $this->getFileExt($fileInfo['name'][$i]),
                $this->getFileName($fileInfo['name'][$i]),
                $allowValidation
            );
            if ($resultUploadFile->code == 200) {
           		$imageNames[] = $resultUploadFile->data->url;
            } else {
            	$resultUploadFile = $resultUploadFile;
            }
        }
        
        if ($resultUploadFile->code == 200) {
        	$resultUploadFile->data->url = implode(",",$imageNames);
        }
       
        return $resultUploadFile;
    }


    public function uploadFileStream($fileStream, $fileExt = '', $fileName = '', $allowValidation = '')
    {
        if (!$fileStream) {
            throw new \Exception("fileStream is required");
        }
        
        $body = [
            'fileStream'      => $fileStream,
            'fileExt'         => $fileExt,
            'fileName'        => $fileName,
            'allowValidation' => $allowValidation
        ];

        if(!empty(config('app.apiKey'))){
			$configApiKey = config('app.apiKey');
        }else{
    		$configApiKey = \Config::get('apiKey');
        }

        $res = $this->connection->request(
			'POST',
			$this->imgServiceDomain."/uploadStream",
			[
				"form_params" => $body,
				"headers" => ["api-key" => $configApiKey]
			]
		);

        $var = $res->getBody();
		return json_decode($var);

    }

    private function getFileExt($fileName)
    {
        if (!$fileName) {
            return '';
        }

        return (strrpos($fileName, ".") === false) ? '' : substr($fileName, strrpos($fileName, ".") + 1);
    }

    private function getFileName($fileName)
    {
        if (!$fileName) {
            throw new \Exception("File name error!");
        }
        return substr($fileName, 0, strrpos($fileName, "."));
    }
    
    /**
     * Send mail to agent / admin / operator or customer
     * @param $url|STRING
     * @param $requests|ARRAY
     * @return ARRAY
     * @author Anjana Janani<bipl.anjana@gmail.com>
     * @date 2018-02-01
    **/
    public function sendMail($url, $requests = [])
    {
        $res = $this->connection->request(
			'POST',
			$this->mailServiceDomain.$url,
			[
				"form_params" => $requests
			]
		);
		$var = $res->getBody();
		return json_decode($var);
    }

    private function createLoggingHandlerStack(array $messageFormats)
    {
        $stack = \GuzzleHttp\HandlerStack::create();

        collect($messageFormats)->each(function ($messageFormat) use ($stack) {
            // We'll use unshift instead of push, to add the middleware to the bottom of the stack, not the top
            $stack->unshift(
                $this->createGuzzleLoggingMiddleware($messageFormat)
            );
        });

        return $stack;
    }

    
    private function createGuzzleLoggingMiddleware( $messageFormat)
    {
        return \GuzzleHttp\Middleware::log(
            $this->getLogger(),
            new \GuzzleHttp\MessageFormatter($messageFormat)
        );
    }

    private function getLogger()
    {
        if (!$this->logger) {
            $logPath = getRezbLogPath('api-log');
            $this->logger = with(new \Monolog\Logger('api-consumer'))->pushHandler(
                //new \Monolog\Handler\RotatingFileHandler(storage_path('logs/api-log.log'))
                new \Monolog\Handler\RotatingFileHandler($logPath)
            );
        }

        return $this->logger;
    }
    
    /*
	* use for the post request
	* @author Chintan S <chintan.bipl@gmail.com>
	* @params url [string]
	* @params request [array, mixed]
	**/

	public function sendPostCatalogRequest( $url, $requests = [], $languageId = 1)
	{
		if($languageId == 1){
			$catalogurl = \App\Models\Old\Configuration::getVal('CATALOG_URL');
		}else{
        	$catalogurl = \App\Models\Es\Configuration::getVal('CATALOG_URL');
		}

        $catalogurl = trim($catalogurl, '/').'/';
        $url = trim($url, '/');
		$res = $this->connection->request(
			'POST',
			$catalogurl.$url,
			[
				"form_params" => $requests
			]
		);
		$var = $res->getBody();
		return json_decode($var);
	}
    
    /*
	* use for the post request
	* @author Chintan S <chintan.bipl@gmail.com>
	* @params url [string]
	* @params request [array, mixed]
	**/

	public function sendPostNewOrderServiceRequest( $url, $requests = [], $languageId = 1)
	{
		if($languageId == 1){
        	$servicesUrl = \App\Models\Old\Configuration::getVal('NEW_T4F_ORDER_SERVICE_URL');
		} else {
			$servicesUrl = \App\Models\Es\Configuration::getVal('NEW_T4F_ORDER_SERVICE_URL');
		}
        $servicesUrl = trim($servicesUrl, '/').'/';
        $url = trim($url, '/');
        
        $res = $this->connection->request(
			'POST',
			$servicesUrl.$url,
			[
				"json" => $requests
			]
		);
		$var = $res->getBody();
		return json_decode($var);
	}

	public function postT4fOrderList($url ,$requests = [] , $encryptKey = '' ,$languageId = 1) {
        
        if ($languageId == 1){
        	$catalogurl = \App\Models\Old\Configuration::getVal('CATALOG_URL');
        } else {
        	$catalogurl = \App\Models\Es\Configuration::getVal('CATALOG_URL');
        }
        
        $catalogurl = trim($catalogurl, '/').'/';

        $res = $this->connection->request(
            "POST",
            $catalogurl.$url,
            [
				'form_params' => $requests,
				'headers'     => ["encryptKey" => $encryptKey]
			]
        );
      	
      	$var =  $res->getBody();

        return json_decode($var);
    }

    public function postNewT4fOrderList($url, $attributes = [], $languageId = 1) {
        
        if ($languageId == 1) {
        	if(env('APP_ENV') == 'local'){
    			$catalogurl = "http://order.services.qa.tours4fun.com/";
    		} elseif (env('APP_ENV') == 'dev') {
        		$catalogurl = "http://order.services.dev.tours4fun.com/";
    		} elseif (env('APP_ENV') == 'qa') {
        		$catalogurl = "http://order.services.qa.tours4fun.com/";
    		} elseif (env('APP_ENV') == 'prod') {
        		$catalogurl = "http://order.services.tours4fun.com/";
    		}
    	} else {
    		if(env('APP_ENV') == 'local'){
    			$catalogurl = "http://order.services.qa.tours4fun.es/";
    		} elseif (env('APP_ENV') == 'dev') {
        		$catalogurl = "http://order.services.dev.tours4fun.es/";
    		} elseif (env('APP_ENV') == 'qa') {
        		$catalogurl = "http://order.services.qa.tours4fun.es/";
    		} elseif (env('APP_ENV') == 'prod') {
        		$catalogurl = "http://order.services.tours4fun.es/";
    		}
        }
        
        $catalogurl = trim($catalogurl, '/').'/';
       
        $attributesSend = "";
        if(!empty($attributes)){
            $attributesSend = "?".http_build_query($attributes);
        }
        
        $res = $this->connection->request(
            "GET",
            $catalogurl.$url.$attributesSend
        );
        
        $var =  $res->getBody();
        
        return json_decode($var);
    }

    public function postEditT4fOrderDetails($url ,$requests = [] , $encryptKey = '' ,$languageId = 1) {
        
        if ($languageId == 1){
        	$catalogurl = \App\Models\Old\Configuration::getVal('CATALOG_URL');
        } else {
        	$catalogurl = \App\Models\Es\Configuration::getVal('CATALOG_URL');
        }
        
        $catalogurl = trim($catalogurl, '/').'/';
        
        $res = $this->connection->request(
            "POST",
            $catalogurl.$url,
            [
				'form_params'  => $requests,
				'headers'      => ["encryptKey" => $encryptKey]
			]
        );
      	
      	$var =  $res->getBody();
        
        return json_decode($var);
    }

    public function postEditNewT4fOrderDetails($url ,$orderProductId = null , $attributes = [] ,$languageId = 1) {
        
        if ($languageId == 1){
        	if(env('APP_ENV') == 'local'){
    			$catalogurl = "http://order.services.qa.tours4fun.com/";
    		} elseif (env('APP_ENV') == 'dev') {
        		$catalogurl = "http://order.services.dev.tours4fun.com/";
    		} elseif (env('APP_ENV') == 'qa') {
        		$catalogurl = "http://order.services.qa.tours4fun.com/";
    		} elseif (env('APP_ENV') == 'prod') {
        		$catalogurl = "http://order.services.tours4fun.com/";
    		}
        } else {
        	if(env('APP_ENV') == 'local'){
    			$catalogurl = "http://order.services.qa.tours4fun.es/";
    		} elseif (env('APP_ENV') == 'dev') {
        		$catalogurl = "http://order.services.dev.tours4fun.es/";
    		} elseif (env('APP_ENV') == 'qa') {
        		$catalogurl = "http://order.services.qa.tours4fun.es/";
    		} elseif (env('APP_ENV') == 'prod') {
        		$catalogurl = "http://order.services.tours4fun.es/";
    		}
        }
        
        $catalogurl = trim($catalogurl, '/').'/';

        $attributesSend = "";
        if(!empty($attributes)){
            $attributesSend = "?".http_build_query($attributes);
        }

        $res = $this->connection->request(
            "GET",
            $catalogurl.$url.$orderProductId.$attributesSend
        );
      	
      	$var =  $res->getBody();
        
        return json_decode($var);
    }

    public function postT4fOrderUpdate($url ,$requests = [] , $encryptKey = '' ,$languageId = 1) {
        
        if ($languageId == 1){
        	$catalogurl = \App\Models\Old\Configuration::getVal('CATALOG_URL');
        } else {
        	$catalogurl = \App\Models\Es\Configuration::getVal('CATALOG_URL');
        }
        
        $catalogurl = trim($catalogurl, '/').'/';
        
        $res = $this->connection->request(
            "POST",
            $catalogurl.$url,
            [
				'form_params'  => $requests,
				'headers'      => ["encryptKey" => $encryptKey]
			]
        );
      	
      	$var =  $res->getBody();
        
        return json_decode($var);
    }

    public function postNewT4fOrderUpdate($url ,$requests = [] ) {
        
        if(env('APP_ENV') == 'local'){
			$catalogurl = "http://order.services.qa.tours4fun.es/";
		} elseif (env('APP_ENV') == 'dev') {
    		$catalogurl = "http://order.services.dev.tours4fun.es/";
		} elseif (env('APP_ENV') == 'qa') {
    		$catalogurl = "http://order.services.qa.tours4fun.es/";
		} elseif (env('APP_ENV') == 'prod') {
    		$catalogurl = "http://order.services.tours4fun.es/";
		}
        
        $catalogurl = trim($catalogurl, '/').'/';
        
        $res = $this->connection->request(
            "POST",
            $catalogurl.$url,
            [
				'form_params'  => $requests
			]
        );
      	
      	$var =  $res->getBody();
        
        return json_decode($var);
    }
    
    public function getT4fOrderPreview($url, $languageId = 1) {
        
        if($languageId == 1){
        	$catalogurl = \App\Models\Old\Configuration::getVal('CATALOG_URL');
        }else{
        	$catalogurl = \App\Models\Es\Configuration::getVal('CATALOG_URL');
        }
        $catalogurl = trim($catalogurl, '/').'/';
        
        $res = $this->connection->request(
            "GET",
            $catalogurl.$url,
            []
        );
        $var =  $res->getBody();
        
        return json_decode($var);
        
    }

    public function getNewT4fOrderPreview($url, $attributes = []) {
        
        if(env('APP_ENV') == 'local'){
			$catalogurl = "http://order.services.qa.tours4fun.es/";
		} elseif (env('APP_ENV') == 'dev') {
    		$catalogurl = "http://order.services.dev.tours4fun.es/";
		} elseif (env('APP_ENV') == 'qa') {
    		$catalogurl = "http://order.services.qa.tours4fun.es/";
		} elseif (env('APP_ENV') == 'prod') {
    		$catalogurl = "http://order.services.tours4fun.es/";
		}
        
        $catalogurl = trim($catalogurl, '/').'/';

        $attributesSend = "";
        if(!empty($attributes)){
            $attributesSend = "?".http_build_query($attributes);
        }

        $res = $this->connection->request(
            "GET",
            $catalogurl.$url.$attributesSend
        );
        $var =  $res->getBody();
        
        return json_decode($var);
        
    }
    
    public function getT4fOrderPrint($url, $languageId = 1) {
        
        if($languageId == 1){
        	$catalogurl = \App\Models\Old\Configuration::getVal('CATALOG_URL');
        }else{
        	$catalogurl = \App\Models\Es\Configuration::getVal('CATALOG_URL');
        }
        $catalogurl = trim($catalogurl, '/').'/';
        
        $res = $this->connection->request(
            "GET",
            $catalogurl.$url,
            []
        );
        $var =  $res->getBody();
        return json_decode($var);
        
    }
    
    /*
	* use for the post request
	* @author Chintan Patel <chintans.bipl@gmail.com>
	* @params url [string]
	* @params serviceName [string]
	* @params request [array, mixed]
	**/
	public function sendPostRequestT4fService( $serviceName, $url, $requests = [] )
	{
		$service = [
			'ALL_PRODUCT' => config("services.T4F_ALL_PRODUCT_SERVICE_URL"),
		];

		if (!empty($service[$serviceName])) {
			$serviceUrl = rtrim($service[$serviceName], '/').'/'.ltrim($url, '/');
			$res = $this->connection->request(
				'POST',
				$serviceUrl,
				[
					'json' => $requests
				]
			);
			$var = $res->getBody();
			return json_decode($var);
		}
	}

	/*
	* use for the post request
	* @author Chintan Patel <chintans.bipl@gmail.com>
	* @params url [string]
	* @params request [array, mixed]
	**/
	public function sendPostRequestTffService($url, $requests = [])
	{
		$service = config("services.TFF_OPEN_API_URL");

		if (!empty($service)) {
			$serviceUrl = rtrim($service, '/').'/'.ltrim($url, '/');

			$appkey = config("services.TFF_OPEN_API_KEY");
			$appsecret = config("services.TFF_OPEN_API_SECRET");

			$t = time();
			$queryParams = [
                'appkey' => $appkey,
                't'     => $t,
                'sign'  => md5($appkey . $t . $appsecret.json_encode($requests)),
                'lang' => 'en'
            ];

			$res = $this->connection->request(
				'POST',
				$serviceUrl,
				[
					'json' => $requests,
					'query' => $queryParams
				]
			);
			$var = $res->getBody();
			return json_decode($var);
		}
	}

    /**
     * This function call t4f associate services
     * @param  [string] [$url] [action name]
     * @param  [array] [$requests] [parameter array]
     * @return [json]
     * @author Sagar Ankoliya <sagara.bipl@gmail.com> | 01 February 2019 (Friday)
     */
    public function newT4fAssociatePostRequest($url, $requests = [])
    {
        // $servicesUrl = \App\Models\Old\Configuration::getVal('NEWT4F_ASSOCIATE_SERVICE_END_POINT');
        $servicesUrl = config("services.NEWT4F_ASSOCIATE_SERVICE_END_POINT");
        if (!empty($servicesUrl) && !empty($url)) {
            $servicesUrl = trim($servicesUrl, '/').'/';
            $url = trim($url, '/');
            
            $res = $this->connection->request(
               'POST',
               $servicesUrl.$url,
               [
                   "json" => $requests
               ]
            );

            $var = $res->getBody();
            return json_decode($var);
        }
    }

    /**
     * Do ticket stock request.
     *
     * @param  string $endPoint
     * @param  string $method
     * @param  array  $request
     * @param  array  $queryParams
     * @return mixed
     *
     * @author Milan Chhaniyara <milanc.bipl@gmail.com>
     * @date   2019-04-24 06:40 PM
     */
    public function ticketStockRequest($endPoint, $method = "GET", array $request = [], array $queryParams = [])
    {
        $servicesUrl = env('TICKET_STOCK_APP_URL');
        $endPoint    = trim($endPoint, '/');
        if (!empty($servicesUrl) && in_array($method, ["POST", "GET"])) {
            if ($method == 'GET' && !empty($request)) {
                $queryParams = array_merge($request, $queryParams);
            }

            $servicesUrl = trim($servicesUrl, '/').'/';
            $response = $this->connection->request(
               $method,
               $servicesUrl.$endPoint,
               [
                   'json'  => $request,
                   'query' => $queryParams
               ]
            );

            return ($response->getStatusCode() == 200) ? json_decode($response->getBody(), true): false;
        }

        return false;
    }
    
    /**
     * <This function will send post request to the new t4f admin>
     * @param  : [str] [$url] [<Url on which post request to be sent>] | [array] [$requests] [<Data which needs be sent with post request>]
     * @return : [obj] [<Return values from the post request>]
     * @author : Rushikesh Oza <rushikesho.bipl@gmail.com> | 30 July 2019 (Tuesday)
     * Last Updated By : Rushikesh Oza <rushikesho.bipl@gmail.com> | 30 July 2019 (Tuesday)
     */
    public function newT4fAdministratorPostRequest($url = '', $requests = [])
    {
        $servicesUrl = config("services.NEW_ADMIN_DOMAIN");
        
        if (!empty($servicesUrl) && !empty($url)) {
            $servicesUrl = trim($servicesUrl, '/').'/';
            $url = trim($url, '/');
            
            $res = $this->connection->request(
               'POST',
               $servicesUrl.$url,
               [
                   "json" => $requests
               ]
            );
            
            $var = $res->getBody();
            return json_decode($var);
        }
    }
    
    /**
     * <This function will sync new admin product to rezb2b>
     * @param  : [array / integer] [$productIds] [<Product Ids which needs to be synced>]
     * @return : [response] [<Call response>]
     * @author : Rushikesh Oza <rushikesho.bipl@gmail.com> | 30 July 2019 (Tuesday)
     * Last Updated By : Rushikesh Oza <rushikesho.bipl@gmail.com> | 30 July 2019 (Tuesday)
     */
    public function syncNewAdminProduct($productIds = [])
    {
        if (!empty($productIds)) {
            if ((int) $productIds > 0) {
                $postRequest['oldProductIds'][] = $productIds;
            } else {
                $postRequest['oldProductIds'] = $productIds;
            }
            
            $postRequest['syncSign'] = '12wxt4336V799Bp8I7P6r19L90';
            return $this->newT4fAdministratorPostRequest('new-admin-to-rezb2b-sync-api', $postRequest);
        }
    }

    public function callT4FApiPost($route, $requests = [])
    {
        $serviceUrl = config("services.T4F_API_URL");

        if (!empty($serviceUrl)) {
            $serviceUrl = trim($serviceUrl, '/').'/';
            $route      = trim($route, '/');

            $response = $this->connection->request(
               'POST',
               $serviceUrl.$route,
               [
                   "form_params" => $requests
               ]
            );
            
            $var = $response->getBody();
            return json_decode($var);
        }

        return false;
    }

    public function updateAlgoliaProduct($productId)
    {
        if (!empty($productId)) {
            return $this->callT4FApiPost("/algolia/update", ["product_id" => (int)$productId]);
        }

        return false;
    }

    public function updateTtdProductsExpiry($params = [])
    {
        $attributesSend = "";
        if(!empty($attributes)){
            $attributesSend = "?".http_build_query($attributes);
        }

        $res = $this->connection->request(
            'POST',
            $this->productDomain.'/v1/ttd/updateTtdExpiry?needLog=false',
            [
                "form_params" => $params,
                "headers"     => $this->getCommonHeader(["api-key" => config('app.apiKey')])
            ]
        );

        $var =  $res->getBody();

        return json_decode($var);
    }

}
