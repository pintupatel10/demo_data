<?php
namespace App\Http\Middleware;

use App\Libraries\Permission;
use App\Models\General\ApiKey;
use Closure;
use Config;
use Illuminate\Support\Facades\Auth;
use Route;
use Session;
use App\Models\Provider;
use App\Models\Location\CountryDescription;
use App\Models\Agent\AgentSetting;

class SiteUrlRule
{
    public function __construct()
    {
        $provider_id = 0;
        Session::put('rez_previous_url', "");
        //Session::put("current_provider_id", $provider_id);
        if (!defined('INT_CONFIG')) {
            /*$configuration = \App\Models\General\Configuration::select(['key','value'])->get();
            foreach ($configuration as $configurationValue) {
                if (!defined($configurationValue['key'])) {
                    define($configurationValue['key'], $configurationValue['value']);
                }
            }*/
            $configuration = \App\Models\General\Configuration::getAllConfiguration();
            foreach ($configuration as $key => $value) {
                if (!defined($key)) {
                    define($key, $value);
                }
            }
            define('INT_CONFIG', true);
        }

        $guard = "web";

        if (IS_AGENT_SITE == true) {
            $guard = "agent";
        }
        
        // the back door of operator for fe dev
        if (isset($_SERVER['HTTP_REFERER'])) {
            $url = parse_url($_SERVER['HTTP_REFERER']);
            if (isset($url['query']) && strpos($url['query'], 'devs=toursforfun') !== false) {
                // if (IS_OPERATOR_SITE == true && env('APP_ENV') == 'local') {
                if (IS_OPERATOR_SITE == true) {
                    Auth::guard($guard)->loginUsingId(100000);
                }
            }
        }

        if (Auth::guard($guard)->check()) {
            self::updateTimeZoneConfig();
            if (IS_OPERATOR_SITE == true) {

                if (!Auth::guard($guard)->user()->userProvider->isEmpty()) {

                    $provider_id = (int) Auth::guard($guard)->user()->userProvider[0]->provider_id;
                    Session::put("current_provider_id", $provider_id);

                } else {

                    //todo this issue due to config cookie
                    Auth::guard($guard)->logout();
                    request()->session()->flush();
                    request()->session()->regenerate();
                    return redirect('/login');
                }
            }
            if (IS_AGENT_SITE == false) {
                $userId = Auth::guard($guard)->user()->user_id;
                Auth::user()->setAttribute('isProvider', Session()->get("isProvider{$userId}", false));
                if (!empty(Auth::guard($guard)->user()->userGroup->first())) {
                    $userGroupName = Auth::guard($guard)->user()->userGroup->first()->group->name;
                    Auth::guard($guard)->user()->setAttribute('userGroupName', $userGroupName);
                }
            }

        } else {

            Session::put('rez_previous_url', url()->current());

            return redirect('/login');

        }
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //if not logged in than redirect back to login page
        Session::put('rez_previous_url', "");

        $guard = "web";

        if (IS_AGENT_SITE == true) {

            $guard = "agent";

        }

        if (!Auth::guard($guard)->check()) {
            Session::put('rez_previous_url', url()->current());
            return redirect('/login');
        }

        $agentId = 0;
		
		//Check Multi - Currency config variable
        putenv('ENABLE_MULTI_CURRENCY_CHECKOUT=true');

        needMulticurrencyTrue();
        $sandboxApiKeyStr = str_shuffle(substr(md5(time()), 0, 25));
        $agentUserId     = 0;
        if (is_admin_site()) {
            $userId = Auth::guard($guard)->user()->user_id;
            $apiKey = ApiKey::where('user_id', $userId)->where('api_user', 'admin')->first();
            if(!$apiKey) {
               $apiKeyStr = substr(md5(time()), 0, 25);
               $apiKey = ApiKey::create(['api_user' => 'admin', 'api_key' => $apiKeyStr, 'sandbox_api_key' => $sandboxApiKeyStr, 'user_id' => $userId]);
            }
        } else if (is_agent_site()) {
            $agentId = (int) Auth::guard($guard)->user()->agent_id;
            $agentUserId = (int) Auth::guard($guard)->user()->agent_user_id;
            $apiKey  = ApiKey::where('agent_id', $agentId)->where('api_user', 'agent')->first();
            if(!$apiKey) {
               $apiKeyStr = substr(md5(time()), 0, 25);
               $apiKey = ApiKey::create(['api_user' => 'agent', 'api_key' => $apiKeyStr, 'sandbox_api_key' => $sandboxApiKeyStr, 'agent_id' => $agentId]);
            }
        } else if (is_provider_site()) {
            $userId     = Auth::guard($guard)->user()->user_id;
            $providerId = (int) Auth::guard($guard)->user()->userProvider[0]->provider_id;
            $apiKey     = ApiKey::where('user_id', $userId)->where('provider_id', $providerId)->where('api_user', 'provider')->first();
            $providerData = Provider::where('provider_id',$providerId)->first();
            $actionName = Route::getCurrentRoute()->getActionName();
            $action = $this->fixActionName($actionName);
            if(!$apiKey) {
               $apiKeyStr = substr(md5(time()), 0, 25);
               $apiKey = ApiKey::create(['api_user' => 'provider', 'api_key' => $apiKeyStr, 'sandbox_api_key' => $sandboxApiKeyStr, 'user_id' => $userId, 'provider_id' => $providerId]);
            }

            if (!empty($providerData) && $providerData->old_provider_id == 0 && $providerData->is_agree == 0 && !in_array($action,array('dashboardcontroller.operatoragree'))) {
                
                return redirect(route('operator_agreement'));
                
            }
            // $apiKey = ApiKey::where(['user_id' => $userId, 'provider_id' => $providerId, 'api_user' => 'provider'])->first();
        }

        if ($apiKey) {
            $configApiKey = (env("APP_ENV") == 'prod') ? $apiKey->api_key : $apiKey->sandbox_api_key;
            Config::set('apiKey', $configApiKey);
            Config::set('currentStaffId', $agentUserId);
        }
        if (IS_AGENT_SITE == false) {
            
            $prefix = $request->segments();
            if (!empty($prefix[0]) && in_array($prefix[0], ['products', 'product-management'])) {
                $request->request->add(['cookie_language' => getCurrentLanguage()]);
                $_REQUEST['cookie_language'] = getCurrentLanguage();
            }

            if (is_provider_site()) {
                $actionName = Route::getCurrentRoute()->getPath();

                if (strpos($actionName, "my-account/users") !== false) {

                    $userProvider = Auth::guard($guard)->user()->userProvider->first();

                    if ($userProvider) {

                        $isMasterAccount = \App\Models\UserProvider::where(['is_master_account' => 1, 'user_id' => Auth::guard($guard)->user()->user_id, 'provider_id' => (int) $userProvider->provider_id])->count();

                        $permission = new Permission;

                        if ($isMasterAccount > 0 || $permission->checkOperatorPermissionUser("operator_modify_permission")) {
                            return $next($request);
                        }

                    }
                    abort(404, 'Unauthorized action ' . $actionName . ', please ask permission from administration');
                }
                return $next($request);
            } else {

                $actionName = $request->segment(1);
                $permission = new Permission;
                
                $method = $request->isMethod('get');
                $uriPath = $request->path();
                $uriParts = explode('/', $uriPath);
                $requestUser = end($uriParts);
                if (defined('REZB2B_AGENT_GROUP_ID')) {
                    $agentGroupId = REZB2B_AGENT_GROUP_ID;
                    if (empty($agentGroupId)) {
                        $agentGroupId = $permission->agentGroupId;
                    }
                } else {
                    $agentGroupId = \App\Models\General\Configuration::select(['value'])->where('key','REZB2B_AGENT_GROUP_ID')->first();
                    $agentGroupId = !empty($agentGroupId) ? $agentGroupId->value : $permission->agentGroupId;
                }
                if (defined('REZB2B_OPERATOR_GROUP_ID')) {
                    $operatorGroupId = REZB2B_OPERATOR_GROUP_ID;
                    if (empty($operatorGroupId)) {
                        $operatorGroupId = $permission->operatorGroupId;
                    }
                } else {
                    $operatorGroupId = \App\Models\General\Configuration::select(['value'])->where('key','REZB2B_OPERATOR_GROUP_ID')->first();
                    $operatorGroupId = !empty($operatorGroupId) ? $operatorGroupId->value : $permission->operatorGroupId;
                }
                
                $groups = Auth::user()->UserGroup->pluck('group_id')->toArray();

                if( $permission->checkPermission($actionName,$userId) || request()->session()->get('current_provider_id') > 0 || $actionName == "homecontroller.dashboard"){
                    return $next($request);
                }else if(in_array($agentGroupId, $groups) && $actionName == 'product-management' && $permission->checkPermission('PRODUCT_VIEW',$userId) && ($method || ($request->isMethod('post') && $requestUser == 'get-default'))){
                    return $next($request);
                }else if(in_array($agentGroupId, $groups) && $actionName == 'operator-management' && $permission->checkPermission('OPERATOR_VIEW',$userId) && $method){
                    return $next($request);
                }else if(in_array($operatorGroupId, $groups) && $actionName == 'agent-management' && $permission->checkPermission('AGENT_VIEW',$userId) && $method){
                    return $next($request);
                }else if(in_array($operatorGroupId, $groups) && $actionName == 'order-management' && $permission->checkPermission('ORDER_VIEW',$userId) && $method){
                    return $next($request);
                }else if($actionName == 'admin-system-setting' && ($requestUser == $userId || $requestUser == 'save-user')){
                    return $next($request);
                }else{
                    if($request->ajax()){
                        return response()->json(['code' => -1, 'msg' => "you don't have permission to make changes", 'data' => array("you don't have permission to make changes")]);
                    }else{
                        abort(404, "you don't have permission to make changes");
                    }
                }
            }

        } else {

            $token = md5($agentId.session()->getId());
            $redisArray = array();
            $agent = Auth::guard($guard)->user();
            if (!empty($agent)) {
                \App\Models\Agent\Agent::storeAgentDataToRedis($agent,$token);
            }
            return $next($request);

        }

    }

