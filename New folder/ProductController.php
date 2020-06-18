<?php
namespace Modules\ProductEditor\Http\Controllers\Product;

use App\Common\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Http\Requests\ValidFormRequest;
use App\Libraries\RezApi;
use App\Models\Activity\ActivityProduct;
use App\Models\Essential\ProductType;
use App\Models\Location\TourCity;
use App\Models\Location\TourCityDescription;
use App\Models\Product;
use App\Models\Provider;
use App\Models\ProviderProductRequest\ProductMaster;
use App\Models\Provider\ProviderLanguageDescription;
use App\Models\Tour\TourProduct;
use App\Models\Transportation\TransportationProduct;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;
use Validator;
use \App\Models\ProviderProductRequest\ProductStatusHistoryLog;
use App\Models\Location\Region;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use App\Models\Activity\ProductOperation as ActivityProductOperation;
use App\Models\Activity\Operation as ActivityOperation;
use App\Models\Tour\Operation as TourOperation;
use App\Models\Transportation\Operation as TransportationOperation;
use App\Models\Ttd\TtdProduct;
use App\Services\UpdateCnSphinxTaskService;

class ProductController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');

		$this->essentialDb = env('REZB2B_DB_ESSENTIAL_DATABASE');
		$this->providerDb = env('REZB2B_DB_PROVIDER_DATABASE');
		$this->productDb = env('REZB2B_DB_TOUR_DATABASE');
		$this->activityDb = env('REZB2B_DB_ACTIVITY_DATABASE');
		$this->transportationDb = env('REZB2B_DB_TRANSPORTATION_DATABASE');
        $this->ttdDb = env('REZB2B_DB_TTD_DATABASE');
        $this->rezApi = new RezApi;
    }

    /**
     * Show available activity product list.
     * @param \Illuminate\Http\Request
     */
    public function listActivityProduct() {
        
        return view('product.activity-product');
    }
    
    public function createActivityProduct() {
        return view('product.create-activity-product');
    }
    
    public function createActivityProductDesc() {
        
        return view('product.activity-product-description');
    }
	
	/**
     * Show available transportation product list.
     * @param \Illuminate\Http\Request
     */
    public function listTransportationProduct() {
        
        return view('product.transportation-product');
    }
    
    public function createTransportationProduct() {
        return view('product.create-transportation-product');
    }
    
    public function createTransportationProductDesc() {
        
        return view('product.transportation-product-description');
    }
	
	//Get All Product
    public function allProduct()
    {
    	$columnName = request('sort', 'id');
        $page       = request('page', 1);
        $perPage    = request('perPage', 20);
        $sortby     = request('sortby', config('app.SORT_DESC'));
		$languageId = 1;

		$validator = \Validator::make(request()->all(),[
            'is_published'  => 'in:1,0',
            'is_published_es'  => 'in:1,0',
            'is_published_cn'  => 'in:1,0',
            'type_id'   	=> 'integer',
            'page'   		=> 'integer',
        ]);

        if ($validator->fails() ) {
            abort("404");
        }

        if (!in_array(strtolower($sortby), [config('app.SORT_ASC'), config('app.SORT_DESC')])) {
            $sortby = config('app.SORT_DESC');
        }
        
        //$products = Product::select([
        $selectField = [
			$this->essentialDb.'.product.id as id',
			$this->essentialDb.'.product.product_seo_url as product_seo_url',
			$this->essentialDb.'.product.old_product_id as old_product_id',
			$this->essentialDb.'.product.provider_id as provider_id',
			//$this->essentialDb.'.product.product_name as product_name',
			$this->essentialDb.'.product.type_id as type_id',
			$this->essentialDb.'.product.is_published as is_published',
			$this->essentialDb.'.product.is_published_cn as is_published_cn',
			$this->essentialDb.'.product.is_published_es as is_published_es',
			$this->essentialDb.'.product.product_line as product_line',
            $this->essentialDb.'.product.product_sub_line as product_sub_line',
			$this->essentialDb.'.product.departure_city as departure_city',
			$this->essentialDb.'.product.duration as duration',
			$this->essentialDb.'.product.duration_type as duration_type',
			$this->essentialDb.'.product.provider_product_code as provider_product_code',
			$this->providerDb.'.provider.name as name',
			
			$this->productDb.'.tour_product.product_region as tour_product_region',
			$this->activityDb.'.activity_product.product_region as activity_product_region',
			$this->transportationDb.'.transportation_product.product_region as transportation_product_region',
			$this->ttdDb.'.ttd_product.product_region as ttd_product_region',
            \DB::raw("max((CASE
                WHEN ".$this->activityDb.".product_info.language_id = 1 THEN ".$this->activityDb.".product_info.name
                WHEN ".$this->productDb.".product_info.language_id = 1 THEN ".$this->productDb.".product_info.name
                WHEN ".$this->transportationDb.".product_info.language_id = 1 THEN ".$this->transportationDb.".product_info.name
                WHEN ".$this->ttdDb.".ttd_product_description.language_id = 1 THEN ".$this->ttdDb.".ttd_product_description.name
                ELSE '' END)) as product_en_name"),
            \DB::raw("max((CASE
                WHEN ".$this->activityDb.".product_info.language_id = 2 THEN ".$this->activityDb.".product_info.name
                WHEN ".$this->productDb.".product_info.language_id = 2 THEN ".$this->productDb.".product_info.name
                WHEN ".$this->transportationDb.".product_info.language_id = 2 THEN ".$this->transportationDb.".product_info.name
                WHEN ".$this->ttdDb.".ttd_product_description.language_id = 2 THEN ".$this->ttdDb.".ttd_product_description.name
                ELSE '' END)) as product_es_name"),
            \DB::raw("max((CASE
                WHEN ".$this->activityDb.".product_info.language_id = 3 THEN ".$this->activityDb.".product_info.name
                WHEN ".$this->productDb.".product_info.language_id = 3 THEN ".$this->productDb.".product_info.name
                WHEN ".$this->transportationDb.".product_info.language_id = 3 THEN ".$this->transportationDb.".product_info.name
                WHEN ".$this->ttdDb.".ttd_product_description.language_id = 3 THEN ".$this->ttdDb.".ttd_product_description.name
                ELSE '' END)) as product_cn_name"),

            //$this->ttdDb.'.ttd_product.activity_type as ttd_activity_type',
            //$this->ttdDb.'.ttd_product.ticket_type as ttd_ticket_type',
		];
		
		$products = Product::LeftJoin($this->providerDb.'.provider', function ($join) {
			$join->on($this->providerDb.'.provider.provider_id', '=', $this->essentialDb.'.product.provider_id');
		});
		
		$products = $products->LeftJoin($this->productDb.'.tour_product', function ($join) {
			$join->on($this->productDb.'.tour_product.product_id', '=', $this->essentialDb.'.product.id');
		});
		
		$products = $products->LeftJoin($this->activityDb.'.activity_product', function ($join) {
			$join->on($this->activityDb.'.activity_product.product_id', '=', $this->essentialDb.'.product.id');
		});
		
		$products = $products->LeftJoin($this->transportationDb.'.transportation_product', function ($join) {
			$join->on($this->transportationDb.'.transportation_product.product_id', '=', $this->essentialDb.'.product.id');
		});

        $products = $products->LeftJoin($this->ttdDb . '.ttd_product', function ($join) {
            $join->on($this->ttdDb . '.ttd_product.product_id', '=', $this->essentialDb.'.product.id');
        });

        $products = $products->LeftJoin($this->activityDb.'.product_info', function ($join) {
			$join->on($this->activityDb.'.product_info.product_id', '=', $this->essentialDb.'.product.id');
		});

		$products = $products->LeftJoin($this->transportationDb.'.product_info', function ($join) {
			$join->on($this->transportationDb.'.product_info.product_id', '=', $this->essentialDb.'.product.id');
		});

		$products = $products->LeftJoin($this->productDb.'.product_info', function ($join) {
			$join->on($this->productDb.'.product_info.product_id', '=', $this->essentialDb.'.product.id');
		});

		$products = $products->LeftJoin($this->ttdDb.'.ttd_product_description', function ($join) {
			$join->on($this->ttdDb.'.ttd_product_description.product_id', '=', $this->essentialDb.'.product.id');
		});

		$products = $products->where('product_line', '!=', Product::PRODUCT_LINE_CUSTOM);
        $products = $products->where('product_line', '!=', '');
		$searchFields = ['id', $this->essentialDb.'.product.provider_id'];
		
        foreach ($searchFields as $field) {
            if (request($field, '')) {
                $products = $products->where($field, request($field));
            }
        }
		

        $region = request('region','');
        if ($region) {
            /*$products = $products->where($this->productDb.'.tour_product.product_region', $region);
            $products = $products->orwhere($this->activityDb.'.activity_product.product_region', $region);
            $products = $products->orwhere($this->transportationDb.'.transportation_product.product_region', $region);
            $products = $products->orwhere($this->ttdDb.'.ttd_product.product_region', $region);*/

            $products = $products->where(function ($query) use($region) {
                $query->orwhere($this->productDb.'.tour_product.product_region', $region);
                $query->orwhere($this->activityDb.'.activity_product.product_region', $region);
                $query->orwhere($this->transportationDb.'.transportation_product.product_region', $region);
                $query->orwhere($this->ttdDb.'.ttd_product.product_region', $region);
                // $query->orWhere(env('REZB2B_DB_TTD_DATABASE').'.ttd_product.ticket_type', $typeId);
            });

        }

		$duration = request('duration', null);
        if (!empty($duration)) {
        	
        	$duration_type = request('duration_type','day');

        	if (is_numeric($duration)) {
        		$products = $products->where($this->essentialDb.'.product.duration', $duration);
        	}else{
        		$durationAttr = explode('-', trim($duration));
        		if(count($durationAttr)  == 2){
        			if (is_numeric($durationAttr[0]) && is_numeric($durationAttr[1])) {
        				$products = $products->whereBetween($this->essentialDb.'.product.duration',[$durationAttr[0],$durationAttr[1]]);
        			}
        		}
        	}
            
            
            $products = $products->where($this->essentialDb.'.product.duration_type', $duration_type);
        }

		
		$productId = request('product_id', '');
		if (is_numeric(trim($productId))) {
			$products = $products->where($this->essentialDb.'.product.id', $productId);
		} else {
			$allProductIdAttr = explode('-', trim($productId));
			$lengths = count($allProductIdAttr);
			if ($lengths == 2) {
				if (is_numeric($allProductIdAttr[1]) && is_numeric($allProductIdAttr[0])) {
					$products = $products->where($this->essentialDb.'.product.id', $allProductIdAttr[1])
						                 ->where($this->essentialDb.'.product.provider_id', $allProductIdAttr[0]);
				}
				if(is_numeric($allProductIdAttr[0]) && $allProductIdAttr[1] == ''){
					$products = $products->where($this->essentialDb.'.product.provider_id', $allProductIdAttr[0]);
				}
			}
		}

		$typeId = request('type_id', 0);
		if ((int)$typeId > 0) {
            $products = $products->where(function ($query) use($typeId) {
                            $query->where($this->essentialDb.'.product.type_id', $typeId);
                            if(request('product_entity_type') == 'tour' || request('product_entity_type') == ''){
                            	$query->where($this->essentialDb.'.product.product_line','!=','tour');
                            }
                            if(request('product_entity_type') == 'ttd_ticket'){
                            	$query->where($this->essentialDb.'.product.product_sub_line','!=','ticket');
                            }
                            $query->orWhere($this->ttdDb.'.ttd_product.activity_type', $typeId);
                            // $query->orWhere(env('REZB2B_DB_TTD_DATABASE').'.ttd_product.ticket_type', $typeId);
                        });
		}
		
		$isPublished = request('is_published');
        if ($isPublished != NULL) {
            $products = $products->where($this->essentialDb.'.product.is_published', $isPublished);
        }


        $isPublishedEs = request('is_published_es');
        if ($isPublishedEs != NULL) {
            $products = $products->where($this->essentialDb.'.product.is_published_es', $isPublishedEs);
        }

        $isPublishedCn = request('is_published_cn');
        if ($isPublishedCn != NULL) {
            $products = $products->where($this->essentialDb.'.product.is_published_cn', $isPublishedCn);
        }
		
		$operators = request('operators');
		if ($operators != NULL) {
			$products = $products->whereIn($this->essentialDb.'.product.provider_id', $operators);
		}
        
        $providerProductCode = trim(request('provider_product_code'));
        if (!empty($providerProductCode)) {
            $products = $products->where($this->essentialDb.'.product.provider_product_code', 'like', '%'.$providerProductCode.'%');
        }

        $productNameSearch = trim(request('product_name'));
		if (!empty($productNameSearch)) {
            $products = $products->havingRaw('(product_en_name like "%'.$productNameSearch.'%" or product_es_name like "%'.$productNameSearch.'%" or product_cn_name like "%'.$productNameSearch.'%")');
		}

		$allApiSource = request('is_api_source');
		if ($allApiSource != NULL) {
			$products = $products->where($this->essentialDb.'.product.api_source',strtolower($allApiSource));
		}

		$productLines = Product::getProductLine();
		$productEntityType = request('product_entity_type');
		if ($productEntityType != NULL && array_key_exists($productEntityType, $productLines)) {

			if (strpos($productEntityType, Product::PRODUCT_LINE_TTD) !== false) {

				$productLine = strtolower(explode('_', $productEntityType)[0]);
				$subject = $productEntityType;
				$search = strtolower(Product::PRODUCT_LINE_TTD.'_');
				$productSublineLine = str_replace($search, '', $subject);

				$products = $products->where($this->essentialDb.'.product.product_line', $productLine)
									->where($this->essentialDb.'.product.product_sub_line', $productSublineLine);
			} else if (strpos($productEntityType, Product::PRODUCT_LINE_CRUISE) !== false) {
				$products    = $products->where($this->essentialDb.'.product.is_cruise', 1);
			} else {

				$productLine = strtolower($productEntityType);
				$products    = $products->where($this->essentialDb.'.product.product_line', $productLine);

			}
		}
        
        $products        = $products->whereRaw('FIND_IN_SET(?,product.distribution_type )',3);
		

		$getExpiredDate = request('expired_date', null);
		if($getExpiredDate){
			$expiredProducts = $this->getExpiredProduct($getExpiredDate);
			$products        = $products->whereIn($this->essentialDb.'.product.id',$expiredProducts);
			
            if(!empty($productNameSearch)) {
                $productTotalCountQ  = $products->groupBy($this->essentialDb.'.product.id')->select($selectField)->get();
                $searchCount         = count($productTotalCountQ);
                $products     = $products->orderBy($columnName, $sortby)->skip(($page * $perPage) - $perPage)->take($perPage)->get();
            } else {
                $hostedFields       = clone $products;
                $productTotalCountQ = $hostedFields->select(\DB::raw("count(distinct(".$this->essentialDb.".product.id)) as totalRecord"))->orderBy($this->essentialDb.".product.id", $sortby)->first();
                $searchCount        = ($productTotalCountQ) ? $productTotalCountQ->totalRecord : 0;    
                
                $products        = $products->groupBy($this->essentialDb.'.product.id')->orderBy($columnName, $sortby)->select($selectField)->skip(($page * $perPage) - $perPage)->take($perPage)->get();
            }

		}else{

            if(!empty($productNameSearch)) {
                $productTotalCountQ  = $products->groupBy($this->essentialDb.'.product.id')->select($selectField)->get();
                $searchCount         = count($productTotalCountQ);
                $products     = $products->orderBy($columnName, $sortby)->skip(($page * $perPage) - $perPage)->take($perPage)->get();
            } else {
                $hostedFields       = clone $products;
                $productTotalCountQ = $hostedFields->select(\DB::raw("count(distinct(".$this->essentialDb.".product.id)) as totalRecord"))->orderBy($this->essentialDb.".product.id", $sortby)->first();
                $searchCount        = ($productTotalCountQ) ? $productTotalCountQ->totalRecord : 0;    
                
                $products        = $products->groupBy($this->essentialDb.'.product.id')->orderBy($columnName, $sortby)->select($selectField)->skip(($page * $perPage) - $perPage)->take($perPage)->get();
            }
		}
        
        if($products){

            $products = new LengthAwarePaginator($products, $searchCount,$perPage,$page);
           
            $products->setPath("/product-management/product-listing?duration_type=".request('duration_type','day')."&product_id=".$productId."&product_name=".$productNameSearch."&provider_product_code=".$providerProductCode."&type_id=".$typeId."&is_published_cn=".$isPublishedCn."&is_published=".$isPublished."&is_published_es=".$isPublishedEs."&is_api_source=".$allApiSource."&product_entity_type=".$productEntityType."&region=".$region."&duration=".$duration."&expired_date=".$getExpiredDate);
        }

		$pageRecordCount = $products->count();
		//$productName = [];
		// if (!empty($products)) {
		// 	$getProductIds = $products->pluck('product_line','id');
		// 	$getProductName = Product::getProductNames($getProductIds);

		// 	foreach ($getProductName as $key => $productNames) {
		// 		$enName = isset($productNames[1]) ? $productNames[1] :'';
		// 		$esName = isset($productNames[2]) ? $productNames[2] :'';
		// 		$cnName = isset($productNames[3]) ? $productNames[3] :'';
		// 		$productName[$key] = ['en_name'=>$enName,'cn_name'=>$cnName,'es_name'=>$esName];
		// 	}
			
		// }

		// $tourType = ProductType::leftJoin('product_type_description',function($join) {
		// 	$join->on('product_type.product_type_id', '=' , 'product_type_description.product_type_id');
		// })->select('product_type.product_type_id as id', 'product_type_description.name')
		// ->where(['product_type_description.language_id'=>1, 'product_type.is_active'=>1])
		// ->pluck('product_type_description.name', 'product_type.product_type_id as id');


		$getRegion = Region::join('region_description', 'region.region_id', '=', 'region_description.region_id')
            ->where('active', 1)
            ->where('language_id', 1)
            ->orderBy('name', 'asc')
            ->pluck('region_description.name','region.region_id');

		$durationType = ['day'=>'Days','hour'=>'Hours','minute'=>'Minutes'];
		
		$expiredDate = [
			Carbon::now()->toDateString()					=>'Overdue',
			date('Y-m-d', strtotime("+6 days"))				=>'A week',
			date('Y-m-d', strtotime("+13 days"))			=>'Two week',
			date('Y-m-d', strtotime("+30 days"))			=>'One month',
			Carbon::now()->addMonths(3)->toDateString()		=>'Three month',
			Carbon::now()->addMonths(6)->toDateString()		=>'Six month'
		];
		
		$productType = ProductType::all();
		
		$form = request()->all();
        
		// $selectedProviderId = request('provider_id', 0);
		// $selectedProvider   = Provider::find($selectedProviderId);
		// if (((int)$selectedProviderId > 0) && (!empty($selectedProvider))) {
		// 	$form['provider_id'] = $selectedProviderId;
		// 	$form['provider_name'] = $selectedProvider->name;
		// }
		
		$hasDownloadAccess = \App\Models\User::checkPermission('PRODUCT_DOWNLOAD');
		$hasEditAccess     = \App\Models\User::checkPermission('PRODUCT_MODIFY');

		return view('producteditor::all_product_list', [
            'products' => $products,
            'form' => $form,
            'perPage' => $perPage,
            'page' => $page,
            'duration_type' => TourProduct::DURATION_TYPE_DAY,
            'duration_type_options' => TourProduct::durationTypeOptions(),
            'hasDownloadAccess' => $hasDownloadAccess,
			'hasEditAccess' => $hasEditAccess,
			'pageRecordCount' => $pageRecordCount,
			'productType' => $productType,
			//'tourType' => $tourType,
			'searchCount' => $searchCount,
			'getRegion'	=> 	$getRegion,
			'durationType' => $durationType,
			'expiredDate'  => $expiredDate,
			//'productName'  => $productName,
			'searchProducts' => $this->getSearchProduct($form),
			'searchOperators' => $this->getSearchOperator($form),
			'allApiSource'	=> Product::$source,
			'productsLine'	=> ['' => 'All'] + $productLines
		]);
    }
	
	public function getExpiredProduct($getExpiredDate)
	{
		$allProduct = [];
		$activityProduct = ActivityOperation::select('product_operation.product_id',DB::raw('MAX(operation.end_date) as expired_date'))
            ->leftjoin('rezb2b_activity.product_operation','product_operation.operation_id','operation.id')
            ->groupby('product_id');
            

		$tourProduct = TourOperation::select('product_id',DB::raw('MAX(operation.end_date) as expired_date'))
            ->groupby('product_id');
            
        $transportationProduct = TransportationOperation::select('product_id',DB::raw('MAX(operation.end_date) as expired_date'))
            ->groupby('product_id');
        
        $ttdProduct = TtdProduct::select('product_id','expiry_date as expired_date');
         
        if($getExpiredDate == Carbon::now()->toDateString()){
        	$activityProduct->havingRaw('FROM_UNIXTIME(expired_date,"%Y-%m-%d") < "'.Carbon::now()->toDateString().'"');
        	$tourProduct->havingRaw('FROM_UNIXTIME(expired_date,"%Y-%m-%d") < "'.Carbon::now()->toDateString().'" ');
        	$transportationProduct->havingRaw('DATE(expired_date) < "'.Carbon::now()->toDateString().'"');
        	$ttdProduct->havingRaw('FROM_UNIXTIME(expired_date) < "'.Carbon::now()->toDateString().'"');
        }else{
        	$activityProduct->havingRaw('FROM_UNIXTIME(expired_date,"%Y-%m-%d") BETWEEN "'.Carbon::now()->toDateString().'" AND "'.$getExpiredDate.'"');
        	$tourProduct->havingRaw('FROM_UNIXTIME(expired_date,"%Y-%m-%d") BETWEEN "'.Carbon::now()->toDateString().'" AND "'.$getExpiredDate.'"');
        	$transportationProduct->havingRaw('DATE(expired_date) BETWEEN "'.Carbon::now()->toDateString().'" AND "'.$getExpiredDate.'"');
        	$ttdProduct->havingRaw('FROM_UNIXTIME(expired_date,"%Y-%m-%d") BETWEEN "'.Carbon::now()->toDateString().'" AND "'.$getExpiredDate.'"');
        }
        $activityData = $activityProduct->get();
        $tourData = $tourProduct->get();
        $transportationData = $transportationProduct->get();
        $ttdProductData = $ttdProduct->get();

	    $allProduct = collect($activityData)
	        		 ->merge($tourData)
	        		 ->merge($transportationData)
	        		 ->merge($ttdProductData)
	        		 ->pluck('product_id')->toArray();
		
		return $allProduct;
	}

	public function getAllProduct($keyword = "all", RezApi $rezApi)
	{
		$page    = request("page", 1);
		$keyword = $keyword;
        if (request()->ajax()) {
            $productList = $rezApi->findProducts(["q" => $keyword, "page" => $page - 1, "limit" => 2]);

            $paginator = new Paginator([], $productList->total_found, 2, $page, [
				'path'  => request()->url(),
				'query' => request()->query(),
			]);
			
			return view('producteditor::product-search-list',["productList" => $productList, "paginator" => $paginator, "keyword" => $keyword])->render();
    	} else {
    		return view('producteditor::product-search-service',["keyword" => $keyword]);
    	}
    }
    
     public function getNetPrice($id,$agentId,Request $request,RezApi $rezApi){
        
        $param = $request->all();
        $param['used_in'] = 'order';
        //$param['agent_id']=$param['agent_Id'];
        
        $response = $rezApi->_sendPostRequest("/v1/price/$id/price-calculate",$param);

        if($response->code == 200){
            if(!isset($param['attribute_require'])){
        
                $response->data->rates;
                unset($response->data->attributes);
                unset($response->data->passengerAttributes);
            }
            return response()->json(["code" => 200,"error" => "success","agentId" =>$param['agent_id'] ,"price_detail" => $response->data]);
        }else{
            return response()->json(["code" => $response->code,"error" => $param['agent_id'],"message" =>$response->message]);
             //return response()->json(["code" => $response->code,"error" => $response->response_status,"message" =>$response->message]);
        }
     }
     
     public function getOptionDetail($id,$agentId,Request $request,RezApi $rezApi){
        
        $param    = $request->all();
        $rateInfo = (array) array_get($param, 'rate_options', []);

        if(!empty($request['api_source']) && $request['api_source'] == 'fareharbor') {
        	
        	$return['rates'] = [];
			if (!empty($request['rate_options'])) {
				foreach ($request['rate_options'] as $k => $rateOptionv) {
					foreach ($rateOptionv as $id1 => $qty) {
						$return['rates'][$id1] = [
							"rate_id" => $id1,
							"qty"     => $qty,
						];
					}
				}
			}
			$response = $rezApi->_sendPostRequest("/v1/product/$id/get-options", [
	            "departure_date" => $request->get("departure_date", "2018-10-31"),
	            "start_time" => $request->get("start_time", ""),
	            "rates" => $return,
	            "booking_field_only" => 0,
                "useAreaCode" => true,
	        ]);
        } else {
        	$response = $rezApi->_sendPostRequest("/v1/product/$id/get-options", [
                "departure_date" => $request->get("departure_date", "2018-10-31"),
                "start_time" => $request->get("start_time", ""),
                "rate_options" => $request->get("rate_options", ""),
                // if booking_field_only is 1 then it will return only passenger form [i.e exclude extra fields.]
                "booking_field_only" => 0,
                "useAreaCode" => true,
            ]);
            
        }

        $skuIds = [];
        if(!empty($rateInfo)) {
            foreach ($rateInfo as $roomId => $roomInfo) {
                foreach ($roomInfo as $rateId => $qty) {
                        $skuIds[] = $rateId;
                }
            }
        }

        if($response->code != 200) {
            return response()->json(["code" => $response->code,"error" => $param['agent_id'],"message" =>$response->message]);
        }

        //[Jaydip_20190605] [Task_3993] TTD Product level additional fields.
        $productLines        = Product::getProductsLine([$id]);
        $productLine         = array_get(array_column($productLines,'product_line','id'),$id,'');
        $productOptions      = $response->data;
        $productAddiFields   = [];
        $passengerAttributes = [];

        if($productLine == Constants::PRODUCT_LINE_TTD && !empty($skuIds) && !empty($productOptions)) {
            if (isset($productOptions->passengerAttributes) && !empty($productOptions->passengerAttributes)) {
                foreach ($productOptions->passengerAttributes as $passengerAttribute) {
                    $isRequiredForBook  = isset($passengerAttribute->required_per_booking) ? $passengerAttribute->required_per_booking : 0;
                    $useFor             = isset($passengerAttribute->use_for) ? $passengerAttribute->use_for : [];
                    if ($isRequiredForBook == 1 && (in_array("ALL", $useFor) || !empty(array_intersect($skuIds, $useFor)))) {
                        $productAddiFields[] = $passengerAttribute;
                    } else {
                        $passengerAttributes[] = $passengerAttribute;
                    }
                }
            }
            $response->data->product_addi_fields = $productAddiFields;
            $productOptions->passengerAttributes = $passengerAttributes;
        }

        if($response->code == 200){
            return response()->json(["code" => 200,"error" => "success","agentId" =>$param['agent_id'] ,"option_detail" => $response->data]);
        }else{
            return response()->json(["code" => $response->code,"error" => $param['agent_id'],"message" =>$response->message]);
        }
     }
     
    public function getPrice($id,Request $request,RezApi $rezApi){

        $param            = $request->all();
        $param['used_in'] = 'order';
        $param['book_platform'] = 'system_operator';
        $response = $rezApi->_sendPostRequest("/v1/price/$id/price-calculate",$param);

        if($response->code == 200){
            if(!isset($param['attribute_require'])){
        
                unset($response->data->rates);
                unset($response->data->attributes);
                unset($response->data->passengerAttributes);
            }
            return response()->json(["code" => 200,"error" => "success","price_detail" => $response->data]);
        }else{
            $error = isset($response->status) ? $response->status : (isset($response->response_status) ? $response->response_status : "some error");
            return response()->json(["code" => $response->code,"error" => $error,"message" =>$response->message]);
        }
    }

    public function getOptions($id,Request $request,RezApi $rezApi){

        $param            = $request->all();
        $param['used_in'] = 'order';
        $param['book_platform'] = 'system_operator';
        $response = $rezApi->_sendPostRequest("/v1/product/$id/get-options",$param);
		if($response->code == 200){
            return response()->json(["code" => 200,"error" => "success","option_detail" => $response->data]);
        }else{
            return response()->json(["code" => $response->code,"message" =>$response->message]);
        }
    }
    
    /**
     * This function will be used to download all
     * the products from the essential database
     * Author Rushikesh Oza <rushikesho.bipl@gmail.com>
     * Last Updated By : Rushikesh Oza <rushikesho.bipl@gmail.com> | 25 April 2019 (Thursday)
    **/
    public function exportAllProducts(RezApi $rezApi)
    {
        ini_set("memory_limit", "512M");
        set_time_limit('1200');
        
        $displayProductLine = [
            Product::PRODUCT_LINE_TOUR           => 'multi-day',
            Product::PRODUCT_LINE_ACTIVITY       => 'single-day',
            Product::PRODUCT_LINE_TRANSPORTATION => 'transportation',
            Constants::PRODUCT_LINE_TTD          => 'ttd'
        ];
        
        $form   = request()->all();
        $fields = (!empty($form['fields'])) ? $form['fields'] : [];
        
        $productSqlToRun = "SELECT p.id AS id,
                                    p.provider_id AS provider_id,
                                    p.product_name AS product_name,
                                    p.type_id AS type_id,
                                    p.is_published AS is_published,
                                    p.is_published_cn AS is_published_cn,
                                    p.is_published_es AS is_published_es,
                                    p.is_soldout AS is_soldout,
                                    p.is_soldout_es AS is_soldout_es,
                                    p.is_soldout_cn AS is_soldout_cn,
                                    p.product_line AS product_line,
                                    p.product_sub_line AS product_sub_line,
                                    p.departure_city AS departure_city,
                                    p.return_city AS return_city,
                                    p.duration AS duration,
                                    p.duration_type AS duration_type,
                                    p.provider_product_code AS provider_product_code,
                                    p.advertised_price AS advertised_price,
                                    p.created_at AS created_at,
                                    p.provider_name AS provider_name,
                                    p.api_source AS api_source,
                                    tp.product_region as tour_product_region,
                                    ap.product_region as activity_product_region,
                                    trp.product_region as transportation_product_region,
                                    ttd.product_region as ttd_product_region,
                                    max((CASE
                                    WHEN api.language_id = 1 THEN api.name
                                    WHEN tpi.language_id = 1 THEN tpi.name
                                    WHEN trpi.language_id = 1 THEN trpi.name
                                    WHEN ttpi.language_id = 1 THEN ttpi.name
                                    ELSE '' END)) as product_en_name,
                                    max((CASE
                                    WHEN api.language_id = 2 THEN api.name
                                    WHEN tpi.language_id = 2 THEN tpi.name
                                    WHEN trpi.language_id = 2 THEN trpi.name
                                    WHEN ttpi.language_id = 2 THEN ttpi.name
                                    ELSE '' END)) as product_es_name,
                                    max((CASE
                                    WHEN api.language_id = 3 THEN api.name
                                    WHEN tpi.language_id = 3 THEN tpi.name
                                    WHEN trpi.language_id = 3 THEN trpi.name
                                    WHEN ttpi.language_id = 3 THEN ttpi.name
                                    ELSE '' END)) as product_cn_name
                                FROM rezb2b_essential.product AS p
                                LEFT JOIN rezb2b_product.tour_product AS tp ON tp.product_id = p.id
                                LEFT JOIN rezb2b_activity.activity_product AS ap ON ap.product_id = p.id
                                LEFT JOIN rezb2b_transportation.transportation_product AS trp ON trp.product_id = p.id
                                LEFT JOIN rezb2b_ttd.ttd_product AS ttd ON ttd.product_id = p.id
                                LEFT JOIN rezb2b_activity.product_info AS api ON api.product_id = p.id
                                LEFT JOIN rezb2b_product.product_info AS tpi ON tpi.product_id = p.id
                                LEFT JOIN rezb2b_transportation.product_info AS trpi ON trpi.product_id = p.id
                                LEFT JOIN rezb2b_ttd.ttd_product_description AS ttpi ON ttpi.product_id = p.id
                                WHERE p.product_line NOT IN ('custom', '') AND p.distribution_type LIKE '%3%'";
        
        if (!empty(request('provider_id', ''))) {
            $conditionToApply = " AND p.provider_id = " . request('provider_id');
            $productSqlToRun .= $conditionToApply;
        }
        
        $nearestCity = request('nearest_city');
        if ($nearestCity != NULL) {
            $tourCity = TourCityDescription::where('name', 'like', '%'.$nearestCity.'%')->get()->pluck('tour_city_id')->toArray();
            if ($tourCity) {
                $implodedTourCities = implode(',', $tourCity);
                
                $mergeCitySql = 'SELECT GROUP_CONCAT(DISTINCT (CASE
                                    WHEN (ap.nearest_city > 0 && ap.nearest_city IN (' . $implodedTourCities . ')) THEN p.id
                                    WHEN (pp.nearest_city > 0 && pp.nearest_city IN (' . $implodedTourCities . ')) THEN p.id
                                    WHEN (tp.nearest_city > 0 && tp.nearest_city IN (' . $implodedTourCities . ')) THEN p.id
                                    ELSE 0
                                END)) AS nearest_city
                                FROM rezb2b_essential.product AS p
                                LEFT JOIN rezb2b_activity.activity_product AS ap ON ap.product_id = p.id
                                LEFT JOIN rezb2b_product.tour_product AS pp ON pp.product_id = p.id
                                LEFT JOIN rezb2b_transportation.transportation_product AS tp ON tp.product_id = p.id
                                GROUP BY p.id HAVING nearest_city > 0';
                
                $mergeCity = \DB::connection('essential')->select($mergeCitySql);
                $mergeCity = array_pluck($mergeCity, 'nearest_city');
                
                if (!empty($mergeCity)) {
                    $conditionToApply = " AND p.id IN (" . implode(',', $mergeCity) . ")";
                    $productSqlToRun .= $conditionToApply;
                }
                
                unset($transportationProduct);
                unset($implodedTourCities);
                unset($activityProduct);
                unset($tourProduct);
                unset($mergeCity);
            }
        }
        
        $productId = request('product_id', 0);
        if (is_numeric($productId)) {
            $conditionToApply = " AND p.id = " . $productId;
            $productSqlToRun .= $conditionToApply;
        } else {
            $allProductIdAttr = explode('-', trim($productId));
            $lengths          = count($allProductIdAttr);
            
            if ($lengths == 2) {
                if (is_numeric($allProductIdAttr[1]) && is_numeric($allProductIdAttr[0])) {
                    $conditionToApply = " AND p.id = " . $allProductIdAttr[1] . " AND p.provider_id = " . $allProductIdAttr[0];
                    $productSqlToRun .= $conditionToApply;
                }
                
                if (is_numeric($allProductIdAttr[0]) && $allProductIdAttr[1] == '') {
                    $conditionToApply = " AND p.provider_id = " . $allProductIdAttr[0];
                    $productSqlToRun .= $conditionToApply;
                }
            }
        }

        $region = request('region','');
        if ($region) {
         
            $conditionToApply =  "AND ( tp.product_region = '".$region."'
				              		OR ap.product_region = '".$region."'
					                OR trp.product_region = '".$region."'
					                OR ttd.product_region = '".$region."' ) ";
		    $productSqlToRun .= $conditionToApply;
        }

        $duration = request('duration', null);
        if (!empty($duration)) {
        	
        	$duration_type = request('duration_type',"day");

        	if (is_numeric($duration)) {
        		$conditionToApply = " AND p.duration = " . $duration;
        		$productSqlToRun .= $conditionToApply;
        	}else{
        		$durationAttr = explode('-', trim($duration));
        		if(count($durationAttr)  == 2){
        			if (is_numeric($durationAttr[0]) && is_numeric($durationAttr[1])) {
        				$conditionToApply = " AND p.duration = " . $durationAttr[0] . " AND p.duration_type = " . $durationAttr[1];
                    	$productSqlToRun .= $conditionToApply;
        			}
        		}
        	}

        	$conditionToApply = " AND p.duration_type = '".$duration_type."'";
        	$productSqlToRun .= $conditionToApply;
        }

        $getExpiredDate = request('expired_date', null);
		if($getExpiredDate){
			$expiredProducts = $this->getExpiredProduct($getExpiredDate);
			$conditionToApply = " AND p.id IN (" . implode(',', $expiredProducts) . ")";
        	$productSqlToRun .= $conditionToApply;
		}
        
        $typeId = request('type_id', 0);
        if (is_numeric($typeId)) {
            $conditionToApply = " AND (p.type_id = " . $typeId . " OR ttd.activity_type = " . $typeId . ")";
            $productSqlToRun .= $conditionToApply;
        }

        $allApiSource = request('is_api_source');
		if ($allApiSource != NULL) {
			$conditionToApply = " AND (p.api_source LIKE '".$allApiSource."')";
            $productSqlToRun .= $conditionToApply;
		}
        
        $isPublished = request('is_published');
        if ($isPublished != NULL) {
            $conditionToApply = " AND p.is_published = " . $isPublished;
            $productSqlToRun .= $conditionToApply;
        }

        $isPublishedEs = request('is_published_es');
        if ($isPublishedEs != NULL) {
            $conditionToApply = " AND p.is_published_es = " . $isPublishedEs;
            $productSqlToRun .= $conditionToApply;
        }

        $isPublishedCn = request('is_published_cn');
        if ($isPublishedCn != NULL) {
            $conditionToApply = " AND p.is_published_cn = " . $isPublishedCn;
            $productSqlToRun .= $conditionToApply;
        }
        
        $operators = explode(',', request('operators'));
        if ($operators[0] != NULL) {
            $conditionToApply = " AND p.provider_id IN (" . implode(',', $operators) . ")";
            $productSqlToRun .= $conditionToApply;
        }
        
        $providerProductCode = trim(request('provider_product_code'));
        if (!empty($providerProductCode)) {
            $conditionToApply = " AND (p.provider_product_code LIKE '%".$providerProductCode."%')";
            $productSqlToRun .= $conditionToApply;
        }
        
        $allProducts = explode(',', request('products'));
        if ($allProducts[0] != NULl) {
            $conditionToApply = " AND p.id IN (" . implode(',', $allProducts) . ")";
            $productSqlToRun .= $conditionToApply;
        }

        $productEntityType = request('product_entity_type');
		if ($productEntityType != NULL && array_key_exists($productEntityType, Product::getProductLine())) {
			
			if(strpos($productEntityType, Product::PRODUCT_LINE_TTD) !== false) {

				$productLine = strtolower(explode('_', $productEntityType)[0]);
				$subject = $productEntityType;
				$search = strtolower(Product::PRODUCT_LINE_TTD.'_');
				$productSublineLine = str_replace($search, '', $subject);

				$conditionToApply = " AND p.product_line = '" . $productLine . "' AND p.product_sub_line = '". $productSublineLine. "'";
            	$productSqlToRun .= $conditionToApply;
				
			} else if(strpos($productEntityType, Product::PRODUCT_LINE_CRUISE) !== false) {
				$conditionToApply = " AND p.is_cruise = 1";
            	$productSqlToRun .= $conditionToApply;
			} else {

				$productLine = strval(strtolower($productEntityType));
				$conditionToApply = " AND p.product_line = '" .  $productLine."'";
            	$productSqlToRun .= $conditionToApply;
			}
		}

        $conditionToApply = " GROUP BY p.id";
        $productSqlToRun .= $conditionToApply;

        $productNameSearch = trim(request('product_name'));
        if (!empty($productNameSearch)) {
            $conditionToApply = " having (product_en_name like '%".$productNameSearch."%' or product_es_name like '%".$productNameSearch."%' or product_cn_name like '%".$productNameSearch."%')";
            $productSqlToRun .= $conditionToApply;
        }
    
        $products   = \DB::connection('essential')->select($productSqlToRun);
        //$getProductNamesByLang    	= array_pluck($products, 'product_line', 'id');
        $rowsIDs    = array_pluck($products, 'id', 'id');
        $chunkedIds = array_chunk($rowsIDs, 1000);
        unset($rowsIDs);
        
        // get first all current product langauge
        $currentLanguage  = \DB::connection("provider")->select('select provider_language_id, name from provider_language order by provider_language_id');
        $currentLanguage  = array_pluck($currentLanguage, 'name', 'provider_language_id');
        $productLanguages = $productAttractionCities = $productVisitingCities = [];
        
        ///////////////////////////////////////////////////////////////////////////////////////////////////
        // get all provider language with all the visiting cities as well as visiting attraction - start //
        ///////////////////////////////////////////////////////////////////////////////////////////////////
        
        if (!empty($chunkedIds)) {
            foreach ($chunkedIds as $ids) {
                $language = "select p.id,
                            GROUP_CONCAT(DISTINCT (CASE
                                WHEN rapl.operation_language_id > 0 THEN rapl.operation_language_id
                                WHEN rppl.operation_language_id > 0 THEN rppl.operation_language_id
                                WHEN rtpl.operation_language_id > 0 THEN rtpl.operation_language_id
                                WHEN p.product_line = '".Constants::PRODUCT_LINE_TTD."' THEN ttdpl.operation_language
                                ELSE 0
                            END)) AS operation_language_id,
                            GROUP_CONCAT(DISTINCT (CASE
                                WHEN rppc.parent_id > 0 THEN rppc.parent_id
                                WHEN rapc.city_id > 0 THEN rapc.city_id
                                WHEN rtpc.city_id > 0 THEN rtpc.city_id
                                WHEN (ttdvc.itinerary_item_id > 0 AND ttdvc.type = 1) THEN ttdvc.itinerary_item_id
                                ELSE 0
                            END)) AS tour_city_id,
                            GROUP_CONCAT(DISTINCT (CASE
                                WHEN rppc.parent_id > 0 THEN rppc.parent_id
                                WHEN rapa.attraction_id > 0 THEN rapa.attraction_id
                                WHEN rtpa.attraction_id > 0 THEN rtpa.attraction_id
                                WHEN (ttdvc.itinerary_item_id > 0 AND ttdvc.type = 2) THEN ttdvc.itinerary_item_id
                                ELSE 0
                            END)) AS tour_attraction_id
                            from rezb2b_essential.product as p
                            left join rezb2b_activity.product_language as rapl on rapl.product_id = p.id
                            left join rezb2b_product.product_language as rppl on rppl.product_id = p.id
                            left join rezb2b_transportation.product_language as rtpl on rtpl.product_id = p.id
                            left join rezb2b_activity.product_city as rapc on rapc.product_id = p.id
                            left join rezb2b_product.product_city as rppc on rppc.product_id = p.id
                            left join rezb2b_transportation.product_city as rtpc on rtpc.product_id = p.id
                            left join rezb2b_activity.product_attraction as rapa on rapa.product_id = p.id
                            left join rezb2b_transportation.product_attraction as rtpa on rtpa.product_id = p.id
                            left join rezb2b_ttd.ttd_product as ttdpl on ttdpl.product_id = p.id
                            left join rezb2b_ttd.ttd_product_itinerary as ttdvc on ttdvc.product_id = p.id and ttdvc.type in (1, 2)
                            where p.id in (".implode(",", $ids).") GROUP BY p.id having operation_language_id > 0 OR tour_city_id > 0 OR tour_attraction_id > 0";
                
                $data = \Db::connection("essential")->select($language);
                
                if (!empty($data)) {
                    foreach ($data as $d) {
                        if (!isset($productLanguages[$d->id])) {
                            $productLanguages[$d->id] = [];
                        }
                        
                        if (!isset($productVisitingCities[$d->id])) {
                            $productVisitingCities[$d->id] = [];
                        }
                        
                        if (!isset($productAttractionCities[$d->id])) {
                            $productAttractionCities[$d->id] = [];
                        }
                        
                        $productLanguages[$d->id]        = explode(',', $d->operation_language_id);
                        $productVisitingCities[$d->id]   = explode(',', $d->tour_city_id);
                        $productAttractionCities[$d->id] = explode(',', $d->tour_attraction_id);
                        unset($d);
                    }
                }
                unset($data);
                unset($ids);
            }
        }
        
        /////////////////////////////////////////////////////////////////////////////////////////////////
        // get all provider language with all the visiting cities as well as visiting attraction - end //
        /////////////////////////////////////////////////////////////////////////////////////////////////
        
        $finalProduct = $citiesData = [];
        
        if (!empty($products)) {
        	
           // get all city names
	        $tourcities = \DB::connection("location")->select("select tc.tour_city_id, tcd.name from tour_city as tc join tour_city_description as tcd on tcd.tour_city_id = tc.tour_city_id and tcd.language_id = 1");
	        $citiesData = array_pluck($tourcities, 'name', 'tour_city_id');

	        // get product Name by Language Id
	        $productName = [];

			// $getProductName = Product::getProductNames($getProductNamesByLang);
			// foreach ($getProductName as $key => $productNames) {
			// 	$enName = isset($productNames[1]) ? $productNames[1] :'';
			// 	$esName = isset($productNames[2]) ? $productNames[2] :'';
			// 	$cnName = isset($productNames[3]) ? $productNames[3] :'';
			// 	$productName[$key] = ['en_name'=>$enName,'cn_name'=>$cnName,'es_name'=>$esName];
			// }
			
            foreach ($products as $key => $product) {
                if (!empty($product->product_line) && in_array($product->product_line, [Product::PRODUCT_LINE_ACTIVITY, Product::PRODUCT_LINE_TOUR, Product::PRODUCT_LINE_TRANSPORTATION, Product::PRODUCT_LINE_TTD])) {
                    $finalProduct[$key]['Product ID'] = $product->id;
                    
                    if (in_array('product_code', $fields)) {
                        $finalProduct[$key]['Product Code'] = " " . $product->provider_id . " - " . $product->id;
                    }
                    
                    if (in_array('provider_product_code', $fields)) {
                        $finalProduct[$key]['Operator Product Code'] = $product->provider_product_code;
                    }
                    
                    if (in_array('product_name', $fields)) {
                        $finalProduct[$key]['Product Name (CN)'] = $product->product_cn_name;
                        $finalProduct[$key]['Product Name (EN)'] = $product->product_en_name;
                        $finalProduct[$key]['Product Name (ES)'] = $product->product_es_name;
                    }
                    
                    if (in_array('provider_id', $fields)) {
                        $finalProduct[$key]['Provider ID'] = $product->provider_id;
                    }
                    
                    if (in_array('provider_name', $fields)) {
                        $finalProduct[$key]['Provider Name'] = $product->provider_name;
                    }
                    
                    if (in_array('duration', $fields)) {
                        $finalProduct[$key]['Duration'] = $product->duration . " " . (!empty($product->duration_type) ? $product->duration_type : 'day');
                    }
                    
                    if (in_array('display_price', $fields)) {
                        $finalProduct[$key]['Display Price'] = $product->advertised_price;
                    }
                    
                    if (in_array('status_en', $fields)) {
                        $finalProduct[$key]['Status En'] = ($product->is_published == 1) ? 'Active' : 'Inactive';
                    }

                    if (in_array('status_es', $fields)) {
                        $finalProduct[$key]['Status Es'] = ($product->is_published_es == 1) ? 'Active' : 'Inactive';
                    }

                    if (in_array('status_cn', $fields)) {
                        $finalProduct[$key]['Status Cn'] = ($product->is_published_cn == 1) ? 'Active' : 'Inactive';
                    }

                    if (in_array('stock_status_en', $fields)) {
                        $finalProduct[$key]['Stock Status En'] = ($product->is_soldout == 0) ? 'On' : 'Off';
                    }

                    if (in_array('stock_status_es', $fields)) {
                        $finalProduct[$key]['Stock Status Es'] = ($product->is_soldout_es == 0) ? 'On' : 'Off';
                    }

                    if (in_array('stock_status_cn', $fields)) {
                        $finalProduct[$key]['Stock Status Cn'] = ($product->is_soldout_cn == 0) ? 'On' : 'Off';
                    }
                    
                    if (in_array('created_date', $fields)) {
                        $finalProduct[$key]['Product Created Date'] = date('Y-m-d', $product->created_at);
                    }
                    
                    if (in_array('provider_language', $fields)) {
                        $string       = '';
                        $hasLangnauge = isset($productLanguages[$product->id]) ? $productLanguages[$product->id] : [];
                        
                        if (!empty($hasLangnauge)) {
                            foreach ($hasLangnauge as $lang){
                                $string .= isset($currentLanguage[$lang]) ?  $currentLanguage[$lang]. ", " : "";
                            }
                        }
                        
                        $finalProduct[$key]['Provider Language'] = rtrim(trim($string), ',');
                        
                        // remove not need variables
                        unset($hasLangnauge);
                        unset($string);
                    }
                    
                    // start departure cities
                    if (in_array('departure_cities', $fields)) {
                        $finalProduct[$key]['Departure Cities'] = '';
                        
                        if (!empty($product->departure_city)) {
                            $departureCityId   = explode(',', $product->departure_city);
                            $departureCityName = "";
                            
                            if (!empty($departureCityId)) {
                                foreach ($departureCityId as $id) {
                                    $departureCityName .= !empty($citiesData[$id]) ? $citiesData[$id].',' : '';
                                }
                            }
                            
                            $finalProduct[$key]['Departure Cities'] = rtrim(trim($departureCityName), ',');
                        }
                    }
                    
                    if (in_array('return_cities', $fields)) {
                        $finalProduct[$key]['Return Cities'] = '';
                        
                        if (!empty($product->return_city)) {
                            $departureCityId   = explode(',',$product->return_city);
                            $departureCityName = "";
                            
                            if (!empty($departureCityId)) {
                                foreach ($departureCityId as $id) {
                                    $departureCityName .= !empty($citiesData[$id]) ? $citiesData[$id].',' : '';
                                }
                            }
                            
                            $finalProduct[$key]['Return Cities'] = rtrim(trim($departureCityName), ',');
                        }
                    }
                    
                    if (in_array('visiting_cities', $fields)) {
                        $finalProduct[$key]['Visiting Cities'] = $string = '';
                        $hasVistingCities                      = isset($productVisitingCities[$product->id]) ? $productVisitingCities[$product->id] : [];
                        
                        if(!empty($hasVistingCities)) {
                            foreach(array_unique($hasVistingCities) as $cities){
                                $string .= isset($citiesData[$cities]) ?  $citiesData[$cities]. ", " : "";
                            }
                        }
                        
                        $finalProduct[$key]['Visiting Cities'] = rtrim(trim($string),',');
                        // remove not need variables
                        unset($hasVistingCities);
                        unset($string);
                    }
                    
                    if (in_array('visiting_attractions', $fields)) {
                        $finalProduct[$key]['Visiting Attractions'] = $string = '';
                        $hasVistingAttr                             = isset($productAttractionCities[$product->id]) ? $productAttractionCities[$product->id] : [];
                        
                        if (!empty($hasVistingAttr)) {
                            foreach (array_unique($hasVistingAttr) as $cities) {
                                $string .= isset($citiesData[$cities]) ? $citiesData[$cities]. ", " : "";
                            }
                        }
                        
                        $finalProduct[$key]['Visiting Attractions'] = rtrim(trim($string),',');
                        // remove not need variables
                        unset($hasVistingCities);
                        unset($string);
                    }
                    
                    if (in_array('type', $fields)) {
                        if (!empty($product->product_line) && empty($product->product_sub_line)) {
                            $finalProduct[$key]['Type'] = !empty($displayProductLine[$product->product_line]) ? $displayProductLine[$product->product_line] : '';
                        } elseif (!empty($product->product_line) && !empty($product->product_sub_line)) {
                            $line = strtoupper($product->product_line).'_';
                            $line .= !empty($displayProductLine[$product->product_sub_line]) ? $displayProductLine[$product->product_sub_line] : $product->product_sub_line;
                            $finalProduct[$key]['Type'] = $line;
                        } else {
                            $finalProduct[$key]['Type'] = "";
                        }
                    }
                    
                    if (in_array('api_source', $fields)) {
                        $finalProduct[$key]['API Source'] = $product->api_source;
                    }
                }
                unset($product);
            }
        }
        unset($products);
        
        $fileName = "all_products_".date('YmdHis');
        
        Excel::create($fileName, function($excel) use ($finalProduct) {
            $excel->sheet('Products', function($sheet) use ($finalProduct) {
                $sheet->fromArray($finalProduct);
            });
        })->download('xlsx');
    }

	public function changeProductStatus(RezApi $rezApi)
    {
		$id = request('id', null);
		$extraData = request('extraData');
		$storeStatus = ($extraData['storeStatus']) ? $extraData['storeStatus'] : 'en';
		
		if ((int)$id > 0) {

	    	$product = Product::findOrFail($id);

            if ($product) {
				$userId = \Auth::user()->user_id;
                // If Region field or Product URL avalable then update first in db
                $res = $this->updateRegionAndProductUrl(request()->all(), $product);
                
                if (!empty($res)) {
                    return $this->errorJson([],$res);
                }
                
                $currentPublished   = $product->is_published;
                $currentDisabled    = $product->is_disabled;
                // $oldStatusForLog    = (($currentPublished == 0) && ($currentDisabled == 0)) ? ProductMaster::STATUS_REVIEWING : ((($currentPublished == 0) && ($currentDisabled == 1)) ? ProductMaster::STATUS_DEACTIVATE : ProductMaster::STATUS_LIVE);
                $oldStatusForLog    = ProductMaster::STATUS_LIVE;
                if($storeStatus == 'cn'){
                	$product->is_published_cn = ($product->is_published_cn == 1) ? 0 : 1;
                	$productStatus = $product->is_published_cn;
                }else if ($storeStatus == 'es') {
                	$product->is_published_es = ($product->is_published_es == 1) ? 0 : 1;
                	$productStatus = $product->is_published_es;
                }else if($storeStatus == 'en'){
                	$product->is_published = ($product->is_published == 1) ? 0 : 1;
                	$productStatus = $product->is_published;
                }
                
                if (isset($productStatus) && $productStatus == 1) {
                    if (empty($product->product->product_region)) {
                        return $this->errorJson([],'Save Product Region field first, then only product can be activated...!!!');
                    } else if($product->product_line != Constants::PRODUCT_LINE_TTD && empty($product->product_seo_url)){
                        return $this->errorJson([],'Save Product URL field first, then only product can be activated...!!!');
                    }
                }

                //update api_code for ttd product
                if($product->product_line == Constants::PRODUCT_LINE_TTD && empty(trim($product->api_source))){
                    $apiCode = "0@".$product->id;
                    $product->api_code = $apiCode;
                }
                if ($product->save()) {
                    if ($product->is_published == 1 || $product->is_published_cn == 1 || $product->is_published_es == 1) {
                        $product->is_disabled = 0;
                        $product->save();
                    }
                    
                    $languageId = ($storeStatus == 'cn') ? 3 : (($storeStatus == 'es') ? 2 : 1);
                    
                    // update status in old product
                    if ($product->old_product_id > 0 && $product->product_line != Constants::PRODUCT_LINE_TTD) {
                        \App\Models\Old\Product::changeOldProductStatus($product->old_product_id, $product->is_published);
                    }

					$product->product->is_published	= $product->is_published;
					$product->product->is_published_es	= $product->is_published_es;
					$product->product->is_published_cn	= $product->is_published_cn;
					$product->product->is_soldout = 0;
					$product->product->save();
					
					if (!empty($product->product_line)) {
						$operationLog = $product->getOperationLogObject($product->product_line);
						$operationLog->add($id, $userId, 'Status ' . ucfirst($storeStatus) . ': ' . ($productStatus ? 'Active' : 'Inactive') . '. Reason: ' . request('reason'), 1, "All", $languageId);
					}

					$productMaster = ProductMaster::where('product_id',$id)->first();
					// $statusStr     = ProductMaster::STATUS_REVIEWING_STR;
					// $status        = ProductMaster::STATUS_REVIEWING;
					
					// if ($product->is_published == 1) {
					// 	$statusStr = ProductMaster::STATUS_LIVE_STR;
					// 	$status    = ProductMaster::STATUS_LIVE;
					// }

					if(!empty($productMaster)){
                        /*
                        // Store operator side Product Status History Log
                        ProductStatusHistoryLog::add($productMaster->product_master_id, $userId, $statusStr, $languageId, $productMaster->status);
                        $oldStatusForLog = $productMaster->status;
                        // Store Product Status History Log End

                        $productMaster->update(['is_live' => $product->is_published, 'status' => $status]);
                        
                        if ($product->is_published == 1) {
                        	$operatorStatus = 'Status: Active. Reason: ' . request('reason');
                        	\App\Models\ProviderProductRequest\ProductUpdateHistory::changeProductStatusHistory($productMaster->product_master_id,'4','admin');
                        }
                        */
                        $operatorStatus = 'Status ' . ucfirst($storeStatus) . ': Inactive. Reason: ' . request('reason');
                        if ($storeStatus == 'cn') {
                            $oldStatusForLog = $productMaster->status_cn;
                            if ($product->is_published_cn == 1) {
                                $operatorStatus = 'Status Cn: Active. Reason: ' . request('reason');
                            }
                        } else if ($storeStatus == 'es') {
                            $oldStatusForLog = $productMaster->status_es;
                            if ($product->is_published_es == 1) {
                                $operatorStatus = 'Status Es: Active. Reason: ' . request('reason');
                            }
                        } else if ($storeStatus == 'en'){
                            $oldStatusForLog = $productMaster->status;
                            if ($product->is_published == 1) {
                                $operatorStatus = 'Status En: Active. Reason: ' . request('reason');
                            }
                        }
                        
                        // start activation history operator side
                        \App\Models\ProviderProductRequest\ProductOperationLog::add($productMaster->product_master_id, $userId, $operatorStatus, 1, $languageId);
                        // end activation history operator side
                    }

                    //admin side log
                    // if($product->product_line != ""){
                    //     $productStatusHistoryLogObj = Product::getProductStatusHistoryLogObject($product->product_line);
                    //     $productStatusHistoryLogObj->add($id, $userId, $statusStr, $languageId, $oldStatusForLog);
                    // }

                    $rezApi->refreshProductDB((int)$id); // Update product data to Sphinx sever of REZB2B
                    
                	(new UpdateCnSphinxTaskService())->addUpdateSphinxTask([$id]); // Update cn product data to Sphinx sever of REZB2B
                    
		            return $this->successJson(['is_published' => $productStatus,'store_status'=>$storeStatus]);
		        }
	    	}
			return $this->errorJson([],'Product Not Found!!');
		}
		return $this->errorJson([],'Somthing wrong!');
    }
	
	/**
     * Check T4F and Rezb2b Default Price
     * @author  Dalsaniya Pintu <pintud.bipl@gmail.com>
     */
	 
	public function checkSyncToT4fProduct(Request $request)
	{
		if ((int)$request->productId > 0) {
			$product = Product::findOrFail($request->productId);
			
			if ($product) {
				$isDiffrentPrice = $product->checkT4fAndRezPrice($request->productId);
				return $this->successJson([
					'product_id'      => $product->id,
					'old_product_id'  => $product->old_product_id,
					'saveUrl'         => route('sync_to_t4f_save_default_price'),
					'url'             => route('sync_to_t4f', $product->id),
					'isDiffrentPrice' => $isDiffrentPrice
				]);
			}
			return $this->errorJson([],'Product Not Found!!');
		}
		return $this->errorJson([],'Somthing wrong!');
	}
	
	public function syncToT4f($productId = 0, RezApi $rezApi)
	{
		if ((int)$productId > 0) {
			$isDiffrentPrice = 1;
			$syncProduct = $rezApi->syncToT4f($productId, $isDiffrentPrice);
			if (isset($syncProduct->code) && ($syncProduct->code == 200)) {
				return $this->successJson([], isset($syncProduct->message) ? $syncProduct->message : 'Sync Product to T4F Successfully...!!!');
			} else {
				return $this->errorJson([], isset($syncProduct->message) ? $syncProduct->message : 'Something went wrong...!!!');
			}
		}
	}
	
	/**
     * sync product and save Default Price
     * @author  Dalsaniya Pintu <pintud.bipl@gmail.com>
     */
	 
	public function syncToT4fDefaultPrice(Request $request, RezApi $rezApi)
	{
		if ((int)$request->product_id > 0) {
			if(!empty($request->is_default_price) && $request->is_default_price == 1){
				$syncProduct = $rezApi->syncToT4f($request->product_id,$request->is_default_price);
			}else{
				$isDiffrentPrice = 0;
				$syncProduct = $rezApi->syncToT4f($request->product_id,$isDiffrentPrice);
			}
			
			if (isset($syncProduct->code) && ($syncProduct->code == 200)) {
				return $this->successJson(["data"=>isset($syncProduct->data) ? $syncProduct->data : '1'], isset($syncProduct->message) ? $syncProduct->message : 'Sync Product to T4F Successfully...!!!');
			} else {
				return $this->errorJson([], isset($syncProduct->message) ? $syncProduct->message : 'Something went wrong...!!!');
			}
		}
	}
	
	public function getSearchProduct($form)
	{
		if(isset($form['products'])){
			return Product::fetchProductList($form['products']);
		}
        return [];
	}
	
	public function getSearchOperator($form)
	{
		if(isset($form['operators'])){
			return Product::fetchOperatorList($form['operators']);
		}
        return [];
	}
    
    /**
     * This function will update Region ProductUrl if avalable
     * @param  [object] [requestParams] [laravel request object]
     * @return [type] [<description>]
     * @author Sagar Ankoliya <sagara.bipl@gmail.com> | 09 February 2019 (Saturday)
     */
    public function updateRegionAndProductUrl($requestParams, $essentialProduct)
    {
    	
        $extraData = isset($requestParams['extraData']) ? $requestParams['extraData'] : "";

        if (!empty($extraData) && $essentialProduct->product_line != Constants::PRODUCT_LINE_TTD) {
            $valid = \Validator::make($extraData, ['product_seo_url' => 'regex:/^[a-zA-Z0-9_\-]+$/i'], ["product_seo_url.regex" => "Please enter SEO friendly URL. Allowed characters are A-Za-z0-9-_"]);

            if (!empty($valid->fails())) {
                return $valid->errors()->first();
            }
            
            if (!empty($extraData['product_seo_url'])) {
                $productSeoUrlExists = \App\Models\Product::where('product_seo_url', '=', $extraData['product_seo_url'])->where('id', '!=', $essentialProduct->id)->count();
                
                if (!empty($productSeoUrlExists)) {
                    return "Seo url ".$extraData['product_seo_url']." already exists with another product!";
                }
            }
            
            $essentialProduct->product_seo_url = $extraData['product_seo_url'];
            $essentialProduct->save();
            
            $product = $essentialProduct->product;
            $product->product_region = $extraData['product_region'];
            $product->save();
            
            return "";
        }
        
        return "";
    }
    public function copyAdminProduct(Request $request , $productId = 0 )
    {
    	$param = $request->all();

    	$productData = $this->rezApi->_sendGetRequest("/clone-product/" . (int) $productId . "", $param);

    	if ($productData->code == 200) {
    		$response = $productData->data;
            $copyProductUrl = route('product_main_controller', ['productLine' => $response->product_line, 'id' => $response->copy_product_id, 'tab' => 'basic']);
            
            if($response->product_line == 'ttd'){
                $copyProductUrl = route('product_editor_ttd_basic_get_new', ['productId' => $response->copy_product_id, 'tab' => 'basic']);
            }
                
            return response()->json(["code" => 200, "error" => "success", "message" => $response->copy_product_id." Product cloned successfully","url"=>$copyProductUrl]);
        }
        return response()->json(["code" => 500, "error" => "success", "message" => 'something wrong']);
    }
}
