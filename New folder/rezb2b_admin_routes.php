<?php

/*
ALL ROUTES 2018 REZB2B ADMIN
DATE : 26-01-2018
 */

// Route::group(['middleware' => 'url.restriction'], function () {

Route::get('/product-management/new-product-request-listing', ['as' => 'product_management_list_new_product_request', 'uses' => 'Modules\ProviderProductRequest\Http\Controllers\BasicController@getAllRequestV1']);

Route::group(['namespace' => 'Modules\Auth\Http\Controllers\Auth'], function () {
    Route::post('/admin/resetPassword', 'ForgotPasswordController@adminResetPassword');
});

Route::group(['middleware' => 'auth'], function () {

    Route::group(['middleware' => ['site.url']], function () {

        Route::group(['namespace' => 'App\Http\Controllers'], function () {

            Route::get('/sphinx-query-builder', ['as' => 'sphinx_query_bulder', 'uses' => 'SphinxQueryController@display']);
            Route::post('/sphinx-query-builder', ['as' => 'sphinx_query_execute', 'uses' => 'SphinxQueryController@executeQuery']);
            Route::get('/system-information', ['as' => 'system_information', 'uses' => 'SystemInformationController@display']);

            // this is all temp task
            Route::group(['prefix' => '/temp-task'], function () {
                Route::get('/update-agent-in-hotel', ['as' => 'temp_task_update_hotel_agent', 'uses' => 'TempTaskController@updateAgentInHotel']);
                Route::get('/update-agent-in-flight', ['as' => 'temp_task_update_hotel_agent', 'uses' => 'TempTaskController@updateAgentInFlight']);
                Route::get('/update-agent-in-new-question/{take}', ['as' => 'temp_task_update_question_agent', 'uses' => 'TempTaskController@updateAgentInQuestion']);
                Route::get('/update-provider-language', ['as' => 'temp_task_update_provider_language', 'uses' => 'TempTaskController@updateProviderLanguage']);
                Route::get('/update-eticket-template', ['as' => 'temp_task_update_eticet_template', 'uses' => 'TempTaskController@updateProviderEticketTemplate']);
                Route::get('/update-api-code-in-t4f', ['as' => 'temp_task_update_api_code_in_t4f', 'uses' => 'TempTaskController@updateApicodeT4f']);
                Route::get('/update-soldout-remaining-in-newt4f', ['as' => 'temp_task_update_soldout_remaining_in_newt4f', 'uses' => 'TempTaskController@updateSoldoutRemainingToNewT4f']);
                Route::get('/insert-admin-ratetype-description', ['as' => 'temp_task_insert_admin_ratetype_description', 'uses' => 'TempTaskController@insertAdminRateTypeDescription']);
                Route::get('/insert-provider-ratetype-description', ['as' => 'temp_task_insert_provider_ratetype_description', 'uses' => 'TempTaskController@insertProviderRateTypeDescription']);
                Route::get('/delete-language-data', ['as' => 'temp_task_delete_language_data', 'uses' => 'TempTaskController@deleteLanguageData']);
                Route::get('/update-location', ['as' => 'temp_task_update_location', 'uses' => 'TempTaskController@insertLocation']);
                Route::get('/update-provider-code', ['as' => 'temp_task_update_provider_code', 'uses' => 'TempTaskController@updateProviderCode']);
                Route::get('/export-active-product/{product_id?}', ['as' => 'temp_task_export_active_product', 'uses' => 'TempTaskController@exportActiveProduct']);

                Route::get('/set-soldout-on-product-expired/{id?}', function ($id = 0){
                    if ( env('APP_ENV') != 'prod' ){
                        \Artisan::call('availability:Update', ['--product_id' => $id]);
                        print_r(Artisan::output());
                    }else{
                        echo 'Not Allowed For Live'; exit;
                    }
                    
                });

                Route::get('/update-pending-status', function ($id = 0){
                    if ( env('APP_ENV') != 'prod' ){
                        \Artisan::call('updatePendingStatus:update');
                        print_r(Artisan::output());
                    }else{
                        echo 'Not Allowed For Live'; exit;
                    }
                    
                });

                Route::get('/remove-product-request', function ($id = 0){
                    if ( env('APP_ENV') != 'prod' ){
                        \Artisan::call('removeproductrequest');
                        print_r(Artisan::output());
                    }else{
                        echo 'Not Allowed For Live'; exit;
                    }
                    
                });

                // PMS Task [#10131]
                Route::get('/update-old-rezdy-order/{order_id}', ['as' => 'temp_task_update_old_rezdy_order', 'uses' => 'TempTaskController@updateOldRezdyOrder']);

                //update order_product's extra_values
                Route::get('/update-extra-values', ['as' => 'temp_task_update_extra_values', 'uses' => 'TempTaskController@updateExtraValues']);

                // Update agent api key
                Route::get('/update-agent-apikey', ['as' => 'temp_task_update_agent_apikey', 'uses' => 'TempTaskController@updateAgentApiKey']);

                Route::get('/update-live-order-guest-info', ['as' => 'temp_task_update_live_order_guest_info', 'uses' => 'TempTaskController@updateLiveOrderGuestInfo']);
                Route::get('/update-review', ['as' => 'temp_task_review_update', 'uses' => 'TempTaskController@updateReview']);
                //update old api product rate price
                Route::get('/update-product-rate-price', ['as' => 'temp_task_product_rate_price', 'uses' => 'TempTaskController@updateProductRatePrice']);

                //migrate ttd products
                Route::get('migrate-ttd-product', 'TempTaskController@migrateTtdProducts');
                Route::get('test-viator-api/{module}/{lang}', 'TempTaskController@testViatorApi');

            });
            Route::get('/getAreaCode', ['as' => 'get_area_code', 'uses' => 'HomeController@getAreaCode']);
        });

        Route::group(['prefix' => '/product-management'], function () {

            Route::get('/product-auto-confirm', ['as' => 'product_auto_confirm_list', 'uses' => 'App\Http\Controllers\ProductSearchController@getProductAutoConfirmData']);
            Route::get('/provider-auto-confirm', ['as' => 'provider_auto_confirm_list', 'uses' => 'App\Http\Controllers\ProductSearchController@getProviderAutoConfirmData']);

            // Product Manage Routes
            Route::group(['namespace' => 'Modules\ProductEditor\Http\Controllers\Product'], function () {

                Route::get('/match.json', 'DefaultController@match');
                Route::get('/match-order.json', 'DefaultController@order');
                Route::get('agentmatch.json', 'DefaultController@agentMatch');
                Route::get('operatormatch.json', 'DefaultController@operatorMatch');
                Route::get('/searchProduct/{keyword}', ['as' => 'searchProduct', 'uses' => 'ProductController@getAllProduct']);

                //Product All Page Listing
                Route::group(['prefix' => '/product-listing'], function () {
                    Route::get('/', ['as' => 'product_management_product_listing', 'uses' => 'ProductController@allProduct']);
                    Route::group(['middleware' => 'group.permission:product-management'], function () {
                        Route::post('/check-sync-to-t4f-product', ['middleware' => 'group.permission:product-management','as' => 'check_sync_to_t4f_product', 'uses' => 'ProductController@checkSyncToT4fProduct']);
                        Route::get('/sync-to-t4f/{productId}', ['middleware' => 'group.permission:product-management','as' => 'sync_to_t4f', 'uses' => 'ProductController@syncToT4f']);
                    });
                    Route::post('/save-default-price', ['as' => 'sync_to_t4f_save_default_price', 'uses' => 'ProductController@syncToT4fDefaultPrice']);
					Route::any('/product-listing/export', ['as' => 'product_management_product_listing_export', 'uses' => 'ProductController@exportAllProducts']);
                    Route::get('{productId}/copy-admin-product', ['as' => 'copy_admin_product', 'uses' => 'ProductController@copyAdminProduct']);
                });

                Route::get('find-provider-all', ['as' => 'provider_search_list_all', 'uses' => 'DefaultController@providerMatchAll']);
                Route::get('find-provider', ['as' => 'provider_search_list', 'uses' => 'DefaultController@providerMatch']);
                Route::get('find-product', ['as' => 'product_search_list', 'uses' => 'DefaultController@allProductsGet']);

                /* #144 */
                /* - Common route for both product Start - */
                Route::any('/change-status', ['middleware' => 'group.permission:product-management','as' => 'product_manage_status_change', 'uses' => 'ProductController@changeProductStatus']);

                Route::group(['prefix' => '{productLine}/{id}/edit/{tab}', 'middleware' => ['product.validate']], function ($group) {

                    Route::group(['middleware' => 'group.permission:product-management'], function () {
                        Route::delete('/{operationId}', ['as' => 'product_ajax_data_controller_delete', 'uses' => 'MiddlewareController@response']);
                        // Image tab routes start
                        Route::post("/upload-image", ["as" => "admin-product-request-upload-image", "uses" => "MiddlewareController@uploadImage"]);
                        Route::get("/delete-image/{productExtraImageId?}", ["as" => "admin-product-delete-image", "uses" => "MiddlewareController@deleteImage"]);
                        // Image tab routes end
                        
                        Route::get('/delete/{productRateTypeId}', ['as' => 'delete_product_rate_type', 'uses' => 'MiddlewareController@deleteRateType']);
                        
                        Route::get('/add-itinerary-day', ['as' => 'add-itinerary-day', 'uses' => 'MiddlewareController@addItineraryDay']);
                        Route::get('/delete-itinerary-day/{itineraryId}', ['as' => 'delete_product_itinerary_day', 'uses' => 'MiddlewareController@deleteItineraryDay']);
                        Route::get("/fetch-ownexpense-list-item/{providerId}/{listId}/{languageId}", ['as' => 'itinerary_fetch_ownexpense_list_item', 'uses' => 'MiddlewareController@fetchOwnexpenseListItem']);
                        Route::get('/remove-ownexpense-item/{itineraryId}/{listId}/{itemId}', ['as' => 'itinerary_remove_provider_ownexpense', 'uses' => 'MiddlewareController@removeProviderOwnexpense']);
                        Route::get("/clone-ownexpense/{providerId}/{listId}/{languageId}", ["as" => "itinerary_clone_ownexpense", "uses" => "MiddlewareController@cloneOwnexpense"]);
                        Route::delete('/{upgrade_id}/delete', ['as' => 'product_upgrade_delete', 'uses' => 'MiddlewareController@productUpgradeDelete']);
                    });
                    
                    Route::get('/', ['as' => 'product_main_controller', 'uses' => 'MiddlewareController@layout']);
                    Route::get('/response/{statusPage?}/{historyPage?}/{statusHistoryPage?}', ['as' => 'product_ajax_data_controller', 'uses' => 'MiddlewareController@response']);

                    Route::get('get-operation-departure-description/{operationId}', ['as' => 'get_operation_departure_description', 'uses' => 'MiddlewareController@getOperationDepartureDescription']);
                    Route::post('set-operation-departure-description/{operationId}', ['as' => 'set_operation_departure_description', 'uses' => 'MiddlewareController@setOperationDepartureDescription']);
                    
                    Route::get('/clone-upgrade/{upgradeId}', ['as' => 'product_upgrade_clone', 'uses' => 'MiddlewareController@productUpgradeClone']);
                    Route::post('/', ['as' => 'product_editor_basic_post_method', 'uses' => 'MiddlewareController@post']);
                    Route::get('get-upgrade-description/{info}/{upgradeId}', ['as' => 'get_upgrade_description', 'uses' => 'MiddlewareController@productUpgradeDescription']);
                    Route::post('set-upgrade-description/{upgradeId}', ['as' => 'set_upgrade_description', 'uses' => 'MiddlewareController@setUpgradeDescription']);
                    Route::get('/diff-rates/{upgradeId}/option/{providerUpgradeItemId}/get', ['as' => 'upgrade_diff_rates_get', 'uses' => 'MiddlewareController@getDiffRates']);
                    Route::post('/diff-rates/save', ['as' => 'upgrade_diff_rates_save', 'uses' => 'MiddlewareController@saveDiffRates']);
                    Route::post('{upgradeId}/diff-rates/option/{optionId}/delete', ['as' => 'upgrade_diff_rates_delete', 'uses' => 'MiddlewareController@deleteDiffRatesOption']);

                    // Rate Type Tab routes start
                    Route::post('/get-default', ['as' => 'get_product_default_rate_type', 'uses' => 'MiddlewareController@getDefaultRateType']);
                    Route::get('get-ratetype-description', ['as' => 'get_rate_type_description', 'uses' => 'MiddlewareController@getRateTypeDescription']);
                    Route::post('set-ratetype-description', ['as' => 'set_rate_type_description', 'uses' => 'MiddlewareController@setRateTypeDescription']);
                    // Rate Type Tab routes end

                    //Itinerary Tab routes starts
                    Route::get('/getcities', ['as' => 'find_itinerary_city', 'uses' => 'MiddlewareController@searchKeyword']);
                    Route::get('/getattractions', ['as' => 'find_itinerary_attraction', 'uses' => 'MiddlewareController@searchAttractions']);
                    Route::post('/add-visiting-html', ['as' => 'add_itinerary_visited_html', 'uses' => 'MiddlewareController@addVisitedHtml']);
                    Route::post("/upload-itinerary-image", ["as" => "admin-product-request-upload-itinerary-image", "uses" => "MiddlewareController@uploadItineraryImage"]);
                    Route::post("/rearrange-days", ["as" => "rearrange_itinerary_days", "uses" => "MiddlewareController@rearrangeItineraryDays"]);
                    Route::get("/search-ownexpense/{providerId}/{languageId}", ["as" => "itinerary_search_ownexpense", "uses" => "MiddlewareController@searchOwnExpense"]);
                    Route::post('/save-provider-ownexpense/{providerId}', ['as' => 'itinerary_save_provider_ownexpense', 'uses' => 'MiddlewareController@saveProviderOwnexpense']);
                    //Itinerary Tab routes ends

                    Route::post('/updateSpecial', ['as' => 'product_editor_manage_save_special', 'uses' => 'MiddlewareController@updateSpecial']);

                    Route::get('/option-delete/{upgradeOptionId}', ['as' => 'product_upgrade_option_delete', 'uses' => 'MiddlewareController@productUpgradeOptionDelete']);
                    Route::post('/check-product-title-exists', ['as' => 'product_management_check_product_title', 'uses' => 'MiddlewareController@checkProductTitleExists']);
                    foreach ($group->getRoutes() as $route) {
                        $route->where('id', '^[0-9-_\/]+$');
                    }

                });

                Route::get('tourbms-product-update-cron', ['as' => 'tourbms_product_update_cron', 'uses' => 'DefaultController@getProductUpdateCron']);

                Route::group(['prefix' => '{productLine}/{productId}/{tab}/{actionName}', 'middleware' => ['product.validate']], function ($group) {
                    Route::group(['middleware' => 'group.permission:product-management'], function () {
                        Route::any('/delete/{id}', ['as' => 'product_manage_delete', 'uses' => 'MiddlewareController@deleteRequest']);
                    });
                    Route::any('/fetch-other-details', ['as' => 'product_manage_fetch_others', 'uses' => 'MiddlewareController@fetchData']);
                    Route::any('/', ['as' => 'product_manage_fetch_expire', 'uses' => 'MiddlewareController@fetchData']);

                    foreach ($group->getRoutes() as $route) {
                        $route->where('productId', '^[0-9-_\/]+$');
                    }
                });
                /* #144 */

                Route::group(['prefix' => '{productLine}/{id}/edit', 'middleware' => ['product.validate']], function () {
                    Route::get('passenger', ['as' => 'product_management_edit_passenger_attribute', 'uses' => 'PassengerController@editPassenger']);
                    Route::post('passenger', ['as' => 'product_management_update_passenger_attribute', 'uses' => 'PassengerController@updatePassenger'])->middleware('product.permission:PRODUCT_MODIFY');
                });

                //Passenger Update for booking
                Route::group(['prefix' => '{productLine}', 'middleware' => ['product.validate']], function () {
                    Route::get('passenger', ['as' => 'product_management_edit_provider_passenger', 'uses' => 'PassengerController@editPassenger']);
                    Route::post('passenger', ['as' => 'product_management_update_provider_passenger', 'uses' => 'PassengerController@updatePassenger']);
                });
                /* -  Common route for both product End -  */

					//custom tour routes
					Route::get('custom/find-provider-all', ['as' => 'provider_search_list_all', 'uses' => 'DefaultController@providerMatchAll']);
					Route::group (['prefix' => 'custom/product', 'namespace' => 'CustomProduct'], function () {
						Route::post('/add-visiting-html', ['as' => 'add_custom_itinerary_visited_html', 'uses' => 'ProductController@addVisitedHtml']);
						Route::any ('/', ['as' => 'custom_product_list', 'uses' => 'ProductController@all'])->middleware('product.permission:PRODUCT_VIEW');
						Route::post('/export', ['as' => 'custom_product_listing_export', 'uses' => 'ProductController@exportAllProducts']);
						Route::any ('/create', ['as' => 'create_custom_product','uses' => 'ProductController@doCreate']);

						Route::group(['middleware' => ['product.permission:PRODUCT_MODIFY']], function () {
							Route::any('/change-tour-status', ['as' => 'change_tour_status', 'uses' => 'ProductController@doPublish']);
							Route::any('/change-stock-status', ['as' => 'change_stock_status', 'uses' => 'ProductController@changeStockStatus']);
							//Route::any('create', 'ProductController@doCreate');
							Route::get('/delete/soldOutDate', 'RemainingController@deleteProductSoldOut');
							Route::get('/delete/remainingSeat', 'RemainingController@deleteRemainingSeat');
							Route::get('debug', 'ProductController@debug');
						});
                        
						Route::group(['prefix' => '/{productId}/edit', 'middleware' => ['product.validate']], function () {
							
							Route::group(['prefix' => 'basic'], function () {
								Route::get('/', ['as' => 'custom_product_edit_basic', 'uses' => 'BasicController@get']);
								Route::group(['middleware' => ['product.permission:PRODUCT_MODIFY']], function () {
									//Route::post('/', 'BasicController@update');
									Route::post('/', ['as' => 'custom_editor_manage_post_basic','uses' => 'BasicController@update']);
									Route::any('/status_change', ['as' => 'custom_product_status_reason', 'uses' => 'BasicController@changeStatusReason']);
								});
							});
							
							Route::group(['prefix' => 'description'], function () {
								Route::get('/', ['as' => 'custom_product_edit_description', 'uses' => 'DescriptionController@get']);
								Route::post('/', ['as' => 'custom_product_save_description', 'uses' => 'DescriptionController@update'])->middleware('product.permission:PRODUCT_MODIFY');
								Route::get('/{lang}', ['as' => 'custom_product_get_description_by_lang', 'uses' => 'DescriptionController@getDescriptionByLang']);
							Route::post('check_name', ['as' => 'check_tour_product_name', 'uses' => 'DescriptionController@checkProductName']);
							});
							
							
							Route::group(['prefix' => 'image'], function () {
								Route::get('/', ['as' => 'custom_product_edit_image', 'uses' => 'ImageController@get']);
								
								Route::group(['middleware' => ['product.permission:PRODUCT_MODIFY']], function () {
									Route::any('/update', ['as' => 'custom_product_update_image', 'uses' => 'ImageController@update']);
									Route::any('/update/ajax', ['as' => 'custom_product_update_image_ajax', 'uses' => 'ImageController@updateExtraImage']);
									Route::post('/delete-multiple', ['as' => 'custom_product_delete_extra_images', 'uses' => 'ImageController@deleteExtraImages']);
									Route::post('/', ['as' => 'custom_product_save_image', 'uses' => 'ImageController@setImageTabRequest']);
									Route::get("/delete-image/{productExtraImageId}", ["as" => "admin-custom-product-delete-image", "uses" => "ImageController@deleteImage"]);
								});

							});
							
							Route::group(['prefix' => 'itinerary'], function () {
								Route::get('/add-itinerary-custom-product-day', ['as' => 'add-custom-product-itinerary-day', 'uses' => 'ItineraryController@addItineraryDay']);
								Route::get('/delete-itinerary-custom-product-day/{itineraryId}', ['as' => 'delete_custom_product_itinerary_day', 'uses' => 'ItineraryController@deleteItineraryDay']);
								Route::get('/', ['as' => 'custom_tour_itineray', 'uses' => 'ItineraryController@get']);
								Route::get('/{lang}', ['as' => 'custom_tour_itinerary_lang', 'uses' => 'ItineraryController@getByLang']);
								Route::post("/rearrange-days", ["as" => "rearrange_itinerary_custom_days", "uses" => "ItineraryController@rearrangeItineraryDays"]);
								Route::group(['middleware' => 'product.permission:PRODUCT_MODIFY'], function () {
									Route::put('/', ['as' => 'custom_update_tour_itinerary', 'uses' => 'ItineraryController@update']);
									Route::post('/', ['as' => 'custom_update_tour_itinerary', 'uses' => 'ItineraryController@update']);
									//Route::post('/', ['as' => 'custom_add_tour_itinerary', 'uses' => 'ItineraryController@add']);
									Route::post('updateAll/{lang}', 'ItineraryController@updateAll');
									Route::get('deleteItineraryDay/{lang}', 'ItineraryController@deleteItineraryDay');
									Route::get('deleteItinerary', 'ItineraryController@deleteItinerary');
								});
							});
							
							
						});

						Route::group(['prefix' => 'itinerary'], function () {
							Route::get('/getcities', ['as' => 'custom_get_cities', 'uses' => 'ItineraryController@searchKeyword']);
							Route::get('/getattractions', ['as' => 'custom_get_attractions', 'uses' => 'ItineraryController@searchAttractions']);
							Route::get('/gethotels', ['as' => 'custom_get_hotels', 'uses' => 'ItineraryController@searchHotels']);
							Route::get('/getmeals', ['as' => 'custom_get_meals', 'uses' => 'ItineraryController@searchMeals']);
							Route::get('/gettransports', ['as' => 'custom_get_transports', 'uses' => 'ItineraryController@searchTransports']);
						});
						
					});
					//custom tour routes ends
                /* -  Tour Product Route Start -  */
                Route::group(['prefix' => 'tour', 'namespace' => 'TourProduct'], function () {
                    
                    Route::any('create', ['middleware' => 'group.permission:product-management','as' => 'product_manage_create_tour', 'uses' => 'ProductController@doCreate']);
                    Route::group(['prefix' => '/{id}/edit', 'middleware' => ['product.validate']], function () {
                        //[BASIC]
                        Route::group(['prefix' => 'basic'], function () {
                            Route::get('/', ['as' => 'tour_editor_manage_get_basic', 'uses' => 'BasicController@get']);
                            Route::post('/', ['as' => 'tour_editor_manage_post_basic', 'uses' => 'BasicController@update']);
                            Route::any('/status_change', ['as' => 'tour_editor_manage_status_reason', 'uses' => 'BasicController@changeStatusReason']);
                            Route::any('/backend_change', ['as' => 'tour_editor_manage_backend_reason', 'uses' => 'BasicController@changeBackendReason']);
                        });

                    });
                });
                /* -  Tour Product Route End -  */

                /* -  Activity Product Route Start -  */
                Route::group(['prefix' => 'activity', 'namespace' => 'ActivityProduct'], function () {

                    Route::any('create', ['middleware' => 'group.permission:product-management','as' => 'product_manage_create_activity', 'uses' => 'ProductController@doCreate']);
                    Route::group(['prefix' => '/{id}/edit', 'middleware' => ['product.validate']], function () {
                        //[BASIC]
                        Route::group(['prefix' => 'basic'], function () {
                            Route::get('/', ['as' => 'activity_editor_manage_get_basic', 'uses' => 'BasicController@get']);
                            Route::post('/', ['as' => 'activity_editor_manage_post_basic', 'uses' => 'BasicController@update']);
                            Route::any('/status_change', ['as' => 'activity_editor_manage_status_reason', 'uses' => 'BasicController@changeStatusReason']);
                            Route::any('/backend_change', ['as' => 'activity_editor_manage_backend_reason', 'uses' => 'BasicController@changeBackendReason']);
                        });

                    });

                });
                /* -  Activity Product Route End -  */

                /* -  Transportation Product Route Start -  */
                Route::group(['prefix' => 'transportation', 'namespace' => 'TransportationProduct'], function () {
                    Route::any('create', ['middleware' => 'group.permission:product-management','as' => 'product_manage_create_transportation', 'uses' => 'ProductController@doCreate']);

                    Route::group(['prefix' => '/{id}/edit', 'middleware' => ['product.validate']], function () {
                        //[BASIC]
                        Route::group(['prefix' => 'basic'], function () {
                            Route::get('/', ['as' => 'transportation_editor_manage_get_basic', 'uses' => 'BasicController@get']);
                            Route::post('/', ['as' => 'transportation_editor_manage_post_basic', 'uses' => 'BasicController@update']);
                            Route::any('/status_change', ['as' => 'transportation_editor_manage_status_reason', 'uses' => 'BasicController@changeStatusReason']);
                            Route::any('/backend_change', ['as' => 'transportation_editor_manage_backend_reason', 'uses' => 'BasicController@changeBackendReason']);
                        });

                    });
                });
                /* -  Transportation Product Route End -  */

            });

            // TTD product edit route: START
            Route::group(['prefix' => 'ttd', 'namespace' => '\Modules\Ttd\Http\Controllers'], function () {

                Route::any('create/{product_type}', ['middleware' => 'group.permission:product-management','as' => 'product_manage_create_ttd', 'uses' => 'TtdProductController@doCreate'])->where('product_type', '^[0-9-_\/]+$');

                Route::group(['prefix'=>'product/{productId}/edit','middleware'=> ['product.validate']], function ($productId) {
                    Route::get('/', [ 'as' => 'product_editor_ttd_basic_get_new', 'uses' => 'TtdProductController@index']);
                    Route::get('/{action}', [ 'as' => 'product_editor_ttd_basic_get_new', 'uses' => 'TtdProductController@index'])->where('action', '(.*)');
                });
                Route::post('/common', 'TtdProductController@ttdCommon');
                Route::post('/info', 'TtdProductController@ttdInfo');

                // TTD order ext field
                Route::group(['prefix'=>'orderExtFieldConf'], function() {
                    Route::get('/', 'TtdProductController@orderExtView');
                    Route::get('/{action}', 'TtdProductController@orderExtView')->where('action', '(.*)');
                    Route::post('/common', 'TtdProductController@orderExtFieldCommon');
                });
            });

            // TTD product edit route: END

            Route::group(['middleware' => ['web', 'site.url'], 'prefix' => 'new-product-request', 'namespace' => 'Modules\ProviderProductRequest\Http\Controllers'], function () {
                Route::get('/listing', ['as' => 'product_management_list_new_product_request', 'uses' => 'BasicController@getAllRequestV1']);
                Route::get('/get-listing-combobox', ['as' => 'product_management_new_product_request_get_listing_combobox', 'uses' => 'BasicController@getListingCombobox']);

                Route::get('/product-detail/{productMasterId}', ['as' => 'product_management_get_product_detail_new', 'uses' => 'ProductDetailController@getProductDetailNew']);
                Route::get('/product-language-detail/{productMasterId}/{languageId?}', ['as' => 'get_new_product_audit_detail', 'uses' => 'ProductDetailController@getNewProductAuditDetail']);
                Route::get('/get-product-data/{productMasterId}/{languageId?}', ['as' => 'get_product_data_language_wise', 'uses' => 'ProductDetailController@getProductAuditData']);
                Route::post('/product-revise-message', ['as' => 'product_management_product_revise_message', 'uses' => 'ProductDetailController@reviseMessagePost']);
                Route::group(['prefix' => '{productMasterId}/view', 'middleware' => ['product.validate']], function () {
                    Route::get('basic', ['as' => 'product_management_provider_product_request_basic_view', 'uses' => 'BasicController@adminView']);
                });

                Route::any('export', 'ProductController@doExport')->middleware('product.permission:PRODUCT_DOWNLOAD');
                Route::group(['middleware' => 'group.permission:product-management'], function () {
                    Route::get('/approve-product-request/{productMasterId}', ['as' => 'product_management_approve_product_request', 'uses' => 'ProductDetailController@approveProductRequest']);
                    Route::post('/v1/approve-product-request/{productMasterId}', ['as' => 'product_management_approve_product_request_v1', 'uses' => 'ProductDetailController@approveProductRequestV1']);
                    Route::get('/status-activate-product/{productMasterId}/{languageId?}', ['as' => 'product_management_status_activate', 'uses' => 'ProductController@statusActivateProduct']);
                    Route::get('/status-deactivate-product/{productMasterId}/{languageId?}', ['as' => 'product_management_status_deactivate_product', 'uses' => 'ProductController@statusDeactivateProduct']);
                    Route::get('/get-product-request-audit-setting', ['as' => 'product_management_get_product_request_audit_setting', 'uses' => 'ProductController@getProductRequestAuditSetting']);
                    Route::post('/save-product-request-audit-setting', ['as' => 'product_management_save_product_request_audit_setting', 'uses' => 'ProductController@saveProductRequestAuditSetting']);
                });
                
                Route::group(['prefix' => 'audit'], function () {
                    Route::get('/list', ['as' => 'new_product_request_audit', function () {
                        return view('providerproductrequest::new-product-request-audits');
                    }]);

                    Route::get('/detail/{productId}/{langId}', ['as' => 'new_product_request_audit_detail', function ($productId, $langId) {
                        return view('providerproductrequest::new-product-request-audits', ['productId' => $productId, 'langId' => $langId]);
                    }]);

                    Route::get('/setting/{setting}', ['as' => 'new_product_request_audit_setting', function ($setting) {
                        return view('providerproductrequest::new-product-request-audits', ['setting' => $setting]);
                    }]);

                    Route::get('/operation-log/{logId}', ['as' => 'new_product_request_audit_log', function ($logId) {
                        return view('providerproductrequest::new-product-request-audits', ['logId' => $logId]);
                    }]);

                    Route::get('/log/list', ['as' => 'get_audit_operation_log_list', 'uses' => 'OperationController@auditOperationLogList']);

                    Route::get('/log/{logId}/detail', ['as' => 'get_audit_operation_log_list_detail', 'uses' => 'OperationController@auditOperationLogDetail']);
                });
            });

            Route::group(['namespace' => 'Modules\ProductEditor\Http\Controllers\ProductAssets'], function () {

                //Product Review
                Route::group(['prefix' => 'product-review', 'namespace' => 'Review'], function () {
                    Route::group(['middleware' => 'group.permission:product-management'], function () {
                        Route::get('/create', ['as' => 'product_management_create_review', 'uses' => 'ReviewController@create']);
                        Route::get('{id}/delete', ['as' => 'product_management_delete_review', 'uses' => 'ReviewController@delete']);
                    });
                    Route::get('/', ['as' => 'product_management_list_review', 'uses' => 'ReviewController@index']);
                    Route::post('/save', ['as' => 'product_management_save_review', 'uses' => 'ReviewController@save']);
                    Route::get('{id}/edit', ['as' => 'product_management_get_review', 'uses' => 'ReviewController@get']);
                    Route::get('/product-list', ['as' => 'product_management_product_list_review', 'uses' => 'ReviewController@getProducts']);
                });

                Route::group(['prefix' => '/product-tags'], function () {

                    //Product Tag
                    Route::group(['prefix' => '/tag-category', 'namespace' => 'TagCategory'], function () {
                        Route::group(['middleware' => 'group.permission:product-management'], function () {
                            Route::get('/create', ['as' => 'product_management_create_tag_category', 'uses' => 'TagCategoryController@create']);
                            Route::get('/delete-tag-category/{id}', ['as' => 'product_management_delete_tag_category', 'uses' => 'TagCategoryController@delete']);
                        });
                        Route::get('/', ['as' => 'product_management_list_tag_category', 'uses' => 'TagCategoryController@index']);
                        Route::post('save', ['as' => 'product_management_save_tag_category', 'uses' => 'TagCategoryController@save']);
                        Route::get('/edit-tag-category/{tagCategoryId}', ['as' => 'product_management_edit_tag_category', 'uses' => 'TagCategoryController@get']);
                        Route::post('/change-status/', ['as' => 'product_management_change_status_tag_category', 'uses' => 'TagCategoryController@changeStatus']);
                    });

                    Route::group(['prefix' => '/tag', 'namespace' => 'Tag'], function () {
                        Route::group(['middleware' => 'group.permission:product-management'], function () {
                            Route::get('/create', ['as' => 'product_management_create_tag', 'uses' => 'TagController@create']);
                            Route::get('/delete-tag/{id}', ['as' => 'product_management_delete_tag', 'uses' => 'TagController@delete']);
                        });
                        Route::get('/', ['as' => 'product_management_list_tag', 'uses' => 'TagController@index']);
                        Route::post('save', ['as' => 'product_management_save_tag', 'uses' => 'TagController@save']);
                        Route::get('/edit-tag/{id}', ['as' => 'product_management_edit_tag', 'uses' => 'TagController@get']);
                    });

                    Route::group(['prefix' => '/tag-manager', 'namespace' => 'TagManager'], function () {
                        Route::group(['middleware' => 'group.permission:product-management'], function () {
                            Route::get('/create', ['as' => 'product_management_create_tag_manager', 'uses' => 'TagManagerController@create']);
                            Route::get('/delete-tag-manager/{id}', ['as' => 'product_management_delete_tag_manager', 'uses' => 'TagManagerController@delete']);
                        });
                        Route::get('/', ['as' => 'product_management_list_tag_manager', 'uses' => 'TagManagerController@index']);
                        Route::post('save', ['as' => 'product_management_save_tag_manager', 'uses' => 'TagManagerController@save']);
                        Route::get('/edit-tag-manager/{id}', ['as' => 'product_management_edit_tag_manager', 'uses' => 'TagManagerController@get']);
                        Route::post('/change-status/', ['as' => 'product_management_change_status_tag_manager', 'uses' => 'TagManagerController@changeStatus']);
                    });
                });

                //Product Type
                Route::group(['prefix' => 'product-type','namespace' => 'ProductTypeManage'], function () {
                    Route::group(['middleware' => 'group.permission:product-management'], function () {
                        Route::get('add', ['as' => 'product_management_add_product_type', 'uses' => 'ProductTypeController@addProductType']);
                        Route::get('{id}/delete', ['as' => 'product_management_delete_product_type', 'uses' => 'ProductTypeController@delete']);
                    });
                    Route::any('/', ['as' => 'product_management_list_product_type', 'uses' => 'ProductTypeController@showList']);
                    Route::post('save', ['as' => 'product_management_save_product_type', 'uses' => 'ProductTypeController@save']);
                    Route::get('{id}/edit', ['as' => 'product_management_get_product_type', 'uses' => 'ProductTypeController@get']);
                    Route::any('/search', ['as' => 'product_management_search_product_type', 'uses' => 'ProductTypeController@showList']);
                });
                
                //Ticket Type
                Route::group(['prefix' => 'ticket-type','namespace' => 'TicketTypeManage'], function () {
                    Route::any('/', ['as' => 'product_management_list_ticket_type', 'uses' => 'TicketTypeController@showList']);
                    Route::group(['middleware' => 'group.permission:product-management'], function () {
                        Route::get('add', ['as' => 'product_management_add_ticket_type', 'uses' => 'TicketTypeController@addTicketType']);
                        Route::get('{id}/delete', ['as' => 'product_management_delete_ticket_type', 'uses' => 'TicketTypeController@delete']);
                    });
                    Route::post('save', ['as' => 'product_management_save_ticket_type', 'uses' => 'TicketTypeController@save']);
                    Route::get('{id}/edit', ['as' => 'product_management_get_ticket_type', 'uses' => 'TicketTypeController@get']);
                    Route::any('/search', ['as' => 'product_management_search_ticket_type', 'uses' => 'TicketTypeController@showList']);
                });

                //Default Rate Type
                Route::group(['prefix' => 'default-rate-type', 'namespace' => 'DefaultRateType'], function () {
                    Route::get('/', ['as' => 'product_management_get_default_rate_type', 'uses' => 'DefaultRateTypeController@getRateType']);
                    Route::post('/', ['as' => 'product_management_update_default_rate_type', 'uses' => 'DefaultRateTypeController@updateRateType']);
                });
            });
        });

        Route::group(['prefix' => 'agent-management', 'namespace' => '\App\Http\Controllers\WhiteLabel'], function () {
            Route::group(['middleware' => 'group.permission:agent-management'], function () {
                Route::get('/white-label-qa/{question_id}/reply', ['as' => 'white-label-qa', 'uses' => 'WhiteLabelController@getQuestion']);
                Route::get('white-label-qa/{question_id}/reply/{answer_id}/edit', ['as' => 'white-label-qa-reply-edit', 'uses' => 'WhiteLabelController@editAnswer']);
                //Delete
                Route::get('/white-label-qa/{question_id}/delete', ['as' => 'white-label-qa-delete', 'uses' => 'WhiteLabelController@deleteQuestion']);
            });
            Route::get('/white-label-qa', ['as' => 'white_label_qa', 'uses' => 'WhiteLabelController@getQa']);
            Route::post('/change-question-status', ['as' => 'change_question_status', 'uses' => 'WhiteLabelController@changeStatus']);
            //Reply
            Route::post('/white-label-qa-reply', ['as' => 'white_label_qa_reply', 'uses' => 'WhiteLabelController@saveReply']);
            //View
            Route::get('/white-label-qa/{question_id}/view', ['as' => 'white-label-qa-view', 'uses' => 'WhiteLabelController@viewQuestion']);
            Route::post('/white-label-qa-view-active', ['as' => 'white-label-qa-view-active', 'uses' => 'WhiteLabelController@saveViewActive']);
            //Edit Answer
            Route::post('/white-label-qa-reply-edit', ['as' => 'white_label_qa_reply_edit', 'uses' => 'WhiteLabelController@editReply']);
            Route::post('/all_white_label_qa/export', ['as' => 'export_all_white_label_qa', 'uses' => 'WhiteLabelController@exportAllWhiteLabelQa'])->middleware('product.permission:PRODUCT_DOWNLOAD');
        });

        Route::group(['prefix' => '/admin-system-setting'], function () {

            //Configuration routing is done here
            Route::group(['prefix' => 'configuration', 'namespace' => 'App\Http\Controllers\Configuration'], function () {

                Route::get('/all', ['as' => 'new_admin_setting_configuration_all', 'uses' => 'ConfigurationController@listConfiguration']);
                Route::get('/add', ['as' => 'new_admin_setting_configuration_add', 'uses' => 'ConfigurationController@addConfiguration']);
                Route::post('/save', ['as' => 'new_admin_setting_configuration_save', 'uses' => 'ConfigurationController@saveConfiguration']);
                Route::get('/edit/{id}', ['as' => 'new_admin_setting_configuration_edit', 'uses' => 'ConfigurationController@editConfiguration']);
                Route::get('/delete/{id}', ['as' => 'new_admin_setting_configuration_delete', 'uses' => 'ConfigurationController@deleteConfiguration']);

                //configuration group route goes here
                Route::group(['prefix' => 'group'], function () {
                    Route::get('/all', ['as' => 'new_admin_setting_configuration_group_all', 'uses' => 'ConfigurationController@listConfigurationGroup']);
                    Route::get('/add', ['as' => 'new_admin_setting_configuration_group_add', 'uses' => 'ConfigurationController@addConfigurationGroup']);
                    Route::post('/save', ['as' => 'new_admin_setting_configuration_group_save', 'uses' => 'ConfigurationController@saveConfigurationGroup']);
                    Route::get('/edit/{id}', ['as' => 'new_admin_setting_configuration_group_edit', 'uses' => 'ConfigurationController@editConfigurationGroup']);
                    Route::get('/delete/{id}', ['as' => 'new_admin_setting_configuration_group_delete', 'uses' => 'ConfigurationController@deleteConfigurationGroup']);
                    Route::get('/change-status/{id}', ['as' => 'new_admin_setting_configuration_group_change_status', 'uses' => 'ConfigurationController@changeStatus']);
                });
            });

            //User
            Route::group(['prefix' => 'users', 'namespace' => 'Modules\User\Http\Controllers'], function () {

                Route::get('/', ['as' => 'new_system_setting_admin', 'uses' => 'AdminController@index']);
                Route::get('/authorize', ['as' => 'new_system_setting_admin_authorize', 'uses' => 'AdminController@adminAuthorize']);

                Route::get('/getusers', ['as' => 'get_users', 'uses' => 'AdminController@getUsers']);
                Route::get('/getgroups', ['as' => 'get_groups', 'uses' => 'AdminController@getGroups']);
                Route::get('/getusergroup', ['as' => 'get_user_group', 'uses' => 'AdminController@getUserGroup']);
                Route::get('/authorizeusergroup', ['as' => 'authorize_user_group', 'uses' => 'AdminController@authorizeUserGroup']);

                Route::get('/getgroupdata', ['as' => 'get_group_data', 'uses' => 'AdminController@getGroupData']);
                Route::get('/getgroupwisedata', ['as' => 'get_group_wise_data', 'uses' => 'AdminController@getGroupWiseData']);
                Route::get('/getpermissiondata', ['as' => 'get_permission_data', 'uses' => 'AdminController@getPermissionData']);
                Route::get('/getgroupusers', ['as' => 'get_group_users', 'uses' => 'AdminController@getGroupUsers']);
                Route::get('/userlist', ['as' => 'user_list', 'uses' => 'AdminController@userList']);
                Route::get('/authorizegroupuser', ['as' => 'authorize_group_user', 'uses' => 'AdminController@authorizeGroupUser']);

                //New Url Chagne
                Route::get('/get-user-data', ['as' => 'get_user_data', 'uses' => 'AdminController@getUserData']);
                Route::get('/authorize-user', ['as' => 'authorize_user', 'uses' => 'AdminController@authorizeUser']);
                Route::get('/authorize-group', ['as' => 'authorize_group', 'uses' => 'AdminController@authorizeGroup']);
                Route::get('/authorize-permission', ['as' => 'authorize_permission', 'uses' => 'AdminController@authorizePermission']);

                //Permission Tab
                Route::get('/userpermission', ['as' => 'user_permission', 'uses' => 'AdminController@userPermission']);
                Route::get('/permissions', ['as' => 'permissions', 'uses' => 'AdminController@getPermissions']);

                //group
                Route::get('/create-group', ['as' => 'new_system_setting_create_group', 'uses' => 'AdminController@createGroup']);
                Route::post('/save-group', ['as' => 'new_system_setting_save_group', 'uses' => 'AdminController@saveGroup']);
                Route::any('/list-group', ['as' => 'new_system_setting_list_group', 'uses' => 'AdminController@listGroup']);
                Route::any('/delete-group/{groupId}', ['as' => 'new_system_setting_delete_group', 'uses' => 'AdminController@deleteGroup']);
                Route::get('/edit-group/{groupId}', ['as' => 'new_system_setting_edit_group', 'uses' => 'AdminController@editGroup']);

                Route::get('/url-access-control', ['as' => 'new_system_setting_url_access_control', 'uses' => 'AdminController@urlDomainAccessList']);
                Route::post('/save-url-access', ['as' => 'new_system_setting_save_url_access', 'uses' => 'AdminController@updateUrlDomainAccessList']);
                Route::get('/detail-permission/{groupId}', ['as' => 'new_system_setting_permission_detail', 'uses' => 'AdminController@permissionDetail']);

                //Permission
                Route::get('/scan-permission', ['as' => 'new_system_setting_permission_scan', 'uses' => 'AdminController@scanPermission']);
                Route::get('/create-permission', ['as' => 'new_system_setting_permission_create', 'uses' => 'AdminController@createPermission']);
                Route::post('/save-permission', ['as' => 'new_system_setting_permission_save', 'uses' => 'AdminController@savePermission']);
                Route::any('/list-permission', ['as' => 'new_system_setting_permission_list', 'uses' => 'AdminController@listPermission']);
                Route::any('/delete-permission/{permissionId}', ['as' => 'new_system_setting_permission_delete', 'uses' => 'AdminController@deletePermission']);
                Route::get('/edit-permission/{permissionId}', ['as' => 'new_system_setting_permission_edit', 'uses' => 'AdminController@editPermission']);

                //User
                Route::get('/create', ['as' => 'new_system_setting_user_create', 'uses' => 'AdminController@createUser']);
                Route::any('/save-user', ['as' => 'new_system_setting_user_save', 'uses' => 'AdminController@saveUser']);
                Route::get('/modify-user/{userId}', ['as' => 'new_system_setting_user_edit', 'uses' => 'AdminController@editUser']);
                Route::post('/change-status/{user_id}', ['as' => 'new_system_setting_user_change_status', 'uses' => 'AdminController@changeStatus']);
                Route::get('/all-users', ['as' => 'new_system_setting_all_users_list', 'uses' => 'SubAccountController@allUsers']);
                Route::get('/delete-user', ['as' => 'my_account_user_delete', 'uses' => 'AdminController@deleteUSer']);
                Route::post('/check-new-user-email', ['as' => 'check_new_user_email', 'uses' => 'AdminController@updateExistUserRole']);
                //Auth
                Route::any('/get-auth', ['as' => 'new_system_setting_auth_type', 'uses' => 'AdminController@getAuth']);
                Route::any('/save-auth', ['as' => 'new_system_setting_save_auth', 'uses' => 'AdminController@saveAuth']);
                Route::post('/save-sub-account', ['as' => 'new_system_setting_save_sub_account', 'uses' => 'SubAccountController@saveSubAccount']);

            });

            Route::group(['middleware' => ['web', 'site.url', 'url.restriction'], 'prefix' => 'my-account/users', 'namespace' => 'Modules\User\Http\Controllers'], function () {
                Route::any('/save-operator-user', ['as' => 'my_account_operator_user_save', 'uses' => 'AdminController@saveOperatorSubUser']);
                Route::get('/permission/{userId}', ['as' => 'check_permission_operator_user_page', 'uses' => 'AdminController@checkOperatorUserPagePermission']);
                Route::post('/permission', ['as' => 'set_permission_operator_user_page', 'uses' => 'AdminController@setOperatorUserPagePermission']);
                Route::get('/unlink-account/{userId}/{providerId}', ['as' => 'unlink_operator_account', 'uses' => 'AdminController@unLinkOperatorUserAccount']);
                Route::get('/change-account-status/{userId}', ['as' => 'change_account_status', 'uses' => 'AdminController@operatorUserChangeStatus']);
                Route::get('/change-email-notice/{userId}', ['as' => 'change_email_notice', 'uses' => 'AdminController@operatorUserChangeEmailNotice']);
                Route::get('/check-link-email-account-operator', ['as' => 'check_link_email_account_operator', 'uses' => 'AdminController@checkOperatorSubAccount']);

            });

        });

        //messages group route goes here
        Route::group(['prefix' => 'message', 'namespace' => 'App\Http\Controllers\Messages'], function () {
            Route::get('/inbox_message_count', ['as' => 'inbox-message-count', 'uses' => 'MessageController@getInboxMessageCount']);
            Route::get('/', ['as' => 'message_list', 'uses' => 'MessageController@messageList']);
            Route::get('/read/{message_id}', ['as' => 'message_read', 'uses' => 'MessageController@messageRead']);
            Route::get('responded/read/{message_id}', ['as' => 'send_message_read', 'uses' => 'MessageController@messageSendRead']);
            Route::get('/responded', ['as' => 'send_message_list', 'uses' => 'MessageController@sendMessageList']);
            Route::get('/compose', ['as' => 'message_compose', 'uses' => 'MessageController@messageForm']);
            Route::get('/get-notification-status', ['as' => 'get_notification_status', 'uses' => 'MessageController@getNotificationStatus']);
            Route::get('/no-response/{message_id}', ['as' => 'no-response', 'uses' => 'MessageController@saveNoresponseNeeded']);
            Route::post('/send', ['as' => 'Send_message', 'uses' => 'MessageController@saveMessage']);
            Route::post('/replay', ['as' => 'replay_message', 'uses' => 'MessageController@saveReplayMessage']);
            Route::post('/save-image', ['as' => 'save_image', 'uses' => 'MessageController@saveImage']);
            Route::get('/search_management', ['as' => 'search-management', 'uses' => 'MessageController@searchManagement']);
        });

        Route::group(['prefix' => '/tools'], function () {
            Route::group(['namespace' => 'App\Http\Controllers\EmailTool'], function () {
                Route::get('/email-records', ['as' => 'email_tools', 'uses' => 'EmailToolController@getAllEmails']);
                Route::get('/email-record/{id}', ['as' => 'email_tools_detail', 'uses' => 'EmailToolController@getEmailDetail']);
            });

            Route::group(['namespace' => 'App\Http\Controllers\Announcement'], function () {
                Route::get('/announcements', ['as' => 'announcements', 'uses' => 'AnnouncementsController@getAllAnnouncements']);
                Route::get('/announcement-delete/{annoucement_id}', ['as' => 'announcement-delete', 'uses' => 'AnnouncementsController@deleteAnnoucement']);
                Route::get('/new-create-announcement', ['as' => 'new_create_announcement', 'uses' => 'AnnouncementsController@newCreateAnnouncement']);
                Route::post('/add-announcement', ['as' => 'add_announcement', 'uses' => 'AnnouncementsController@addAnnouncement']);
                Route::get('/edit-announcement/{annoucement_id}', ['as' => 'edit-announcement', 'uses' => 'AnnouncementsController@getEditAnnouncement']);
                Route::post('/post-edit-announcement', ['as' => 'post-edit-announcement', 'uses' => 'AnnouncementsController@editAnnouncement']);
                Route::any('/announcement-status-change', ['as' => 'announcement_status_change', 'uses' => 'AnnouncementsController@changeAnnouncementStatus']);
            });

            Route::group(['prefix' => '/help-center', 'namespace' => 'App\Http\Controllers'], function () {
                Route::get('/', ['as' => 'help-center', 'uses' => 'HelpCenterController@dashboardFe']);
                Route::post('/doc', 'HelpCenterController@createDoc');
                Route::get('/doc', 'HelpCenterController@searchDoc');
                Route::get('/doc/{id}', 'HelpCenterController@getDocById');
                Route::put('/doc/{id}', 'HelpCenterController@modifyDoc');
                Route::delete('/doc/{id}', 'HelpCenterController@dropDocById');
                Route::get('/category', 'HelpCenterController@getAllCategory');
                Route::post('/category', 'HelpCenterController@createCategory');
                Route::get('/category/{id}', 'HelpCenterController@getCategoryById');
                Route::put('/category/{id}', 'HelpCenterController@modifyCategory');
                Route::delete('/category/{id}', 'HelpCenterController@dropCategoryById');
                Route::get('/tag', 'HelpCenterController@getAllTag');
                Route::post('/tag', 'HelpCenterController@createTag');
                Route::delete('/tag/{id}', 'HelpCenterController@dropTag');
                Route::post('/upload-image', 'HelpCenterController@uploadImage');
            });
        });

        // Database Manage Routes
        Route::group(['prefix' => '/database-management'], function () {

            Route::group(['prefix' => '/change-stock-status', 'namespace' => 'Modules\ProductEditor\Http\Controllers\Product'], function () {
                Route::get('/{take}/{page}', ['as' => 'change_stock_status', 'uses' => 'DefaultController@changeProductStockStatus']);
            });

            Route::group(['prefix' => '/update-product-desc', 'namespace' => 'Modules\ProductEditor\Http\Controllers\Product'], function () {
                Route::get('/{take}/{page}', ['as' => 'update_product_desc', 'uses' => 'DefaultController@updateProductDesc']);
            });

            //Currency
            Route::group(['prefix' => '/currency', 'namespace' => 'Modules\Localization\Http\Controllers'], function () {
                Route::any('/', ['as' => 'database_manage_currency_list', 'uses' => 'CurrencyController@index']);
                Route::get('create', ['as' => 'database_manage_currency_list_save', 'uses' => 'CurrencyController@create']);
                Route::post('save', ['as' => 'database_manage_currency_list_save', 'uses' => 'CurrencyController@save']);
                Route::get('get/{id}', ['as' => 'database_manage_currency_list_get', 'uses' => 'CurrencyController@get']);
                Route::get('delete/{id}', ['as' => 'database_manage_currency_list_delete', 'uses' => 'CurrencyController@delete']);
            });

            //Region Specialist
            Route::group(['prefix' => '/region-specialist', 'namespace' => 'App\Http\Controllers\RegionSpecialist'], function () {
                Route::get('/', ['as' => 'database_manage_region_specialist_get', 'uses' => 'RegionSpecialistController@get']);
                Route::get('/statusChange/{id}', ['as' => 'database_manage_region_specialist_status', 'uses' => 'RegionSpecialistController@statusChange']);
                Route::get('/edit/{id}', ['as' => 'database_manage_region_specialist_edit', 'uses' => 'RegionSpecialistController@edit']);
                Route::get('/delete/{id}', ['as' => 'database_manage_region_specialist_delete', 'uses' => 'RegionSpecialistController@delete']);
                Route::post('/save', ['as' => 'database_manage_region_specialist_save', 'uses' => 'RegionSpecialistController@save']);
            });

            Route::group(['prefix' => '/location-setting', 'namespace' => 'Modules\Location\Http\Controllers'], function () {

                Route::get('/', 'LocationController@index');
                Route::get('/match.json', ['as' => 'location_list', 'uses' => 'LocationController@match']);
                //Continent
                Route::group(['prefix' => 'continent'], function () {
                    Route::get('/', ['as' => 'database_manage_location_continent_list', 'uses' => 'ContinentController@index']);
                    Route::get('/create', ['as' => 'database_manage_location_continent_create', 'uses' => 'ContinentController@create']);
                    Route::post('/submit', ['as' => 'database_manage_location_continent_save', 'uses' => 'ContinentController@update']);
                    Route::any('/{continent_id}/edit', ['as' => 'database_manage_location_continent_edit', 'uses' => 'ContinentController@edit']);
                    Route::get('/delete/{continent_id}', ['as' => 'database_manage_location_continent_delete', 'uses' => 'ContinentController@deleteContinent']);
                    Route::post('/change-status/{continent_id}', ['as' => 'database_manage_location_continent_change_status', 'uses' => 'ContinentController@changeStatus']);
                });

                //City
                Route::group(['prefix' => 'tourcity'], function () {
                    Route::any('/', ['as' => 'database_manage_tourcity_list', 'uses' => 'TourCityController@index']);
                    Route::get('/create', ['as' => 'database_manage_tourcity_create', 'uses' => 'TourCityController@create']);
                    Route::post('/save', ['as' => 'database_manage_tourcity_save', 'uses' => 'TourCityController@save']);
                    Route::get('/{id}/edit', ['as' => 'database_manage_tourcity_get', 'uses' => 'TourCityController@get']);
                    Route::get('/change/{id}', ['as' => 'tourcity_change', 'uses' => 'TourCityController@change']);
                    Route::get('/delete/{id}', ['as' => 'database_manage_tourcity_delete', 'uses' => 'TourCityController@delete']);
                    Route::get('/region-list', ['as' => 'database_manage_region_list', 'uses' => 'TourCityController@getRegions']);
                    Route::get('/zone-list', ['as' => 'database_manage_zone_list', 'uses' => 'TourCityController@getZones']);
                    Route::any('/create/ownexpense/{cityId}', ['as' => 'database_manage_ownexpense_create', 'uses' => 'TourCityController@createOwnExpense']);
                    Route::get('/edit/ownexpense/{id}', ['as' => 'database_manage_ownexpense_edit', 'uses' => 'TourCityController@editOwnExpenese']);
                    Route::post('/save-ownexpense', ['as' => 'database_manage_ownexpense_save', 'uses' => 'TourCityController@saveOwnExpense']);
                    Route::get('/change/ownexpense/{id}', ['as' => 'database_manage_ownexpense_change', 'uses' => 'TourCityController@changeState']);
                    Route::get('/delete/ownexpense/{id}', ['as' => 'database_manage_ownexpense_delete', 'uses' => 'TourCityController@deleteOwnExpense']);
                    Route::get('/edit/ownexpense-list/{cityId}', ['as' => 'database_manage_ownexpense_list', 'uses' => 'TourCityController@ownExpenseAll']);
                });

                //Country
                Route::group(['prefix' => 'country'], function () {
                    Route::get('/', ['as' => 'database_manage_location_country_list', 'uses' => 'CountryController@index']);
                    Route::get('/create', ['as' => 'database_manage_location_country_create', 'uses' => 'CountryController@create']);
                    Route::get('/{id}/edit', ['as' => 'database_manage_location_country_edit', 'uses' => 'CountryController@get']);
                    Route::post('/save', ['as' => 'database_manage_location_country_save', 'uses' => 'CountryController@save']);
                    Route::get('/delete/{id}', ['as' => 'database_manage_location_country_delete', 'uses' => 'CountryController@delete']);
                });

                // Region
                Route::group(['prefix' => 'region'], function () {
                    Route::get('/', ['as' => 'database_manage_location_region_list', 'uses' => 'RegionController@index']);
                    Route::post('/submit', ['as' => 'database_manage_location_region_save', 'uses' => 'RegionController@update']);
                    Route::get('/get/{region_id}', ['as' => 'database_manage_location_region_get', 'uses' => 'RegionController@get']);
                    Route::get('/delete/{region_id}', ['as' => 'database_manage_location_region_delete', 'uses' => 'RegionController@delete']);
                    Route::post('/change-status/{region_id}', ['as' => 'database_manage_location_region_change_status', 'uses' => 'RegionController@changeStatus']);
                    Route::get('/country-list', ['as' => 'database_manage_country_list', 'uses' => 'RegionController@getCountries']);
                    Route::get('/country-code-list', ['as' => 'country_code_list', 'uses' => 'RegionController@getCountriesWithCode']);
                });

                // Zone
                Route::group(['prefix' => 'zone'], function () {
                    Route::get('/', ['as' => 'database_manage_location_zone_list', 'uses' => 'ZoneController@index']);
                    Route::post('/submit', ['as' => 'database_manage_location_zone_save', 'uses' => 'ZoneController@update']);
                    Route::get('/get/{zone_id}', ['as' => 'database_manage_location_zone_get', 'uses' => 'ZoneController@get']);
                    Route::get('/delete/{zone_id}', ['as' => 'database_manage_location_zone_delete', 'uses' => 'ZoneController@delete']);
                    Route::post('/change-status/{zone_id}', ['as' => 'database_manage_location_zone_change_status', 'uses' => 'ZoneController@changeStatus']);
                });
            });

            //Command
            Route::group(['prefix' => 'tf4-product-list', 'namespace' => 'App\Http\Controllers'], function () {
                Route::get('/', ['as' => "new_admin_setting_command_list", 'uses' => 'HomeController@oldProductList']);
                Route::post('/command-fire', ['as' => "new_admin_setting_command_fire", 'uses' => 'HomeController@commandFire']);
                Route::get('/general-command', ['as' => "new_admin_setting_general_command_list", 'uses' => 'HomeController@generalCommand']);
                Route::post('/general-command-fire', ['as' => "new_admin_setting_general_command_fire", 'uses' => 'HomeController@generalCommandFire']);
                Route::post('/general-command-upload', ['as' => "upload_agent_file", 'uses' => 'HomeController@uploadAgentFile']);

            });

            Route::group(['prefix' => 'operator-list', 'namespace' => 'App\Http\Controllers'], function () {
                Route::get('/', ['as' => "operator_product_command_list", 'uses' => 'HomeController@operatorList']);
                Route::post('/command-fire', ['as' => "operator_product_command_fire", 'uses' => 'HomeController@operatorCommandFire']);
            });
        });

        Route::group(['prefix' => 'api-documentation', 'namespace' => 'App\Http\Controllers'], function () {
            Route::get('/', ['as' => "api_documentation", 'uses' => 'ApiDocumentationController@index']);
            Route::get('add', ['as' => "api_documentation_add", 'uses' => 'ApiDocumentationController@create']);
            Route::get('edit/{id}', ['as' => "api_documentation_edit", 'uses' => 'ApiDocumentationController@edit']);
            Route::post('insert-documentation', ['as' => "api_documentation_insert", 'uses' => 'ApiDocumentationController@store']);
            Route::get('delete-documentation/{id}', ['as' => "api_documentation_delete", 'uses' => 'ApiDocumentationController@delete']);
        });

        Route::group(['prefix' => 'message-system', 'namespace' => 'App\Http\Controllers\Messages'], function () {
            Route::get('/', ['as' => 'message_monitor_list', 'uses' => 'MessageMonitorController@messageList']);
            Route::get('/view-message/{message_id}', ['as' => 'message_monitor_read', 'uses' => 'MessageMonitorController@messageRead']);
            Route::get('/msg_block/{message_id}', ['as' => 'message-block', 'uses' => 'MessageMonitorController@messageBlock']);
            Route::get('/msg_approve/{message_id}', ['as' => 'message-approve', 'uses' => 'MessageMonitorController@messageApprove']);
            Route::post('/msg_update', ['as' => 'message-update', 'uses' => 'MessageMonitorController@messageUpdate']);
            Route::get('/message/edit/{message_id}', ['as' => 'message_monitor_edit', 'uses' => 'MessageMonitorController@messageEdit']);
            Route::post('/message/edit/', ['as' => 'message_monitor_revice_view', 'uses' => 'MessageMonitorController@messageReviceView']);
            Route::post('/revice/message', ['as' => 'revice-Message-conversation', 'uses' => 'MessageMonitorController@reviseNewConversation']);
            Route::post('/revice_message', ['as' => 'revice-Message', 'uses' => 'MessageMonitorController@reviceMessageSave']);
        });

        Route::group(['prefix' => 'reports', 'namespace' => 'Modules\Reports\Http\Controllers\Admin'], function () {
            Route::get('/sales-reports/agent-sales-report', ['as' => 'admin_agent_sales_report', 'uses' => 'SalesController@index']);
            Route::post('/export-agent-sales-report', ['as' => 'export_agent_sales_report', 'uses' => 'SalesController@exportAgentSalesReport']);
            Route::get('/sales-reports/total-sales-report/{mode_to?}', ['as' => 'admin_default_sales_report', 'uses' => 'SalesReportController@index']);
            Route::get('/agent-login-report', ['as' => 'agent_login_report', 'uses' => 'AgentLoginController@loginReport']);
        });

        Route::group(['prefix' => 'api-management', 'namespace' => 'App\Http\Controllers'], function () {
            Route::get('/api-key-list', ['as' => 'api_key_list', 'uses' => 'AdminApiKeyController@apiKeyList']);
            Route::get('/admin-api-log-list', ['as' => 'admin_api_log_list', 'uses' => 'AdminApiKeyController@adminApiLogList']);
            Route::delete('/admin-api-log-list-delete-all', ['as' => 'admin_api_log_list_delete_all', 'uses' => 'AdminApiKeyController@deleteAll']);
            Route::get('/admin-api-log-detail/{id}', ['as' => 'admin_api_log_detail', 'uses' => 'AdminApiKeyController@getAdminApiLogDetail']);

            Route::get('/provider-api-log-list', ['as' => 'provider_api_log_list', 'uses' => '\App\Http\Controllers\OperatorRepo\ProviderApiKeyController@providerApiLogList']);
            Route::delete('/provider-api-log-list-delete-all', ['as' => 'provider_api_log_list_delete_all', 'uses' => '\App\Http\Controllers\OperatorRepo\ProviderApiKeyController@deleteAll']);
            Route::get('/provider-api-log-detail/{id}', ['as' => 'provider_api_log_detail', 'uses' => '\App\Http\Controllers\OperatorRepo\ProviderApiKeyController@getProviderApiLogDetail']);

            Route::get('/agent-api-log-list', ['as' => 'agent_api_log_list', 'uses' => '\Modules\Agent\Http\Controllers\AgentApiKeyController@agentApiLogList']);
            Route::delete('/agent-api-log-list-delete-all', ['as' => 'agent_api_log_list_delete_all', 'uses' => '\Modules\Agent\Http\Controllers\AgentApiKeyController@deleteAgentApiLogAll']);
            Route::get('/agent-order-api-log-list', ['as' => 'agent_order_api_log_list', 'uses' => '\Modules\Agent\Http\Controllers\AgentApiKeyController@agentOrderApiLogList']);
            Route::delete('/agent-order-api-log-list-delete-all', ['as' => 'agent_order_api_log_list_delete_all', 'uses' => '\Modules\Agent\Http\Controllers\AgentApiKeyController@deleteAgentOrderApiLogListAll']);
            Route::get('/agent-api-log-detail/{id}', ['as' => 'agent_api_log_detail', 'uses' => '\Modules\Agent\Http\Controllers\AgentApiKeyController@getAgentApiLogDetail']);
            Route::get('/agent-order-api-log-detail/{id}', ['as' => 'agent_order_api_log_detail', 'uses' => '\Modules\Agent\Http\Controllers\AgentApiKeyController@getAgentOrderApiLogDetail']);
            Route::get('find-all-agent-company-name', ['as' => 'find_all_agent_company_name', 'uses' => '\Modules\Agent\Http\Controllers\AgentApiKeyController@agentCompanyNameMatchAll']);

            Route::get('/third-party-api-log-list', ['as' => 'third_party_api_log_list', 'uses' => 'ThirdPartyApiLogController@getThirdPartyApiLogList']);
            Route::delete('/third-party-api-log-list-delete-all', ['as' => 'third_party_api_log_list_delete_all', 'uses' => 'ThirdPartyApiLogController@deleteThirdPartyApiLogAll']);
            Route::get('/third-party-api-log-detail/{id}', ['as' => 'third_party_api_log_detail', 'uses' => 'ThirdPartyApiLogController@getThirdPartyApiLogDetail']);
        });

        Route::group(['prefix' => 'reports', 'namespace' => 'Modules\Reports\Http\Controllers\Admin'], function () {
            Route::get('/sales-reports/agent-sales-report', ['as' => 'admin_agent_sales_report', 'uses' => 'SalesController@index']);
            Route::get('/sales-reports/total-sales-report/{mode_to?}', ['as' => 'admin_default_sales_report', 'uses' => 'SalesReportController@index']);
        });

        /* Api Product Route  - Nayan Makwana*/
        Route::group(['prefix' => 'api-management', 'namespace' => 'Modules\ApiManagement\Http\Controllers'], function () {

            Route::get('/api-provider-list', ['as' => 'api_providers_list', 'uses' => 'ApiManagementController@index']);
            Route::get('/api-product/create', ['as' => 'api_providers_create', 'uses' => 'ApiManagementController@create']);
            Route::get('find-provider-all', ['as' => 'search_api_provider', 'uses' => 'ApiManagementController@operatorSearch']);
            Route::get('{provider_id}/find-provider-product', ['as' => 'get_provider_api_info', 'uses' => 'ApiManagementController@getProviderProduct']);
            Route::post('{provider_id}/find-provider-product-by-code', ['as' => 'get_provider_product_by_code', 'uses' => 'ApiManagementController@getProviderProduct']);
            Route::post('/api-product/save', ['as' => 'api_providers_save', 'uses' => 'ApiManagementController@saveApiProviderProduct']);
            Route::post('/api-product/review', ['as' => 'api_product_review', 'uses' => 'ApiManagementController@reviewProduct']);
            Route::get('/{provider_id}/product/{product_code}/detail/{language_id}', ['as' => 'api_product_detail_get', 'uses' => 'ApiManagementController@getProductDetail']);
            Route::post('/product/save', ['as' => 'product_save', 'uses' => 'ApiManagementController@saveProduct']);
            Route::get('/api-product/{id}/decline', ['as' => 'api_product_decline', 'uses' => 'ApiManagementController@declineProduct']);
            Route::get('/api-product/{id}/resume', ['as' => 'api_product_resume', 'uses' => 'ApiManagementController@resumeProduct']);
            Route::get('/{provider_id}/api-product-edit', ['as' => 'api_providers_edit', 'uses' => 'ApiManagementController@edit']);
            Route::get('/update-provider-api', ['as' => 'update_provider_api_data', 'uses' => 'ApiManagementController@providerUpdate']);
            Route::get('/{id}/edit', ['as' => 'api_providers_get', 'uses' => 'ApiManagementController@get']);
            Route::get('/{id}/add-product', ['as' => 'api_providers_get', 'uses' => 'ApiManagementController@addProduct']);
            Route::get('/get-product-info/{id}/info', ['as' => 'get_product_info', 'uses' => 'ApiManagementController@getProductInformation']);
            Route::get('/run-compare-api-product-command/{id?}', function ($id = 0){
                \Artisan::call('compareapiproduct', ['--productid' => $id]);
                print_r(Artisan::output());
            });
        });

        /* Api Product Route  - Nayan Makwana*/
        Route::group(['prefix' => 'migrate-api-products', 'namespace' => 'Modules\Migrate\Http\Controllers'], function () {
            Route::get('/', ['as' => 'migrate_api_products', 'uses' => 'MigrateController@index']);
            Route::post('/', ['as' => 'migrate_api_products_update', 'uses' => 'MigrateController@update']);
            Route::get('/get-api-products/{api_source}', ['as' => 'get_migrate_product_list', 'uses' => 'MigrateController@getProductList']);
        });

        Route::group(['middleware' => ['web'], 'prefix' => 'customer-management', 'namespace' => 'Modules\Customer\Http\Controllers'], function () {
            Route::get('/customer-listing', ['as' => 'customers_list', 'uses' => 'CustomerController@getList']);
            Route::get('/frequent-contact-listing', ['as' => 'frequent_customers_list', 'uses' => 'AgentFrequentContactController@getList']);
            Route::get('/customer-detail', ['as' => 'frequent_customer_detail', 'uses' => 'AgentFrequentContactController@getCustomerDetail']);
        });

        Route::post('order-management/get-price/{id}', 'Modules\ProductEditor\Http\Controllers\Product\ProductController@getPrice');

        //[Order Management]
        Route::group(['middleware' => ['web'], 'prefix' => 'order-management', 'namespace' => 'Modules\Order\Http\Controllers'], function () {

            Route::get('{order_id}/invoice', ['as' => 'admin_order_invoice', 'uses' => 'AdminOrderController@getInvoice']);
            Route::get('{order_id}/send-invoice-pdf', ['as' => 'admin_order_send_invoice_pdf', 'uses' => 'AdminOrderController@pdfview']);
            Route::post('/export-order', ['as' => 'export_all_order', 'uses' => 'AdminOrderController@exportOrder']);
            Route::post('/export-operator-store-order', ['as' => 'export_operator_store_all_order', 'uses' => 'OperatorStoreOrderController@exportOperatorStoreOrder']);
            Route::get('/order-listing', ['as' => 'admin_order_list', 'uses' => 'AdminOrderController@index']);
            Route::get('/operator-store-orders', ['as' => 'operator_store_order_list', 'uses' => 'OperatorStoreOrderController@index']);
               
            Route::group(['middleware' => 'group.permission:order-management'], function () {
                Route::get('/create-order', ['as' => 'admin_order_create', 'uses' => 'AdminOrderController@create']);
                Route::get('/create-custom-order', ['as' => 'admin_order_create', 'uses' => 'AdminOrderController@customOrderCreate']);
            });
            Route::post('/save', ['as' => 'admin_order_save', 'uses' => 'AdminOrderController@save']);
            Route::get('/order-edit/{id}', ['as' => 'admin_order_edit', 'uses' => 'AdminOrderController@get']);
            


            //Api Order Management
            //Route::get('/{order_product_id}/create-api-order', ['as' => 'api_order_create', 'uses' => 'AdminOrderController@apiOrderCreate']);
            Route::get('/{booking_id}/cancel-api-order', ['as' => 'api_order_cancel', 'uses' => 'AdminOrderController@apiOrderCancel']);

            /**
             * Custom order route start
             * Pravin S <iipl.pravins@gmail.com>
             */
            
            Route::get('/custom-order-listing', ['as' => 'admin_custom_order_list', 'uses' => 'AdminOrderController@customOrderList']);
            Route::group(['prefix' => 'custom', 'namespace' => 'OrderManagement'], function () {
                Route::get('/', ['as' => 'v1_admin_custom_order_base', 'uses' => 'Custom\HomeController@index']);
                Route::get('/create', ['as' => 'v1_admin_custom_order_create', 'uses' => 'Custom\HomeController@create']);
                Route::post('/create', ['as' => 'v1_admin_custom_order_create', 'uses' => 'Custom\HomeController@createOrder']);
                Route::get('/products', ['as' => 'v1_admin_custom_product_list', 'uses' => 'Custom\HomeController@productList']);
                Route::post('/export-order', ['as' => 'v1_admin_custom_export_order', 'uses' => 'Custom\HomeController@exportOrder']);
            });
            /**
             * Order New Routes Start
             * Pravin S <iipl.pravins@gmail.com>
             */
            Route::group(['middleware' => ['web'], 'prefix' => '/v1/order-edit/{id}', 'namespace' => 'OrderManagement'], function () {
                Route::get('/', ['as' => 'v1_admin_order_edit_order_view', 'uses' => 'AdminOrderController@detail']);
                Route::get('/all-section', ['as' => 'v1_admin_order_edit_quick_summery', 'uses' => 'AdminOrderController@allSection']);

                Route::get('/send-eticket/{productId}', ['as' => 'admin_order_product_send_eticket', 'uses' => 'AdminOrderController@sendEticket']);
                Route::post('/send-eticket-manually', ['as' => 'send_mail_eticket', 'uses' => 'AdminOrderController@sendMailEticket']);

                Route::get('/quick-summery', ['as' => 'v1_admin_order_edit_quick_summery', 'uses' => 'AdminOrderController@quickSummery']);
                Route::get('/booking-process', ['as' => 'v1_admin_order_edit_booking_process', 'uses' => 'AdminOrderController@bookingProcess']);
                Route::get('/reservation-information', ['as' => 'v1_admin_order_edit_reservation_information', 'uses' => 'AdminOrderController@reservationInformation']);
                Route::get('/customer-pickup', ['as' => 'v1_admin_order_edit_customer_pickup', 'uses' => 'AdminOrderController@customerPickUp']);
                Route::get('/price-update-history', ['as' => 'v1_admin_order_edit_price_update_history', 'uses' => 'AdminOrderController@priceUpdateHistory']);
                Route::get('/operator-records', ['as' => 'v1_admin_order_edit_operator_records', 'uses' => 'AdminOrderController@operatorRecords']);
                Route::get('/order-status-log', ['as' => 'v1_admin_order_edit_status_log', 'uses' => 'AdminOrderController@orderStatusLog']);
                Route::get('/internal-status-history', ['as' => 'v1_admin_order_edit_internal_status_hist', 'uses' => 'AdminOrderController@internalStatusHistory']);
                Route::get('/agent-commision', ['as' => 'v1_admin_order_edit_agent_commision', 'uses' => 'AdminOrderController@agentCommision']);
                Route::get('/payment-records', ['as' => 'v1_admin_order_edit_payment_records', 'uses' => 'AdminOrderController@paymentRecords']);
                Route::get('/adjustment-records', ['as' => 'v1_admin_order_edit_adjustment_records', 'uses' => 'AdminOrderController@adjustmentRecords']);
                Route::get('/product-status-adjustment', ['as' => 'v1_admin_order_edit_product_status_adjustment', 'uses' => 'AdminOrderController@productStatusAdjustment']);
                Route::get('/product-status-change/{order_product_id}', ['as' => 'v1_admin_order_edit_product_status_adjustment_a', 'uses' => 'AdminOrderController@productStatusAdjustmentChange']);
                Route::post('/product-status', ['as' => 'v1_admin_order_edit_update_product_status', 'uses' => 'AdminOrderController@updateProductStatus']);
                Route::get('/payment-receipt-detail', ['as' => 'v1_admin_order_edit_payment_receipt_detail', 'uses' => 'AdminOrderController@paymentReceiptDetail']);
                Route::post('/create-payment-receipt', ['as' => 'v1_admin_order_edit_create_payment_receipt', 'uses' => 'AdminOrderController@createPaymentReceipt']);
                Route::post('/update-internal-status', ['as' => 'v1_admin_order_edit_update_internal_status', 'uses' => 'AdminOrderController@updateInternalStatus']);
                Route::get('/internal-status-group', ['as' => 'v1_admin_order_edit_internal_status_group', 'uses' => 'AdminOrderController@getInternalStatusGroup']);
                Route::get('/internal-status/{group_id}', ['as' => 'v1_admin_order_edit_get_order_status', 'uses' => 'AdminOrderController@getInternalStatus']);
                Route::get('/internal-status-template/{status_id}', ['as' => 'v1_admin_order_edit_order_status_template', 'uses' => 'AdminOrderController@getInternalStatusTemplate']);
                Route::get('/agent-detail', ['as' => 'v1_admin_order_edit_agent_detail', 'uses' => 'AdminOrderController@getAgentDetail']);
                Route::get('/cost-retail-adjustment', ['as' => 'v1_admin_order_edit_get_adjustment_record', 'uses' => 'AdminOrderController@costRetailAdjustmentDetail']);
                Route::post('/{orderProductId}/create-adjustment-record', ['as' => 'v1_admin_order_edit_create_adjustment_record', 'uses' => 'AdminOrderController@updateCostRetailAdjustment']);
                Route::get('/product/{productId}/booking-option', ['as' => 'v1_admin_order_product_booking_option', 'uses' => 'AdminOrderController@getBookingOption']);
                Route::get('/product/{productId}/get-departure-location', ['as' => 'v1_admin_order_get_departure_location', 'uses' => 'AdminOrderController@getDepartureLocation']);
                Route::post('/product/{productId}/api-booking-option', ['as' => 'v1_admin_order_product_api_booking_option', 'uses' => 'AdminOrderController@getApiBookingOption']);
                Route::post('/product/{productId}/fareharbor-booking-option', ['as' => 'fareharbor_booking_option', 'uses' => 'AdminOrderController@getOptionDetail']);
                Route::get('/product/{productId}/availability', ['as' => 'v1_admin_order_product_availability', 'uses' => 'AdminOrderController@getAvailability']);

                Route::post('/product-price-calculate', ['as' => 'v1_admin_order_price_calculate_product_wise', 'uses' => 'AdminOrderController@calculateByProductWise']);
                Route::post('/create-order-product', ['as' => 'v1_admin_order_price_calculate_product_wise', 'uses' => 'AdminOrderController@createOrderProduct']);
                Route::get('/suggested-tour-itinerary', ['as' => 'v1_admin_order_edit_suggested_tour_detail', 'uses' => 'AdminOrderController@suggestedTourItinerary']);
                Route::post('/getagentcredits', ['as' => 'v1_admin_order_getagentcredits', 'uses' => 'AdminOrderController@getagentcredits']);

                //Api Order Management
                Route::get('/create-api-order/{orderProductId}', ['as' => 'api_order_create', 'uses' => 'AdminOrderController@apiOrderCreate']);
                Route::get('/cancel-api-order/{bookingId}', ['as' => 'api_order_cancel', 'uses' => 'AdminOrderController@apiOrderCancel']);

                Route::group(['prefix' => '/op/{orderProductId}'], function () {
                    Route::get('/reservation', ['as' => 'v1_admin_order_edit_product_reservation', 'uses' => 'AdminOrderController@getBookingInformation']);
                    Route::post('/calculate', ['as' => 'v1_admin_order_edit_product_calculation', 'uses' => 'AdminOrderController@priceCalculate']);
                    Route::post('/price-adjust', ['as' => 'v1_admin_order_edit_price_adjust', 'uses' => 'AdminOrderController@priceAdjust']);
                    Route::post('/flight-update', ['as' => 'v1_admin_order_edit_flight_update', 'uses' => 'AdminOrderController@flightUpdate']);
                    Route::post('/save-product', ['as' => 'v1_admin_order_edit_save_product', 'uses' => 'AdminOrderController@saveProduct']);
                    Route::post('/suggested-tour-update', ['as' => 'v1_admin_order_edit_suggested_tour_update', 'uses' => 'AdminOrderController@updateSuggestedTourItinerary']);
                    Route::get('/fax', ['as' => 'v1_admin_order_edit_product_fax', 'uses' => 'AdminOrderController@fax']);
                    Route::get('/getFaxInfo', ['as' => 'v1_admin_order_edit_product_fax', 'uses' => 'AdminOrderController@getFaxInfo']);
                    Route::post('/sendFax', ['as' => 'v1_admin_order_edit_product_fax', 'uses' => 'AdminOrderController@sendFax']);
                    Route::post('/updateOrderSpecialNote', ['as' => 'v1_admin_order_edit_product_fax', 'uses' => 'AdminOrderController@updateOrderSpecialNote']);
                    Route::get('/getOrderLog', ['as' => 'v1_admin_order_edit_product_log', 'uses' => 'AdminOrderController@getOrderLog']);
                    Route::group(['prefix' => '/ticketStock'], function () {
                        Route::get('/getVoucher', ['as' => 'v1_admin_order_edit_product_eCertificate', 'uses' => 'AdminOrderController@getVoucher']);
                        Route::get('/download/{link}', ['as' => 'v1_admin_order_edit_product_voucher_download', 'uses' => 'AdminOrderController@downloadVoucher']);
                    });
                });

                // Update blinking option
                Route::get('/change-need-attention/{orderId}', ['as' => 'change_need_attention', 'uses' => 'AdminOrderController@changeNeedAttention']);

                Route::post('charge', 'AdminOrderController@doCharge'); //docharge接口
                Route::post('void', 'AdminOrderController@doCharge'); //void接口
                Route::get('/updateOrderNotifyStatus/{statusHistoryId}', ['as' => 'v1_admin_order_edit_product_update_notify_status', 'uses' => 'AdminOrderController@updateOrderNotifyStatus']);

            });
          // operator order edit store
        Route::group(['middleware' => ['web'], 'prefix' => '/v1/order-edit-store/{id}',], function () {
            Route::get('/', ['as' => 'v1_operator_store_order_edit_order_view', 'uses' => 'OperatorStoreOrderController@detail']);
            Route::get('/all-section', ['as' => 'v1_operator_store_order_edit_quick_summery', 'uses' => 'OrderManagement\AdminOrderController@allSection']);
             Route::get('/send-eticket/{productId}', ['as' => 'operator_store_order_product_send_eticket', 'uses' => 'OrderManagement\AdminOrderController@sendEticketOperatorAdmin']);
             Route::post('/send-eticket-manually', ['as' => 'send_mail_eticket', 'uses' => 'OrderManagement\AdminOrderController@sendMailEticket']);
            Route::get('/cost-retail-adjustment', ['as' => 'v1_operator_store_order_edit_get_adjustment_record', 'uses' => 'OrderManagement\AdminOrderController@costRetailAdjustmentDetail']);
            Route::get('/quick-summery', ['as' => 'v1_operator_store_order_edit_quick_summery', 'uses' => 'AdminOrderController@quickSummery']);
            Route::get('/booking-process', ['as' => 'v1_operator_store_order_edit_booking_process', 'uses' => 'AdminOrderController@bookingProcess']);
            Route::get('/reservation-information', ['as' => '_operator_store_order_edit_reservation_information', 'uses' => 'AdminOrderController@reservationInformation']);
            Route::get('/customer-pickup', ['as' => 'v1_operator_store_order_edit_customer_pickup', 'uses' => 'AdminOrderController@customerPickUp']);
            Route::get('/price-update-history', ['as' => 'v1_operator_store_order_edit_price_update_history', 'uses' => 'AdminOrderController@priceUpdateHistory']);
            Route::get('/operator-records', ['as' => 'v1_operator_store_order_edit_operator_records', 'uses' => 'AdminOrderController@operatorRecords']);
            Route::get('/order-status-log', ['as' => 'v1_operator_store_order_edit_status_log', 'uses' => 'AdminOrderController@orderStatusLog']);
            Route::get('/payment-records', ['as' => 'v1_operator_store_order_edit_payment_records', 'uses' => 'AdminOrderController@paymentRecords']);
           Route::get('/adjustment-records', ['as' => 'v1_operator_store_order_edit_adjustment_records', 'uses' => 'AdminOrderController@adjustmentRecords']);
           Route::get('/product-status-adjustment', ['as' => ' v1_operator-_store_order_edit_product_status_adjustment', 'uses' => 'AdminOrderController@productStatusAdjustment']);
            Route::get('/payment-receipt-detail', ['as' => 'v1_operator_store_order_edit_payment_receipt_detail', 'uses' => 'OrderManagement\AdminOrderController@paymentReceiptDetail']);
            Route::get('/product-status-change/{order_product_id}', ['as' => 'v1_admin_order_edit_product_status_adjustment_a', 'uses' => 'OrderManagement\AdminOrderController@productStatusAdjustmentChange']);
          Route::get('/product_id/{productId}/availability', ['as' => 'v1_operator_store_order_product_availability', 'uses' => 'OrderManagement\AdminOrderController@getAvailabilityOperatorAdmin']);
          Route::get('/product_id/{productId}/booking-option', ['as' => 'v1_admin_order_product_booking_option', 'uses' => 'OrderManagement\AdminOrderController@getBookingOptionOperatorAdmin']);
          Route::get('/product/{productId}/booking-option', ['as' => 'v1_admin_order_product_booking_option', 'uses' => 'OrderManagement\AdminOrderController@getBookingOptionOperatorAdmin']);
          Route::get('/product/{productId}/get-departure-location', ['as' => 'v1_admin_order_get_departure_location', 'uses' => 'OrderManagement\AdminOrderController@getDepartureLocationOperatorAdmin']);

          Route::post('/create-order-product', ['as' => 'v1_admin_order_price_calculate_product_wise', 'uses' => 'OrderManagement\AdminOrderController@createOrderProduct']);
          Route::post('/product-price-calculate', ['as' => 'v1_admin_order_price_calculate_product_wise', 'uses' => 'OrderManagement\AdminOrderController@calculateByProductWise']);
          Route::post('/{orderProductId}/create-adjustment-record', ['as' => 'v1_operatpr_store_order_edit_create_adjustment_record', 'uses' => 'OrderManagement\AdminOrderController@updateCostRetailAdjustment']);
          Route::post('/create-payment-receipt', ['as' => 'v1_operator_store_order_edit_create_payment_receipt', 'uses' => 'OrderManagement\AdminOrderController@createPaymentReceipt']);


            Route::group(['prefix' => '/op/{orderProductId}','namespace' => 'OrderManagement'], function () {
                    Route::get('/reservation', ['as' => 'v1_admin_order_edit_product_reservation', 'uses' => 'AdminOrderController@getBookingInformation']);
                    Route::post('/calculate', ['as' => 'v1_admin_order_edit_product_calculation', 'uses' => 'AdminOrderController@priceCalculate']);
                    Route::post('/price-adjust', ['as' => 'v1_admin_order_edit_price_adjust', 'uses' => 'AdminOrderController@priceAdjust']);
                    Route::post('/flight-update', ['as' => 'v1_admin_order_edit_flight_update', 'uses' => 'AdminOrderController@flightUpdate']);
                    Route::post('/save-product', ['as' => 'v1_admin_order_edit_save_product', 'uses' => 'AdminOrderController@saveProduct']);
                    Route::post('/suggested-tour-update', ['as' => 'v1_admin_order_edit_suggested_tour_update', 'uses' => 'AdminOrderController@updateSuggestedTourItinerary']);
                    Route::group(['prefix' => '/ticketStock'], function () {
                        Route::get('/getVoucher', ['as' => 'v1_admin_order_edit_product_eCertificate', 'uses' => 'AdminOrderController@getVoucher']);
                        Route::get('/download/{link}', ['as' => 'v1_admin_order_edit_product_voucher_download', 'uses' => 'AdminOrderController@downloadVoucher']);
                    });
                });



            });
        //end operator store edit-order
             Route::group(['middleware' => ['web'], 'prefix' => '/v1/custom-order-edit/{id}', 'namespace' => 'OrderManagement'], function () {

                Route::get('/', ['as' => 'v1_admin_custom_order_edit_order_view', 'uses' => 'Custom\DetailController@detail']);
                Route::get('/all-section', ['as' => 'v1_admin_custom_order_edit_quick_summery', 'uses' => 'Custom\DetailController@allSection']);
                Route::get('/agent-detail', ['as' => 'v1_admin_custom_order_edit_agent_detail', 'uses' => 'Custom\DetailController@getAgentDetail']);
                Route::get('/send-eticket/{orderProductId}', ['as' => 'admin_custom_order_product_send_eticket', 'uses' => 'Custom\DetailController@sendEticket']);
                Route::post('/send-eticket-manually', ['as' => 'admin_custom_order_send_mail_eticket', 'uses' => 'Custom\DetailController@sendMailEticket']);

                Route::get('/quick-summery', ['as' => 'v1_admin_custom_order_edit_quick_summery', 'uses' => 'Custom\DetailController@quickSummery']);
                Route::get('/reservation-information', ['as' => 'v1_admin_custom_order_edit_reservation_information', 'uses' => 'Custom\DetailController@reservationInformation']);
                Route::get('/customer-pickup', ['as' => 'v1_admin_custom_order_edit_customer_pickup', 'uses' => 'Custom\DetailController@customerPickUp']);
                Route::get('/price-update-history', ['as' => 'v1_admin_custom_order_edit_price_update_history', 'uses' => 'Custom\DetailController@priceUpdateHistory']);
                Route::get('/operator-records', ['as' => 'v1_admin_custom_order_edit_operator_records', 'uses' => 'Custom\DetailController@operatorRecords']);
                Route::get('/order-status-log', ['as' => 'v1_admin_custom_order_edit_status_log', 'uses' => 'Custom\DetailController@orderStatusLog']);
                Route::get('/internal-status-history', ['as' => 'v1_admin_custom_order_edit_internal_status_hist', 'uses' => 'Custom\DetailController@internalStatusHistory']);
                Route::get('/agent-commision', ['as' => 'v1_admin_custom_order_edit_agent_commision', 'uses' => 'Custom\DetailController@agentCommision']);
                Route::get('/payment-records', ['as' => 'v1_admin_custom_order_edit_payment_records', 'uses' => 'Custom\DetailController@paymentRecords']);
                Route::get('/adjustment-records', ['as' => 'v1_admin_custom_order_edit_adjustment_records', 'uses' => 'Custom\DetailController@adjustmentRecords']);
                Route::get('/product-status-adjustment', ['as' => 'v1_admin_custom_order_edit_product_status_adjustment', 'uses' => 'Custom\DetailController@productStatusAdjustment']);
                Route::get('/product-status-change/{order_product_id}', ['as' => 'v1_admin_custom_order_edit_product_status_adjustment_a', 'uses' => 'Custom\DetailController@productStatusAdjustmentChange']);
                Route::post('/product-status', ['as' => 'v1_admin_custom_order_edit_update_product_status', 'uses' => 'Custom\DetailController@updateProductStatus']);
                Route::get('/payment-receipt-detail', ['as' => 'v1_admin_custom_order_edit_payment_receipt_detail', 'uses' => 'Custom\DetailController@paymentReceiptDetail']);
                Route::post('/create-payment-receipt', ['as' => 'v1_admin_custom_order_edit_create_payment_receipt', 'uses' => 'Custom\DetailController@createPaymentReceipt']);
                Route::post('/update-internal-status', ['as' => 'v1_admin_custom_order_edit_update_internal_status', 'uses' => 'Custom\DetailController@updateInternalStatus']);
                Route::get('/internal-status-group', ['as' => 'v1_admin_custom_order_edit_internal_status_group', 'uses' => 'Custom\DetailController@getInternalStatusGroup']);
                Route::get('/internal-status/{group_id}', ['as' => 'v1_admin_custom_order_edit_get_order_status', 'uses' => 'Custom\DetailController@getInternalStatus']);
                Route::get('/internal-status-template/{status_id}', ['as' => 'v1_admin_custom_order_edit_order_status_template', 'uses' => 'Custom\DetailController@getInternalStatusTemplate']);
                Route::get('/agent-detail', ['as' => 'v1_admin_custom_order_edit_agent_detail', 'uses' => 'Custom\DetailController@getAgentDetail']);
                Route::get('/cost-retail-adjustment', ['as' => 'v1_admin_custom_order_edit_get_adjustment_record', 'uses' => 'Custom\DetailController@costRetailAdjustmentDetail']);
                Route::post('/{orderProductId}/create-adjustment-record', ['as' => 'v1_admin_custom_order_edit_create_adjustment_record', 'uses' => 'Custom\DetailController@updateCostRetailAdjustment']);
                Route::get('/product/{productId}/booking-option', ['as' => 'v1_admin_custom_order_product_booking_option', 'uses' => 'Custom\DetailController@getBookingOption']);
                Route::get('/product/{productId}/availability', ['as' => 'v1_admin_custom_order_product_availability', 'uses' => 'Custom\DetailController@getAvailability']);

                Route::post('/product-price-calculate', ['as' => 'v1_admin_custom_order_price_calculate_product_wise', 'uses' => 'Custom\DetailController@calculateByProductWise']);
                Route::post('/create-order-product', ['as' => 'v1_admin_custom_order_price_calculate_product_wise', 'uses' => 'Custom\DetailController@createOrderProduct']);
                Route::get('/suggested-tour-itinerary', ['as' => 'v1_admin_custom_order_edit_suggested_tour_detail', 'uses' => 'Custom\DetailController@suggestedTourItinerary']);

                Route::group(['prefix' => '/op/{orderProductId}'], function () {
                    Route::get('/reservation', ['as' => 'v1_admin_custom_order_edit_product_reservation', 'uses' => 'Custom\DetailController@getBookingInformation']);
                    Route::post('/calculate', ['as' => 'v1_admin_custom_order_edit_product_calculation', 'uses' => 'Custom\DetailController@priceCalculate']);
                    Route::post('/price-adjust', ['as' => 'v1_admin_custom_order_edit_price_adjust', 'uses' => 'Custom\DetailController@priceAdjust']);
                    Route::post('/flight-update', ['as' => 'v1_admin_custom_order_edit_flight_update', 'uses' => 'Custom\DetailController@flightUpdate']);
                    Route::post('/save-product', ['as' => 'v1_admin_custom_order_edit_save_product', 'uses' => 'Custom\DetailController@saveProduct']);
                    Route::post('/suggested-tour-update', ['as' => 'v1_admin_custom_order_edit_suggested_tour_update', 'uses' => 'Custom\DetailController@updateSuggestedTourItinerary']);
                });


            });

            Route::post('/remove-product/{order_product_id}', ['as' => 'admin_order_product_delete', 'uses' => 'AdminOrderController@deleteOrderProduct']);
            Route::post('/fetch-product-details/{product_id}', ['as' => 'admin_fetch_product_details', 'uses' => 'AdminOrderController@fetchProductDeatils']);
            Route::post('/remove-participant/{order_product_guest_id}', ['as' => 'admin_order_product_guest_delete', 'uses' => 'AdminOrderController@deleteOrderProductGuest']);
            Route::post('/fetch-departure-dates/', ['as' => 'admin_fetch_departure_date', 'uses' => 'AdminOrderController@fetchDepartureDates']);
            Route::post('/fetch-tourcms-departure-dates/', ['as' => 'admin_fetch_tourcms_departure_date', 'uses' => 'AdminOrderController@fetchTourcmsDepartureDates']);
            Route::post('/get-option-details/{id}', 'AdminOrderController@getOptionDetail');
            Route::get('/get-upgrade/{upgrade_id}/option/{option_id}', ['as' => 'admin_get_upgrade_option', 'uses' => 'AdminOrderController@getUpgradeOptions']);
            Route::get('/find-customer', ['as' => 'admin_find_customer', 'uses' => 'AdminOrderController@findCustomer']);
            Route::post('/fetch-order-status/{order_status_group_id}', ['as' => 'admin_order_get_status', 'uses' => 'AdminOrderController@getOrderStatus']);
            Route::get('/fetch-order-status-html/{orderStatusId}', ['as' => 'admin_order_get_status_html', 'uses' => 'AdminOrderController@getOrderStatusHtml']);
            Route::post('/fetch-order-status-detail/{order_status_id}', ['as' => 'admin_order_get_status_detail', 'uses' => 'AdminOrderController@getOrderStatusDetail']);

            Route::get('/hotel-orders', ['as' => 'admin_hotel_order_list', 'uses' => 'AdminOrderController@hotelOrders']);
            Route::get('/flight-orders', ['as' => 'admin_flight_order_list', 'uses' => 'AdminOrderController@flightOrders']);

            //Store Order Product Detail
            Route::group(['prefix' => '{order_id}/product'], function () {
                Route::post('/{order_product_id}/update', ['as' => 'admin_order_product_update', 'uses' => 'AdminOrderController@updateOrderProduct']);
                Route::post('/{order_product_id}/update-new', ['as' => 'admin_order_product_update_new', 'uses' => 'AdminOrderController@updateOrderProductNew']);
            });
            //Update Order Subscriber
            Route::post('/{order_id}/subscriber/save', ['as' => 'admin_order_subscriber_save', 'uses' => 'AdminOrderController@updateOrderSubscriber']);
            Route::group(['prefix' => 'order-product'], function () {
                Route::get('/pay-detail/{orderProductId}', ['as' => 'admin_order_product_pay_detail', 'uses' => 'AdminOrderController@getPayDetail']);
            });
            //order status update
            Route::post('/{order_id}/status/update', ['as' => 'admin_order_status_update', 'uses' => 'AdminOrderController@updateOrderStatus']);
            //provider order product status update
            Route::post('/provider-status/update', ['as' => 'provider_admin_order_status_update', 'uses' => 'AdminOrderController@updateProviderOrderProductStatus']);
            //Settlement update
            Route::post('/{order_id}/settlement/save', ['as' => 'admin_settlement_save', 'uses' => 'AdminOrderController@updateSettlement']);

            //Order adjustment update
            Route::post('/{order_id}/product/{order_product_id}/adjustment/update', ['as' => 'admin_order_adjustment_save', 'uses' => 'AdminOrderController@updateAdjustment']);
            Route::post('/order-product/cost-retail-adjustment', ['as' => 'admin_order_product_cost_retail_adjustment', 'uses' => 'AdminOrderController@updateOrderCostAndRetailAdjustment']);
            Route::post('/get-attribute-price/{product_id}', ['as' => 'admin_get_attribute_with_price', 'uses' => 'AdminOrderController@changeAttributeWithPrice']);

            //Order Void Status update
            Route::post('/{order_id}/change-void-status', ['as' => 'admin_order_status_void_update', 'uses' => 'AdminOrderController@changeVoidStatus']);
            //Order Charge Capture Status Change
            Route::post('/{order_id}/change-charge-status', ['as' => 'admin_order_status_change_charge', 'uses' => 'AdminOrderController@changeChargeCaptureStatus']);
            Route::post('/order-product/{order_product_id}/price-update', ['as' => 'order_product_price_update', 'uses' => 'AdminOrderController@updateOrderProductPrice']);

            //Order Eticket Log generate
            Route::get('/product/{id}/eticket', ['as' => 'admin_order_product_eticket_generate', 'uses' => 'AdminOrderController@eticketGenerate']);
            //Order Eticket Log Save
            Route::post('/product/{id}/eticket-log/save', ['as' => 'admin_order_product_eticket_log_save', 'uses' => 'AdminOrderController@saveOrderEticketLog']);

            Route::group(['prefix' => 'order-status'], function () {
                Route::get('/', ['as' => 'order_status_list', 'uses' => 'OrderStatusController@index']);
                Route::get('/create', ['as' => 'order_status_create', 'uses' => 'OrderStatusController@create']);
                Route::get('/edit/{id}', ['as' => 'order_status_edit', 'uses' => 'OrderStatusController@get']);
                Route::post('/save', ['as' => 'order_status_save', 'uses' => 'OrderStatusController@save']);
                Route::post('/change-status/', ['as' => 'change_order_status', 'uses' => 'OrderStatusController@changeStatus']);
                Route::get('/{id}/getdescription/{lang}', ['as' => 'get-description', 'uses' => 'OrderStatusController@getDescriptionJson']);
                Route::get('/detail/{id}/{lang}', ['as' => 'get_detail', 'uses' => 'OrderStatusController@getDetailJson']);
            });

            ///This is for Manage Order Product status
            Route::any('/order-product-status', ['as' => 'order_product_status_list', 'uses' => 'ProviderOrderProductStatusController@index']);
            Route::get('/order-product-status/{id}/get', ['as' => 'order_product_status_get', 'uses' => 'ProviderOrderProductStatusController@get']);
            Route::post('/order-product-status/save', ['as' => 'provider_order_product_status_save', 'uses' => 'ProviderOrderProductStatusController@update']);
            Route::post('/order-product-status/{id}/changeStatus', ['as' => 'order_product_change_status', 'uses' => 'ProviderOrderProductStatusController@changeStatus']);
            Route::get('/order-product-status/{id}/delete', ['as' => 'order_product_status_delete', 'uses' => 'ProviderOrderProductStatusController@delete']);

        });


        Route::group(['prefix' => 'agent-management', 'namespace' => 'Modules\Agent\Http\Controllers'], function () {

            Route::group(['prefix' => '/agent-custom-white-label-site'], function () {
                Route::post('/', ['as' => 'agent_custom_white_label_site' ,'uses' => 'AgentCustomDomainController@index']);
                Route::get('/{id}/edit', ['as' => 'edit_agent_custom_white_label_site' ,'uses' => 'AgentCustomDomainController@edit']);
                Route::post('/{id}/update', ['as' => 'update_agent_custom_white_label_site' ,'uses' => 'AgentCustomDomainController@update']);
            });

            Route::group(['prefix' => '/agent-listing'], function () {
                Route::get('/', ['as' => 'agents_list' ,'uses' => 'AgentController@index']);
                Route::post('/export', ['as' => 'agent_management_agent_listing_export', 'uses' => 'AgentController@exportCSV']);
            });

            // Route for Business Manager

            //Route::get('business_manager_list','BusinessManagerController@index');
             Route::group(['prefix' => '/business-manager'], function () {
               //dd(123);
               Route::get('/', ['as' => 'business_manager_list' ,'uses' => 'BusinessManagerController@index']);
               // Route::post('/export', ['as' => 'agent_management_agent_listing_export', 'uses' => 'AgentController@exportCSV']);
               Route::post('/store',['as' => 'business_manager_store', 'uses' =>'BusinessManagerController@store']);              
               Route::get('/region-business-manager', ['as' => 'business_manager_region_list' ,'uses' => 'BusinessManagerController@region']);
            });

            //Route::get('/deposit-listing',['as' => 'deposit_list' ,'uses' => 'DepositController@index']);
            //Route::get('/add-deposit', ['as' => 'add_deposit_and_credit', 'uses' => 'DepositController@create']);

            Route::group(['prefix' => '{id}/edit'], function () {
                Route::any('profile', ['as' => 'admin_edit_agent_profile', 'uses' => 'ProfileController@get']);
            });
            Route::get('deposit/{id}/update', ['as' => 'admin_update_deposit', 'uses' => 'DepositController@update']);

            Route::get('/agent-sales-reports', ['as' => '_admin_agent_sales_report', 'uses' => '\Modules\Reports\Http\Controllers\Admin\SalesController@index']);

            Route::group(['prefix' => 'new-agent-sign-ups'], function () {
                Route::get('/', ['as' => 'admin_agent_request_list', 'uses' => 'AgentRequestController@index']);
                Route::get('/region-list', ['as' => 'region_list', 'uses' => 'AgentRequestController@showRegion']);

                Route::group(['prefix' => '{id}'], function () {
                    Route::get('/', ['as' => 'admin_agent_request_view_details', 'uses' => 'AgentRequestController@viewDetail']);
                    Route::post('audit', 'AgentRequestController@audit');
                    Route::post('update-bussiness-license', 'AgentRequestController@updateBussinessLicense');
                    Route::get('/revise', ['as' => 'agent-request-revise', 'uses' => 'AgentRequestController@getRevise']);
                    Route::post("/post-revise", ["as" => "agent-request-post-revise", 'uses' => "AgentRequestController@postRevise"]);
                });
                Route::post('/all_agent_signups/export', ['as' => 'export_all_agent_signups', 'uses' => 'AgentRequestController@exportAllAgentSignups']);
            });

            Route::group(['prefix' => 'custom-white-label-site'], function () {
                Route::get('/', ['as' => 'custom-white-label-site', function () {
                    return view('agent-repo.custom-white-label-site');
                }]);
            });

            Route::group(['prefix' => 'agent-feedback'], function () {
                Route::get('/', ['as' => 'agent-feedback', 'uses' => 'FeedBackController@dashboardFe']);
            });
            //Agent Tax Form Review

             Route::group(['prefix' => 'tax_form_review'], function () {
                Route::get('/', ['as' => 'agent_tax_form_review', 'uses' => 'AgentTaxFormController@index']);
            });
              Route::post('/revise_message_agent', ['as' => 'revise_message_agent', 'uses' => 'AgentTaxFormController@revise_message_agent']);
              Route::group(['prefix' => '{id}'], function () {
                Route::get('/audit_page', ['as' => 'audit_page', 'uses' => 'AgentTaxFormController@audit_page']);
                Route::get('/{status}/change_form_status', ['as' => 'change_form_status', 'uses' => 'AgentTaxFormController@change_form_status']);
            });


            //Payment
            Route::group(['prefix' => 'agent-payments'], function () {
                Route::get('/', ['as' => 'new_agent_payment_list', 'uses' => 'AgentPaymentController@index']);
                Route::get('/move-pending-agent-payment-to-t4f', ['as' => 'move_pending_agent_payment_to_t4f', 'uses' => 'AgentPaymentController@syncAgentPendingPayment']);
            });
            Route::group(['prefix' => '{id}/edit'], function () {
                Route::get('payment', ['as' => 'new_edit_agent_payment', 'uses' => 'AgentPaymentController@get']);
                Route::post('update-payment', 'AgentPaymentController@update');
            });
            Route::group(['middleware' => 'group.permission:agent-management','prefix' => 'start-payment'], function () {
                Route::get('/', ['as' => 'new_start_payment', 'uses' => 'AgentPaymentController@startPayment']);
            });
            Route::group(['prefix' => '{id}/payment'], function () {
                Route::get('/invoice', ['as' => 'new_payment_invoice', 'uses' => 'AgentPaymentController@invoice']);
                Route::post('invoicemail', 'AgentPaymentController@invoicemail');
            });

            Route::get('/approved-agent-list', ['as' => 'agent_management_approved_list', 'uses' => 'AgentController@agentList']);

            Route::group(['prefix' => '{id}/edit'], function () {
                Route::get('profile', ['as' => 'agent_management_edit_profile', 'uses' => 'ProfileController@get']);
                Route::post('update-profile', 'ProfileController@update');
                Route::get('net-commission-setting', ['as' => 'agent_management_edit_net_commision_setting', 'uses' => 'NetCommissionSettingController@get']);
                Route::post('update-net-commission-setting', 'NetCommissionSettingController@update');
                Route::get('commission', ['as' => 'agent_management_edit_commission', 'uses' => 'CommissionController@get']);
                Route::post('update-commission', 'CommissionController@update');
                Route::get('site', ['as' => 'agent_management_edit_site_settings', 'uses' => 'SiteController@get']);
                Route::post('update-site', 'SiteController@update');
                Route::get('sub-account', ['as' => 'agent_management_edit_sub_account', 'uses' => 'SubAccountController@get']);
                Route::get('agent-credit', ['as' => 'agent_management_credit_listing', 'uses' => 'CommissionController@agentCredits']);
                Route::get('account-setting', ['as' => 'agent_management_edit_account_setting', 'uses' => 'AgentAccountSettingController@get']);
                Route::post('update-account-setting', ['as' => 'agent_management_update_account_setting', 'uses' => 'AgentAccountSettingController@update']);
                Route::post('update-sub-account', ['as' => 'agent_management_save_sub_account', 'uses' => 'SubAccountController@update']);
                Route::post('edit-sub-account-info', ['as' => 'agent_management_edit_sub_account_commission', 'uses' => 'SubAccountController@editInfo']);
                Route::get('delete-sub-account/{subAccountId}', ['middleware' => 'group.permission:agent-management','as' => 'agent_management_delete_sub_account', 'uses' => 'SubAccountController@delete']);
            });
        });

        Route::group(['prefix' => '/agent-management/deposit-listing', 'namespace' => 'Modules\Agent\Http\Controllers'], function () {
            Route::get('/', ['as' => 'deposit_list', 'uses' => 'DepositController@index']);
            Route::group(['middleware' => 'group.permission:agent-management'], function () {
                Route::get('/create', ['as' => 'add_deposit_and_credit', 'uses' => 'DepositController@create']);
            });
            Route::post('/save', ['as' => 'save_deposit_and_credit', 'uses' => 'DepositController@save']);
            Route::post('/updatedeposit', ['as' => 'update_deposit', 'uses' => 'DepositController@updatedeposit']);
            Route::post('/declinedeposit', ['as' => 'cancel_deposit', 'uses' => 'DepositController@declinedeposit']);
            Route::get('/agent-list', ['as' => 'deposit_agent_list', 'uses' => 'DepositController@getAgents']);
        });

        Route::group(['prefix' => 'agent-management', 'namespace' => 'App\Http\Controllers\BackgroundImageGallery'], function () {
            Route::group(['prefix' => 'white-label-background'], function () {
                Route::get('/', ['as' => 'white_label_background_get', 'uses' => 'BackgroundImageGalleryController@get']);
                Route::post('/save', ['as' => 'white_label_background_save', 'uses' => 'BackgroundImageGalleryController@save']);
            });
            /* https://github.com/Tours4Fun/rezb2b_admin/issues/515
            Command for update new tier commission for agents       */
            Route::get('update-commission-policy', function () {
                \Artisan::call('update-commission-policy');
            });
        });
        Route::group(['prefix' => '/agent-management/white-label-review', 'namespace' => 'Modules\Agent\Http\Controllers'], function () {
            Route::group(['middleware' => 'group.permission:agent-management'], function () {
                Route::get('/create', ['as' => 'agent_review_create', 'uses' => 'ReviewController@create']);
                Route::get('delete/{id}', ['as' => 'agent_review_delete', 'uses' => 'ReviewController@delete']);
            });
            Route::get('/', ['as' => 'agent_review_list', 'uses' => 'ReviewController@index']);
            Route::post('/save', ['as' => 'agent_review_save', 'uses' => 'ReviewController@save']);
            Route::get('get/{id}', ['as' => 'agent_review_get', 'uses' => 'ReviewController@get']);
            Route::get('/product-list', ['as' => 'agent_review_product_list', 'uses' => 'ReviewController@getProducts']);
        });

        //soso add Agent Feedback
        Route::group(['prefix' => '/agent-management/agent-feed-back', 'namespace' => 'Modules\Agent\Http\Controllers'], function () {
            Route::get('/', ['as' => 'agent_feed_back_list', 'uses' => 'FeedBackController@index']);
            Route::get('/survey-template/{id}', ['as' => 'agent_feed_back_detail', 'uses' => 'FeedBackController@detail']);
            Route::get('/download', ['as' => 'agent_feed_back_list_download', 'uses' => 'FeedBackController@download']);
        });

        Route::group(['prefix'=>'operator-management','namespace' => '\Modules\Ttd\Http\Controllers'], function () {
            Route::group(['prefix'=>'{operator_id}/sku-attribute'], function(){
                Route::get('/list', 'TtdProductController@listSkuAttributes');
                Route::post('/add', 'TtdProductController@addSkuAttribute');
                Route::post('/{skuAttributeId}/update', 'TtdProductController@updateAdminSkuAttribute');
                Route::get('/{skuAttributeId}/delete', 'TtdProductController@deleteAdminSkuAttribute');
            });
        });

        // OPERATOR MANAGEMENT START
        Route::group(['middleware' => ['web', 'site.url', 'url.restriction'], 'prefix' => 'operator-management', 'namespace' => 'Modules\Provider\Http\Controllers'], function () {
            Route::post('/revise_message_operator', ['as' => 'revise_message_operator', 'uses' => 'ProviderTaxFormController@revise_message_operator']);
            
            //Tax Form Operator
            Route::get("tax_form_review", ["as" => "operator_tax_form_review", "uses" => "ProviderTaxFormController@index"]);
            Route::group(['prefix' => '{id}'], function () {
                Route::get('/audit_page', ['as' => 'operator_audit_page', 'uses' => 'ProviderTaxFormController@audit_page']);
                Route::get('/{status}/change_form_status', ['as' => 'operator_change_form_status', 'uses' => 'ProviderTaxFormController@change_form_status']);
            });
            //Tax Form Operator
            
            Route::group(['prefix' => 'mail'], function () {
                Route::get("operator-mail", ["as" => "operator_mail", "uses" => "MailController@index"]);
                Route::get("get-store-token", ["as" => "get_store_token", "uses" => "MailController@getStoreToken"]);
                Route::post("post-store-token", ["as" => "post_store_token", "uses" => "MailController@postStoreToken"]);
            });

            Route::get('/', ['as' => 'operator_management_listing', 'uses' => 'DefaultController@listProvider']);
            
            Route::post('/export', ['as' => 'operator_management_listing_export', 'uses' => 'DefaultController@exportCSV']);

            Route::group(['prefix' => '/user'], function () {
                Route::group(['middleware' => 'group.permission:operator-management'], function () {
                    Route::get('/create', ['as' => 'operator_management_create_user', 'uses' => 'ProviderUserController@createUser']);
                });
                Route::get('/', ['as' => 'operator_management_listing_user', 'uses' => 'ProviderUserController@userList']);
                Route::any('/save-user', ['as' => 'opeartor_management_user_save', 'uses' => 'ProviderUserController@saveUser']);
                Route::get('/{id}/edit', ['as' => 'operator_management_user_edit', 'uses' => 'ProviderUserController@editUser']);
                Route::post('/change-status/{user_id}', ['as' => 'opeartor_management_user_change_status', 'uses' => 'ProviderUserController@changeStatus']);
                Route::post('/delete-user', ['as' => 'opeartor_management_user_delete', 'uses' => 'ProviderUserController@deleteUser']);
                //Add new route for operator support permission
                Route::get('/operator-permission/{userId}', ['as' => 'operator_check_permission_user_list', 'uses' => 'ProviderUserController@checkOperatorUserPagePermission']);
                Route::post('/permission', ['as' => 'set_permission_operator_user_list', 'uses' => 'ProviderUserController@setOperatorUserPagePermission']);
            });

            Route::any('operator/create', ['as' => 'new_create_operator', 'uses' => 'ProviderController@doCreate']);

            Route::group(['prefix' => '{id}/edit'], function () {
                
                Route::group(['middleware' => 'group.permission:operator-management'], function () {
                    Route::get('/change-account-status/{userId}', ['as' => 'operator_change_account_status', 'uses' => 'LoginController@operatorUserChangeStatus']);
                    Route::get('/change-email-notice/{userId}', ['as' => 'operator_change_email_notice', 'uses' => 'LoginController@operatorUserChangeEmailNotice']);
                    Route::get('/permission/{userId}', ['as' => 'operator_check_permission_user_page', 'uses' => 'LoginController@checkOperatorUserPagePermission']);
                    Route::get('/unlink-account/{userId}', ['as' => 'operator_unlink_account', 'uses' => 'LoginController@unLinkOperatorUserAccount']);
                    Route::get('/check-link-email-account-operator', ['as' => 'operator_check_link_email_account', 'uses' => 'LoginController@checkOperatorSubAccount']);
                });
                
                Route::get('login', ['as' => 'provider_login', 'uses' => 'LoginController@get']);
                Route::put('login', ['middleware' => 'group.permission:operator-management','uses' => 'LoginController@update']);

                Route::any('/save-operator-user', ['as' => 'operator_user_save', 'uses' => 'LoginController@saveOperatorSubUser']);
                Route::any('{userId}/change-master-account-status', ['as' => 'change_master_account_status', 'uses' => 'LoginController@saveOperatorMasterAccount']);
                Route::post('/permission', ['as' => 'operator_set_permission_user_page', 'uses' => 'LoginController@setOperatorUserPagePermission']);

                Route::get('general', ['as' => 'operator_general_editing_page', 'uses' => 'GeneralController@get']);
                Route::put('general', 'GeneralController@update');
                Route::get('reservation', 'ReservationController@get');
                Route::put('reservation', 'ReservationController@update');
                Route::get('accounting', 'AccountController@get');
                Route::post('accounting', 'AccountController@update');
                Route::get('meta', 'MetaInfoController@get');
                Route::put('meta', 'MetaInfoController@update');
                Route::get('eticket', 'EticketController@get');
                Route::put('eticket', 'EticketController@update');
                Route::post('/change-provider-product-currency', ['as' => 'change_provider_product_currency', 'uses' => 'GeneralController@changeProviderCurrencyAndProductCurrency']);
                Route::get('contact', ['as' => 'operator_management_get_contact', 'uses' => 'ContactController@get']);
                Route::post('/contact', ['as' => 'operator_management_set_contact', 'uses' => 'ContactController@update']);
                Route::get('agreement', ['as' => 'operator_management_get_agreement', 'uses' => 'AgreementController@get']);
                Route::post('/agreement', ['as' => 'operator_management_set_agreement', 'uses' => 'AgreementController@update']);
                Route::get('download-agreement', ['as' => 'operator_management_get_download_agreement', 'uses' => 'AgreementController@downloadContract']);
                //Add Eticket Language wise - #585
                Route::get('operator_language_ajax/{lang}', ['as' => 'operator_language_ajax', 'uses' => 'ReservationController@operatorLangChange']);
            });
            

            //Provide Attribute Route
            Route::group(['prefix' => '{provider_id}/attributes'], function () {
                Route::group(['middleware' => 'group.permission:operator-management'], function () {
                    Route::get('/create', ['as' => 'operator_attribute_create', 'uses' => 'AttributeController@create']);
                    Route::get('/delete/{id}', ['as' => 'operator_attribute_delete','uses' => 'AttributeController@delete']);
                });
                Route::get('/', ['as' => 'operator_attribute_list', 'uses' => 'AttributeController@index']);
                Route::post('/save', ['as' => 'operator_attribute_save', 'uses' => 'AttributeController@save']);
                Route::get('/edit/{id}', ['as' => 'operator_attribute_get', 'uses' => 'AttributeController@get']);
            });

            //Provider Ownexpense Route
            Route::group(['prefix' => '{provider_id}/list'], function () {
                Route::group(['middleware' => 'group.permission:operator-management'], function () {
                    Route::get('/create', ['as' => 'operator_ownexpense_create', 'uses' => 'ProviderOwnexpenseListController@create']);
                    Route::get('/delete/{id}', ['as' => 'operator_ownexpense_delete', 'uses' => 'ProviderOwnexpenseListController@delete']);
                });
                Route::get('/', ['as' => 'operator_ownexpense_list', 'uses' => 'ProviderOwnexpenseListController@index']);
                Route::post('/save', ['as' => 'operator_ownexpense_save', 'uses' => 'ProviderOwnexpenseListController@save']);
                Route::get('/edit/{id}', ['as' => 'operator_ownexpense_get', 'uses' => 'ProviderOwnexpenseListController@get']);
            });
            //Provider SKU Attribute Route
            Route::group(['prefix' => '{provider_id}/skuAttrList'], function() {
                Route::get('/', ['as' => 'operator_sku_attribute_list', function () {
					return view('product.ttd', ['productMasterId' => "skuAttrList"]);
				}]);
            });
            Route::group(['middleware' => 'group.permission:operator-management'], function () {
                Route::get('/create-ownexpense/{id}', ['as' => 'operator_create_own_expense', 'uses' => 'ProviderController@createOwnExpense']);
                Route::get('/delete-ownexpense/{id}', ['middleware' => 'group.permission:operator-management','as' => 'operator_delete_ownexpense', 'uses' => 'ProviderController@deleteOwnExpenese']);
            });
            Route::get('/ownexpense/all/{id}', ['as' => 'operator_own_expense_list', 'uses' => 'ProviderController@ownExpenseList']);
            Route::get('/search-oe-city', ['as' => 'operator_searchOECity', 'uses' => 'ProviderController@serchOwnExpenseCity']);
            Route::get('/search-ownexpense', ['as' => 'operator_searchCityOE', 'uses' => 'ProviderController@searchOwnExpense']);
            Route::get('/edit-ownexpense/{id}', ['as' => 'operator_edit_ownexpense', 'uses' => 'ProviderController@editOwnExpenese']);
            Route::post('/save-ownexpense', ['as' => 'operator_save_ownexpense', 'uses' => 'ProviderController@saveOwnExpense']);

            //Provide Attribute Item Route
            Route::group(['prefix' => '{provider_id}/attributes/{provider_upgrade_id}/item'], function () {
                Route::group(['middleware' => 'group.permission:operator-management'], function () {
                    Route::get('/item/create', ['as' => 'operator_attribute_item_create', 'uses' => 'AttributeItemController@create']);
                    Route::get('/child/create/{provider_upgrade_item_id}', ['as' => 'operator_attribute_child_item_create', 'uses' => 'AttributeItemController@createChildAttribute'])->where('provider_upgrade_item_id', '^[0-9-_\/]+$');
                    Route::get('/delete/{id}', ['as' => 'operator_attribute_item_delete', 'uses' => 'AttributeItemController@delete']);
                });
                Route::post('/item/save', ['as' => 'operator_attribute_item_save', 'uses' => 'AttributeItemController@save']);
                Route::get('/edit/{id}', ['as' => 'opertor_attribute_item_get', 'uses' => 'AttributeItemController@get']);
                Route::post('/child/save', ['as' => 'operator_attribute_child_item_save', 'uses' => 'AttributeItemController@saveChild']);
                Route::get('/child/edit/{provider_upgrade_item_id}', ['as' => 'operator_attribute_child_item_get', 'uses' => 'AttributeItemController@getChildAttribute'])->where('provider_upgrade_item_id', '^[0-9-_\/]+$');
                Route::get('/{parent_id}', ['as' => 'operator_attribute_item_list', 'uses' => 'AttributeItemController@index'])->where('parent_id', '^[0-9-_\/]+$');
            });

            //Provider Ownexpense Item Route
            Route::group(['prefix' => '{provider_id}/list/{provider_ownexpense_list_id}/item'], function () {
                Route::group(['middleware' => 'group.permission:operator-management'], function () {
                    Route::get('/item/create', ['as' => 'operator_ownexpense_item_create', 'uses' => 'ProviderOwnexpenseListItemController@create']);
                    Route::get('/child/edit/{provider_ownexpense_list_item_id}/{languageId?}', ['as' => 'operator_ownexpense_child_item_get', 'uses' => 'ProviderOwnexpenseListItemController@create'])->where('provider_ownexpense_list_item_id', '^[0-9-_\/]+$');
                    Route::get('/delete/{id}', ['as' => 'operator_ownexpense_item_delete', 'uses' => 'ProviderOwnexpenseListItemController@delete']);
                });
                Route::post('/item/save', ['as' => 'operator_ownexpense_item_save', 'uses' => 'ProviderOwnexpenseListItemController@save']);
                Route::get('/edit/{id}', ['as' => 'opertor_ownexpense_item_get', 'uses' => 'ProviderOwnexpenseListItemController@get']);
                Route::get('/child/create/{provider_ownexpense_list_item_id}', ['as' => 'operator_ownexpense_child_item_create', 'uses' => 'ProviderOwnexpenseListItemController@createChildAttribute'])->where('provider_ownexpense_list_item_id', '^[0-9-_\/]+$');
                Route::post('/child/save', ['as' => 'operator_ownexpense_child_item_save', 'uses' => 'ProviderOwnexpenseListItemController@save']);
                Route::get('/{languageId?}', ['as' => 'operator_ownexpense_item_list', 'uses' => 'ProviderOwnexpenseListItemController@index'])->where('languageId', '^[0-9-_\/]+$');
                Route::get('/{id}/lang/{language_id}', ['as' => 'operator_ownexpense_list_item_list_by_lang', 'uses' => 'ProviderOwnexpenseListItemController@getDataUsingAjax'])->where('language_id', '^[0-9-_\/]+$');
            });

            Route::group(['prefix' => 'new-operator-sign-ups'], function () {
                Route::get('/', ['as' => 'operator-request-list', 'uses' => 'ProviderRequestController@index']);

                Route::group(['prefix' => '{id}'], function () {
                    Route::get('/', ['as' => 'operator-request-view-detail', 'uses' => 'ProviderRequestController@viewDetail']);
                    Route::post('/audit', ['as' => 'operator-request-audit', 'uses' => 'ProviderRequestController@audit']);
                    Route::get('/revise', ['as' => 'operator-request-revise', 'uses' => 'ProviderRequestController@getRevise']);
                    Route::post("/post-revise", ["as" => "operator-request-post-revise", 'uses' => "ProviderRequestController@postRevise"]);
                });

                Route::post('/all_operator_signups/export', ['as' => 'export_all_operator_signups', 'uses' => 'ProviderRequestController@exportAllOperatorSignups']);
            });
        });
        // OPERATOR MANAGEMENT FINISH
        Route::group(['middleware' => ['web', 'site.url', 'url.restriction'], 'prefix' => 'local-expert-management', 'namespace' => 'Modules\LocalExpert\Http\Controllers'], function () {
            Route::get('/q-a', ['as' => 'local_expert_management_qa', 'uses' => 'LocalExpertController@questionList']);
            Route::get('/q-a/get/{id}', ['as' => 'local_expert_management_qa_details', 'uses' => 'LocalExpertController@getQA']);
            Route::post('/change-shown/{id}', ['as' => 'local_expert_qa_shown', 'uses' => 'LocalExpertController@changeShown']);
            Route::post('/edit-answer', ['as' => 'local_expert_qa_save', 'uses' => 'LocalExpertController@editAnswer']);
            Route::get('/q-a/answer/{id}', ['as' => 'local_expert_management_qa_edit', 'uses' => 'LocalExpertController@getAnswer']);
            Route::get('/inquiry', ['as' => 'local_expert_management_inquiry', 'uses' => 'LocalExpertController@inquiryList']);
            Route::post('/export-inquiry', ['as' => 'export_all_inquiry', 'uses' => 'LocalExpertController@exportInquiry']);
            Route::get('/inquiry/get/{id}', ['as' => 'local_expert_management_inquiry_detail', 'uses' => 'LocalExpertController@getInquiry']);

            // Local expert module Start
            Route::get('/local-expert-list', ['as' => 'local_expert_management_list', 'uses' => 'LocalExpertController@localExpertList']);
            Route::get('{providerId}/edit', ['as' => 'local_expert_expert_profile_get','uses' => 'LocalExpertController@getLocalExpertDetail']);
            Route::post('/save', ['as' => 'local_expert_expert_profile_save','uses' => 'LocalExpertController@save']);
            Route::get('/country-list', ['as' => 'local_expert_country_list', 'uses' => 'LocalExpertController@getCountries']);
            Route::get('/get_activity_cities', ['as' => 'local_expert_get_activity_cities', 'uses' => 'LocalExpertController@searchCities']);
            Route::post('/validate', ['as' => 'local_expert_expert_profile_validate','uses' => 'LocalExpertController@validateFormData']);
            Route::any('change-expert-status', ['as' => 'change_expert_status', 'uses' => 'LocalExpertController@doUpdateStatus']);
            // Local expert module End
            
            //Route::post('/all_operator_signups/export', ['as' => 'export_all_operator_signups', 'uses' => 'ProviderRequestController@exportAllOperatorSignups']);
        });

        Route::group(['prefix' => '/cn_agent','namespace' => '\App\Http\Controllers\CnAgent'], function () {
            Route::get('/get_search_setting', ['as' => 'get_agent_search_setting', 'uses' => 'SearchController@getSearchSetting']);
            Route::post('/save_search_setting', ['as' => 'save_agent_search_setting', 'uses' => 'SearchController@saveSearchSetting']);
            Route::post('/upload_image', ['as' => 'agent_upload_image', 'uses' => 'SearchController@uploadImage']);
        });
    });
    Route::group(['middleware' => ['web', 'site.url', 'url.restriction'], 'prefix' => 'local-expert-management', 'namespace' => 'Modules\LocalExpert\Http\Controllers'], function () {
        Route::get('/q-a', ['as' => 'local_expert_management_qa', 'uses' => 'LocalExpertController@questionList']);
        Route::get('/q-a/get/{id}', ['as' => 'local_expert_management_qa_details', 'uses' => 'LocalExpertController@getQA']);
        Route::post('/change-shown/{id}', ['as' => 'local_expert_qa_shown', 'uses' => 'LocalExpertController@changeShown']);
        Route::post('/edit-answer', ['as' => 'local_expert_qa_save', 'uses' => 'LocalExpertController@editAnswer']);
        Route::get('/q-a/answer/{id}', ['as' => 'local_expert_management_qa_edit', 'uses' => 'LocalExpertController@getAnswer']);
        Route::get('/inquiry', ['as' => 'local_expert_management_inquiry', 'uses' => 'LocalExpertController@inquiryList']);
        Route::get('/inquiry/get/{id}', ['as' => 'local_expert_management_inquiry_detail', 'uses' => 'LocalExpertController@getInquiry']);
        Route::post('/assign-local-expert/{id}', ['as' => 'assign_local_expert', 'uses' => 'LocalExpertController@assignExpertProvider']);

    });
    Route::group(['middleware' => ['web', 'site.url', 'url.restriction'], 'prefix' => 'operator-management', 'namespace' => 'Modules\Localization\Http\Controllers'], function () {

        Route::group(['prefix' => 'operator-language'], function () {
            Route::group(['middleware' => 'group.permission:operator-management'], function () {
                Route::get('create', ['as' => 'operator_language_save', 'uses' => 'ProviderLanguageController@create']);
                Route::get('{id}/delete', ['middleware' => 'group.permission:operator-management','as' => 'operator_language_delete', 'uses' => 'ProviderLanguageController@delete']);
            });
            Route::any('/', ['as' => 'operator_language_list', 'uses' => 'ProviderLanguageController@index']);
            Route::post('save', ['as' => 'operator_language_save', 'uses' => 'ProviderLanguageController@save']);
            Route::get('edit/{id}', ['as' => 'operator_language_edit', 'uses' => 'ProviderLanguageController@get']);
        });
    });

    Route::group(['middleware' => ['web', 'site.url', 'url.restriction'], 'prefix' => 'tools', 'namespace' => 'Modules\Order\Http\Controllers'], function () {
        Route::get('/order-mismatch', ['as' => 'order_mismatch_list', 'uses' => 'OrderMismatchController@index']);
        Route::get('/order-mismatch/export', ['as' => 'order_mismatch_list_download', 'uses' => 'OrderMismatchController@exportAllToExcel']);
    });

    Route::group(['middleware' => ['web', 'site.url', 'url.restriction'], 'prefix' => 'accounting', 'namespace' => 'Modules\Accounting\Http\Controllers'], function () {
        Route::get('/', ['as' => 'admin_accounting_home', 'uses' => 'AccountingController@index']);

        Route::group(['prefix' => 'monthly-agent-invoice'], function () {
            Route::get('/', ['as' => 'admin_monthly_agent_invoice_home', 'uses' => 'MonthlyAgentInvoiceController@index']);
            Route::get('/get-sales', ['as' => 'admin_monthly_agent_invoice_get_sales', 'uses' => 'MonthlyAgentInvoiceController@getSales']);
            Route::get('/create-invoice', ['as' => 'admin_monthly_agent_invoice_create_invoice', 'uses' => 'MonthlyAgentInvoiceController@createInvoice']);
            Route::get('/{invoiceNo}/download', ['as' => 'admin_monthly_agent_invoice_download', 'uses' => 'MonthlyAgentInvoiceController@download']);
        });

        Route::group(['prefix' => 'agent-payment-received'], function () {
            Route::get('/', ['as' => 'admin_agent_payment_received_home', 'uses' => 'AgentPaymentController@index']);
            Route::get('/get-invoice', ['as' => 'admin_agent_payment_invoice', 'uses' => 'AgentPaymentController@invoiceList']);
            Route::get('/get-invoices.json', ['as' => 'admin_agent_payment_get_invoice_json', 'uses' => 'AgentPaymentController@invoicesList']);
            Route::post('/received-payment', ['as' => 'admin_agent_payment_received', 'uses' => 'AgentPaymentController@receivedPayment']);
        });
		
		Route::group(['prefix' => 'order-settlement'], function(){
			Route::get('/', ['as' => 'admin_order_settlement', 'uses' => 'OrderSettlementController@index']);
            Route::get('/download-report', ['as' => 'admin_order_settlement_download_report', 'uses' => 'OrderSettlementController@downloadReport']);
			Route::group(['prefix' => '{orderId}'], function(){
				Route::get('/', ['as' => 'admin_order_settlement_detail', 'uses' => 'OrderSettlementController@detail']);
				Route::get('/settlement-info/{settlementId}', ['as' => 'admin_order_settlement_detail_settlement_info', 'uses' => 'OrderSettlementController@settlementInfo']);
				Route::post('/create-log/{settlementId}', ['as' => 'admin_order_settlement_detail_create_log', 'uses' => 'OrderSettlementController@createLog']);
                Route::post('/refund/{settlementId}', ['as' => 'admin_order_settlement_refund', 'uses' => 'OrderSettlementController@autoRefund']);
			});
		});

    });

    Route::group(['middleware' => ['web', 'site.url', 'url.restriction'], 'prefix' => 'cron-report', 'namespace' => 'Modules\CronReport\Http\Controllers'], function () {
        Route::group(['prefix' => 'log-table'], function(){
            Route::get('/', ['as' => 'cron-report.log-table.index', 'uses' => 'LogTableController@index']);
            Route::get('/records', ['as' => 'cron-report.log-table.records', 'uses' => 'LogTableController@records']);
            Route::post('/records', ['as' => 'cron-report.log-table.save-records', 'uses' => 'LogTableController@saveRecords']);
            Route::get('/delete-record', ['as' => 'cron-report.log-table.delete', 'uses' => 'LogTableController@delete']);
        });
    });


});