    /*
     * get current action name with strtolower
     * @author Pravin S <iipl.pravins@gmail.com>
     * action name from the route
     */
    public function fixActionName($action)
    {
        if ($action && strpos($action, "\Http\Controllers") !== false) {
            list($pre, $action)        = explode("\Http\Controllers\\", $action);
            $action                    = str_replace("\\", ".", $action);
            list($controller, $method) = explode('@', $action);
            $controller                = preg_replace('/.*\\\/', '', $controller);
            $action                    = $controller . "." . $method;
            return strtolower($action);
        }
    }
    public static function updateTimeZoneConfig()
    {

        if (is_admin_site()) {
            if (Auth::user()->timezone && Auth::user()->timezone != "") {
                Config::set('app.rezTimezone', Auth::user()->timezone);
            }
        } else if (is_agent_site()) {
            if (Auth::guard('agent')->user()->timezone && Auth::guard('agent')->user()->timezone != "") {
                Config::set('app.rezTimezone', Auth::guard('agent')->user()->timezone);
            }
        } else if (is_provider_site()) {
            if (session::get('current_provider_id')) {
                $provider = \App\Models\Provider\Provider::where('provider_id', session::get('current_provider_id'))->first();
                if (!empty($provider) && $provider->timezone != "") {
                    Config::set('app.rezTimezone', $provider->timezone);
                }
            }
        }

    }
}
