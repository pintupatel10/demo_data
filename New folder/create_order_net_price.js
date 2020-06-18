'use strict';
/* Global Object Declare */
var countProductRow = 0;
var passengerAttributes = {};
var rateOptions = {};
var priceArray = {};
var calendars = {};
var minMaxGuestNumber = {};
var selectValues = new Array();
var startTime = 0;
var month_first_date = 0;
var month_end_date = 0;
var availableSkuInfo = {};
var availableRateInfo = {};
var selectedSkuInfo = {};
var firstTimeRateArray = {};
var totalCnt = 0;
var areaCodeData = {};
var productRateArray = {};
var productPriceArray = {};
//Total Raw Count
function totalRow() {
    countProductRow = parseInt($('.well-sm:last').attr('data-row-id'));
}
//Current Raw ID
function currentRowID(Obj) {
    return Obj.parents('.parentProduct').attr('data-row-id');
}
//Customer Search
function showResultUser(data) {
    if (data.loading) return data.text;
    var markup  = "<div class='select2-result-repository clearfix'>" +
                    "<div class='select2-result-repository__title'>" + data.text + "</div>";
        markup += "</div>";
    return markup;
}
function showResultSelectUser(data) {
    return data.text;
}
function encodeFromInput(string) {
    return (typeof string == 'string') ? string.replace(/ /g,"_").toLowerCase() : string;
}
//Display Product Details
function displayProductDetails(productObject,e){
    var product_id = productObject.val();
    $.ajax({
        url: '/booking/fetch-product-details/'+product_id,
        data : { _token : window.csrf_token_string},
        dataType : 'json',
        method:'post',
        beforeSend: function () {
            $(".loading").show();
        }
    }).done(function(data){

        
        if(data.productDetail.code != 200){
            $(".loading").hide();
            notify(data.productDetail.message, "danger");
            //$('.product-detail-div').hide();
        }else{
            //first remove old data
            $('.product-detail-div').find('div.roomRow').remove();
            $('.product-detail-div').find('div.skuRoomRow').remove();
            $('.product-detail-div').find('div.departureRow').remove();
            $('.orderProductDiv').append(generateProductDetail());
            totalRow();
            var newId = $('.orderProductDiv').find('.well-sm:last').attr('data-row-id');
            generateProductSubDetail(data.productDetail,e,newId,productObject);

            //assign product & price details
            if(typeof priceArray[newId] === "undefined"){
                priceArray[newId] = {};
            }
            
            //Assign  rateOptions
            if(typeof rateOptions[newId] === "undefined"){
                rateOptions[newId] = {};
            }
            
            //Assign  passengerAttributes
            if(typeof passenggerAttributes[newId] === "undefined"){
                passengerAttributes[newId] = {};
            }
            //Assign  min Guest No
            if(typeof minMaxGuestNumber[newId] === "undefined"){
                minMaxGuestNumber[newId] = {};
            }
            
            passengerAttributes[newId]['product_name'] = e.params.data.name;
            passengerAttributes[newId]['attributes'] = data.productDetail.passengerAttributes;
            rateOptions[newId] = data.productDetail.rates;

            priceArray[newId]['product_name'] = e.params.data.name;

            //Assign min & max Number
            minMaxGuestNumber[newId]['min_guest_number'] = data.productDetail.basicInfo.min_guest_number;
            minMaxGuestNumber[newId]['max_guest_number'] = data.productDetail.basicInfo.max_guest_number;
            $(".loading").hide();

        }
    });
}

//Display Product Details
function displayProductDetailsWithNetPrice(productObject,e,agentId){
    var product_id = productObject.val();
    $.ajax({
        url: '/booking/fetch-product-details-net-price/'+product_id+"/"+agentId,
        data : { _token : window.csrf_token_string},
        dataType : 'json',
        method:'post',
        beforeSend: function () {
            $(".loading").show();
        }
    }).done(function(data){

        if(data.productDetail.code != 200){
            $(".loading").hide();
            notify(data.productDetail.message, "danger");
            //$('.product-detail-div').hide();
        }else{
            //first remove old data
            $('.product-detail-div').find('div.roomRow').remove();
            $('.product-detail-div').find('div.skuRoomRow').remove();
            $('.product-detail-div').find('div.departureRow').remove();
            $('.orderProductDiv').append(generateProductDetail());
            totalRow();
            var newId = $('.orderProductDiv').find('.well-sm:last').attr('data-row-id');
            api_source = (typeof data.productBasic.api_source == 'string' && data.productBasic.api_source != '') ? data.productBasic.api_source.toLowerCase() : '' ;
            generateProductSubDetail(data.productDetail,e,newId,productObject,agentId);

            //assign product & price details
            if(typeof priceArray[newId] === "undefined"){
                priceArray[newId] = {};
            }
            
            //Assign  rateOptions
            if(typeof rateOptions[newId] === "undefined"){
                rateOptions[newId] = {};
            }
            
            //Assign  passengerAttributes
            if(typeof passengerAttributes[newId] === "undefined"){
                passengerAttributes[newId] = {};
            }
            //Assign  min Guest No
            if(typeof minMaxGuestNumber[newId] === "undefined"){
                minMaxGuestNumber[newId] = {};
            }
            
            passengerAttributes[newId]['product_name'] = e.params.data.name;
            passengerAttributes[newId]['attributes'] = data.productDetail.passengerAttributes;
            passengerAttributes[newId]['lead_traveller'] = data.productDetail.basicInfo.is_required_only_lead_traveler;
            rateOptions[newId] = data.productDetail.rates;

            priceArray[newId]['product_name'] = e.params.data.name;

            //Assign min & max Number
            minMaxGuestNumber[newId]['min_guest_number'] = data.productDetail.basicInfo.min_guest_number;
            minMaxGuestNumber[newId]['max_guest_number'] = data.productDetail.basicInfo.max_guest_number;

            if(typeof data.productDetail.prices != "undefined") {
                var firstKey = Object.keys(data.productDetail.prices)[0];
                if(typeof data.productDetail.prices[firstKey] != "undefined") {
                    month_first_date = data.productDetail.prices[firstKey].date;
                }
            }

            $(".loading").hide();

        }
    });
}
/* Product Detail Creation */
function generateProductDetail(){
    var html = '';
        html +='<div class="row well well-sm  parentProduct product-detail-div-'+(countProductRow+1)+'" data-row-id="'+(countProductRow+1)+'">';
        html +='<span class="txt-product-line"></span>';
        html +='<div class="nameRow-'+(countProductRow+1)+'">';
        html +='<div class="col-md-1 text-right">';
       // html +='<button class="btn btn-sm btn-danger pull-left m-l deleteProduct" type="button">delete</button>';
        html +='</div>';
        html +='<div class="col-md-2 text-right">';
		html +='<label class="col-form-label form-control-label product-label"></label>';
	    html +='</div>';
	    html +='<div class="col-md-9 form-group row text-left">';
	    html +='<label class="col-form-label form-control-label product-name"></label>';
	    html +='<input type="hidden" name="item['+(countProductRow+1)+'][product_id]" class="product_id">';
	    html +='<input type="hidden" name="item['+(countProductRow+1)+'][product_source]" value="EN" class="product_source">';
	    html +='</div>';
	    html +='<span class="productDetail" data-duration="" data-durationType=""></span>';
		html +='</div>';
		html +='<div class="clearfix"></div>';
	    html +='<div class="dateRow-'+(countProductRow+1)+'">';
	    html +='<div class="col-md-3 text-right">';
	    html +='<label class="col-form-label form-control-label">Start Date :</label>';
	    html +='</div>';
	    html +='<div class="col-md-9 text-left">';
	    html +='<div class="col-sm-4 form-group row">';
	    html +='<div class="main-departure">';
	    html +='<div class="departure-date">';
	    html +='<input type="text" name="item['+(countProductRow+1)+'][departure_date]" class="form-control departure_date" placeholder="Select Date" data-rawId="'+(countProductRow+1)+'">';
	    html +='</div>';
	    html +='<div class="bot-cal">';
	    html +='<script type="text/template" id="template-calendar">';
	    html +='<div class="responsive-calendar">';
	    html +='<div class="controls">';
	    html +='<a class="pull-left clndr-previous-button clndr-btn"><div class="btn btn-sm btn-primary leftButton"><</div></a>';
	    html +='<h4><span data-head-year class="month_year_name"><%= month %> <%= year %></span></h4>';
	    html +='<a class="pull-right clndr-next-button clndr-btn"><div class="btn btn-sm btn-primary rightButton">></div></a>';
	    html +='</div>';
	    html +='<hr/>';
	    html +='<div class="day-headers">';
	    html +='<% _.each(daysOfTheWeek, function(day) { %>';
	    html +='<div class="day header"><%= day %></div>';
	    html +='<% }); %>';
	    html +='</div>';
	    html +='<div class="days" data-group="days">';
	    html +='<% _.each(days, function(day) { %>';
	    html +='<% if (day.events.length > 0) { %>';
	    html +='<div class="day tue past <%= day.classes %>" data-json="<%= day.day %>" ><a data-day="1" data-month="4" data-year="2014"><%= day.day %></a></div>';
	    html +='<% }else{ %>';
	    html +='<div class="day tue not-current <%= day.classes %>" data-json="<%= day.day %>" ><a data-day="1" data-month="4" data-year="2014"><%= day.day %></a></div>';
	    html +='<% } %>';
	    html +='<% }); %>';
	    html +='</div>';
	    html +='</div>';
	    html +='</script>';
	    html +='</div>';
	    html +='</div>';
	    html +='</div>';
	    html +='<div class="col-sm-4">';
	    html +='<input type="text" name="item['+(countProductRow+1)+'][end_date]" class="form-control end_date" placeholder="End date" readonly="readonly">';
	    html +='</div>';
	    html +='</div>';
	    html +='</div>';
	    html +='</div>';
	    html +='</div>';
	return html;
}
/* Room Info Arrange Function */
function roomInfo(room_info,newId){
	var html = '';
	html += '<div class="row rooms" data-rawId="_ID_">';
	var count = 1;
	$.each(room_info, function (index, value) {
		
        var canBeSelected = [];
        var defaultQty = (index != 0) ? 0 : 1;

        if($.isArray(value.available_seat_type) && value.available_seat_type.length > 0 && index == 0) {
            canBeSelected = value.available_seat_type;
            defaultQty = canBeSelected[0];
        }

        canBeSelected = canBeSelected.join(",");

        var minQuantity = value.min_quantity;
        if(count == 1 && minQuantity == 0) {
			minQuantity = minQuantity;
		}else if (count == 1 && minQuantity != defaultQty) {
            minQuantity = defaultQty;
        }

		html += '<div class="col-xl-3 col-sm-12 text-center width_165">';
			
		html += '<div class="p-l-0 m-b-25">';
		html += '<div class="input-group rateDiv">';
		html += '<span class="input-group-btn"><button type="button" class="btn btn-default btn-number shadow-none minusRoom" data-type="minus" data-qty-choise="'+canBeSelected+'" data-rawId="'+newId+'">';
		html += '<span class="ion-minus m-0"></span></button></span>';
		html += '<input type="hidden" name="item['+newId+'][rate_options][_ID_]['+value.rate_id+'][rate_id]" value="'+value.rate_id+'" >';
		html += '<input readonly="true" type="text" name="item['+newId+'][rate_options][_ID_]['+value.rate_id+'][qty]" class="form-control input-number text-center rate" value="'+minQuantity+'" data-min="'+value.min_quantity+'" data-max="'+value.max_quantity+'" data-name="rate_options[_ID_]['+value.rate_id+']" data-rawId="'+newId+'" data-qty_count="'+1+'" data-key="'+value.rate_id+'">';
		html += '<span class="input-group-btn">';
	    html += '<button type="button" class="btn btn-default btn-number shadow-none plusRoom" data-type="plus" data-qty-choise="'+canBeSelected+'" data-rawId="'+newId+'">';
	    html += '<span class="ion-plus m-0"></span>';
	    html += '</button></span>';
		html += '</div>';
		html += '<label>'+value.name+'</label>';
		html += '</div>';
		html += '</div>';

		count++;
	});
	html += '</div>';
	return html;
}

/* SKU Info Arrange Function */
function skuInfo(skuInfo,newId) {
    var customRates = {};
    $.each(skuInfo,function(key,value){
        if(typeof customRates[value.person_type] === "undefined"){
            customRates[value.person_type] = {};
        }
        customRates[value.person_type][key] = value;
    });

    var html = '';
    html += '<div class="skuRoomRow-'+newId+'" style="display:none">';
    html += '<div class="col-md-12 text-left skuRoomDetail">';
    html += '<div class="row skuRooms" data-rawId="_ID_">';

    var cusCnt = 1;
    $.each(customRates, function (index, skuValue) {
        html += '<div class="col-md-3 text-right">';
        html += '<label class="col-form-label form-control-label">'+index+' :</label>';
        html += '</div>';
        html += '<div class="col-md-6 text-left">';
        html += '<div class="form-group">';
        html += '<ul class="skuName-group skuGroup-'+cusCnt+'">';
        var skuCount = 0;
        $.each(skuValue, function (indexSku, valueSku) {
            skuCount++;
            var skuguest = (skuCount > 10) ? 'showGuest' : '';
            html += '<li class="skuName '+skuguest+'" data-rawId="'+newId+'" data-skuValue='+valueSku.rate_id+' data-type="'+index+'">'+valueSku.label+'</li>';
        });
        if(skuCount > 10) {
            html += '<a href="javascript:void(0);" class="showAll" data-SkuCount="'+cusCnt+'" style="position: absolute;top: 40%;margin-top: -10px;font-size: 14px;color: #1f7ff0;">More</a>';
        }
        html += '</ul>';
        html += '</div>';
        html += '</div>';
        html += '<div class="clearfix"></div>';
        cusCnt++;
    });

    html += '</div>';
    html += '</div>';
    html += '</div>';
    return html;
}

function skuRateInfo(selectedSkuInfo,newId) {
    var html = '';
    html += '<div class="skuRatesInfo-'+newId+'" style="display:none">';
    html += '<div class="col-md-3 text-right">';
    html += '<label class="col-form-label form-control-label">Selected Type :</label>';
    html += '</div>';
    html += '<div class="col-md-6 text-left">';
    html += '<div class="form-group">';

    html += '<ul>';
    $.each(availableSkuInfo, function (index,value) {
        $.each(selectedSkuInfo, function (index1,value1) {
            if(value1 == value.rate_id) {
                html += '<li style="margin-top: 5px;list-style: none;" class="selectedSku-'+value1+'" data-skuValue='+value1+'>';
                html += '<div class="clearfix" style="position: relative;">';
                html += '<div class="book-item" style="float: left;width: 423px;border: 1px solid #c4c4c4;font-size: 14px;padding: 8px 5px 5px;font-size: 14px;">';
                html += '<div class="clearfix rateDiv">';
                html += '<div style="border: 0;float: left;margin-right: 10px;border-radius: 5px 5px 0 0;height: 25px;width: auto;background-image: none;">'+value.person_type+'</div>';
                html += '<div style="border: 0;float: left;margin-right: 10px;width: 170px;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;">'+value.label+'</div>';
                html += '<div style="border: 0;float: left;margin-right: 10px;" class="selectedSkuPriceRatesInfo-'+value.rate_id+'">$0</div>';
                html += '<div style="border: 0;float: left;margin-right: 10px;float: right;margin-right: 0;position: relative;width: 87px;text-align: center;">';
                html += '<span class="ion-minus m-r-20 minusRoom" data-type="minus" data-rawId="'+newId+'" style="cursor: pointer;"></span>';
                html += '<input type="hidden" name="item['+newId+'][rate_options]['+newId+']['+value.rate_id+'][rate_id]" value="'+value.rate_id+'" >';
                html += '<input style="position: absolute;text-align: center;border: none;top: -6%;width: 25px;right: 30px;" readonly="true" type="text" name="item['+newId+'][rate_options]['+newId+']['+value.rate_id+'][qty]" class="rate" value="'+value.min_quantity+'" data-min="'+value.min_quantity+'" data-max="'+value.max_quantity+'" data-name="rate_options['+newId+']['+value.rate_id+']" data-rawId="'+newId+'" data-qty_count="'+1+'" data-key="'+value.rate_id+'">';
                //html += '1';
                html += '<span class="ion-plus m-l-10 plusRoom" data-type="plus" data-rawId="'+newId+'" style="cursor: pointer;"></span>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
                html += '<a href="javascript:void(0);" class="text-btn removeSkuRoom m-l-20" data-skuValue='+value1+' data-rawId="'+newId+'" style="position: absolute;top: 50%;margin-top: -10px;font-size: 14px;color: #1f7ff0;">Delete</a>';
                html += '</div>';
                html += '</li>';
            }
        });
    });
    html += '</ul>';

    html += '</div></div><div class="clearfix"></div>';
    html += '</div>';
    return html;
}

//Room Detail
function roomDetail(room_info,newId,isRoomButtonDis=false){
    var html = '';
    html += '<div class="roomRow-'+newId+'" style="display:none">';
    html += '<div class="col-md-3 text-right">';
    if(isRoomButtonDis){
        html += '<label class="col-form-label form-control-label m-t-20">Room :</label>';
    }
    html += '</div>';
    html += '<div class="col-md-8 text-left roomDetail">';
    html += roomInfo(room_info,newId);
    if(isRoomButtonDis){
        html += '<div class="form-group">';
        html += '<button type="button" class="btn btn-warning waves-effect waves-light addRoom m-r-5" data-rawId="'+newId+'"><i class="fa fa-plus"></i> Increase Room</button>';
        html += '<button type="button" class="btn btn-danger waves-effect waves-light removeRoom m-r-5" data-rawId="'+newId+'"><i class="fa fa-plus"></i> Delete Room</button>';
        html += '</div>';
    }
    html += '</div>';
    html += '</div>';
    return html;
}
//Departure Location
function departureDetail(departureInfo,newId,agentId){
    var productDiv          = $('.product-detail-div-'+newId);
    var product_line        = productDiv.find('.productDetail').attr('data-product_line');
    var html = '';

    if(product_line == 'ttd') {
        html += '<div class="departureRow-'+newId+'" style="display:none">';
        html += '<div class="col-md-3 text-right">';
        html += '<label class="col-form-label form-control-label">Departure time and place :</label>';
        html += '</div>';
        html += '<div class="col-md-8 text-left">';
        html += '<div class="form-group">';
        html += '<select name="item['+newId+'][departure_location]" class="form-control departure_location" data-name="" data-rawId="'+newId+'">';
        html += '</select>';
        html +='</div></div></div></div>';

        html += '<div class="departureOnRequestRow-'+newId+'" style="display:none">';
        html += '<div class="col-md-3 text-right">';
        html += '<label class="col-form-label form-control-label">Departure time and place :</label>';
        html += '</div>';
        html += '<div class="col-md-8 text-left">';
        html += '<div class="form-group">';
        html += '<textarea cols="10" rows="3" name="item['+newId+'][departure_location]" class="form-control departure_location_on_request" data-name="" data-rawId="'+newId+'"></textarea>';
        html +='</div></div></div></div>';

        html += '<div class="departureEndLocationRow-'+newId+'" style="display:none">';
        html += '<div class="col-md-3 text-right">';
        html += '<label class="col-form-label form-control-label">End time and place :</label>';
        html += '</div>';
        html += '<div class="col-md-8 text-left">';
        html += '<div class="form-group">';
        html += '<select name="item['+newId+'][end_location]" class="form-control end_location" data-name="" data-rawId="'+newId+'">';
        html += '</select>';
        html +='</div></div></div></div>';

        html += '<div class="departureEndLocationOnRequestRow-'+newId+'" style="display:none">';
        html += '<div class="col-md-3 text-right">';
        html += '<label class="col-form-label form-control-label">End time and place :</label>';
        html += '</div>';
        html += '<div class="col-md-8 text-left">';
        html += '<div class="form-group">';
        html += '<textarea cols="10" rows="3" name="item['+newId+'][end_location]" class="form-control end_location_on_request" data-name="" data-rawId="'+newId+'"></textarea>';
        html +='</div></div></div></div>';

    } else {
        var checkAttribute = $(".dateRow-1 .col-md-9").find();
        
        if (api_source == 'fareharbor' && checkAttribute.prevObject.length > 0) {
            // update for display start time as wightlable
            var html_far = '<div class="departureRow-'+(newId+1)+'" style="display:none">';
            html_far += '<div class="col-md-3 text-right">';
            html_far += '<label class="col-form-label form-control-label">Start Time :</label>';
            html_far += '</div>';
            html_far += '<div class="col-md-7 text-left">';
            html_far += '<div class="form-group">';
            html_far += '<select name="item['+newId+'][start_time]" class="form-control departure start_time" data-name="start_time" data-rawId="'+newId+'">';
            $.each(departureInfo.departure, function (index, value) {
                var name = value['time'];
                html_far += '<option value="'+name+'">'+name+'</option>';
              
            });
            html_far += '</select>';
            html_far +='</div></div></div></div>';
            
            $(".dateRow-1 .col-md-9").after(html_far);

        }else{
            html += '<div class="departureRow-'+(newId+1)+'" style="display:none">';
            html += '<div class="col-md-3 text-right">';
            html += '<label class="col-form-label form-control-label">Start Time :</label>';
            html += '</div>';
            html += '<div class="col-md-7 text-left">';
            html += '<div class="form-group">';
            html += '<select name="item['+newId+'][start_time]" class="form-control departure start_time" data-name="start_time" data-rawId="'+newId+'">';
            $.each(departureInfo.departure, function (index, value) {
                var name = value['time'];
                html += '<option value="'+name+'">'+name+'</option>';
              
            });
            html += '</select>';
            html +='</div></div></div></div>';    
        }

        html += '<div class="departureRow-'+newId+'" style="display:none">';
        html += '<div class="col-md-3 text-right">';
        html += '<label class="col-form-label form-control-label">Departure time and place :</label>';
        html += '</div>';
        html += '<div class="col-md-8 text-left">';
        html += '<div class="form-group">';
        html += '<select name="item['+newId+'][departure_location]" class="form-control departure_location" data-name="" data-pickup="" data-rawId="'+newId+'">';
        // if(departureInfo.pickupRequired == 'yes'){
        //     $.each(customPickup, function(key, value) {
        //         html += '<option value = "'+value+'" data-info = "'+key+'">'+value+'</option>';
        //     });
        // }else{
        //     $.each(departureInfo.departure, function (index, value) {
        //         var time = (value['time'] != null || value['time'] != undefined) ? value['time'] : '';
        //         var location = (value['location'] != null || value['location'] != undefined) ? value['location'] : '';
        //         var address = (value['address'] != null || value['address'] != undefined) ? value['address'] : '';
        //         var name = time+' At '+location+' '+address;
        //         html += '<option value="'+name+'">'+name+'</option>';
        //     });
        // }
        html += '</select>';
        html += '<input type="hidden" id="pickup_on_request" name="item['+newId+'][pickup_on_request]" value="'+ (departureInfo.pickupRequired == 'yes' ? 1 : 0) +'">';
        html +='</div></div></div></div>';

        html += '<div class="pickupRow-'+newId+'" style="display:none">';
        html += '<div class="col-md-3 text-right">';
        html += '<label class="col-form-label form-control-label">Pickup Information :</label>';
        html += '</div>';
        html += '<div class="col-md-8 text-left">';
        html += '<div class="form-group">';
        html += '<input type="text" name="item['+newId+'][pickup_location]" class="form-control pickup" id="pickup_location">';
        html += '</div></div></div></div>';

        html += '<div class="departureEndLocationRow-'+newId+'" style="display:none">';
        html += '<div class="col-md-3 text-right">';
        html += '<label class="col-form-label form-control-label">End time and place :</label>';
        html += '</div>';
        html += '<div class="col-md-8 text-left">';
        html += '<div class="form-group">';
        html += '<select name="item['+newId+'][end_location]" class="form-control end_location" data-name="" data-rawId="'+newId+'">';
        html += '</select>';
        html +='</div></div></div></div>';

        html += '<div class="departureEndLocationOnRequestRow-'+newId+'" style="display:none">';
        html += '<div class="col-md-3 text-right">';
        html += '<label class="col-form-label form-control-label">End time and place :</label>';
        html += '</div>';
        html += '<div class="col-md-8 text-left">';
        html += '<div class="form-group">';
        html += '<textarea cols="10" rows="3" name="item['+newId+'][end_location]" class="form-control end_location_on_request" data-name="" data-rawId="'+newId+'"></textarea>';
        html +='</div></div></div></div>';
    }
    
    return html;
}
function upgradeDetailWithOption(attributes,newId){
    //Upgrade Options
    var html = '';
    html += '<div class="upgradeRow-'+newId+'" >';
    html += '<div class="upgrade">';
    var first_name='';
    $.each(attributes, function (index, value) {
        if (value.option_selections.length > 0) {
            var upgradeCount = 1;
            html += '<div class="upgradeDiv">';
            html += '<div class="col-md-3 text-right">';
            html += '<label class="form-control-label">'+ value.option_name +' : </label>';
            html += '</div>';
            html += '<div class="col-md-7">';
            html += '<div class="p-l-0 m-b-10">';
            html += '<div class="input-group">';

            if(value.is_multi == 1){

                html += '<select name="item['+newId+'][upgrade]['+value.option_id+'][]" class="form-control upgrades overFloW" data-name="upgrade['+value.option_id+']" data-upgrade-id="'+value.option_id+'" multiple data-rawId="'+newId+'" data-count="'+upgradeCount+'" data-isMulti="'+value.is_multi+'" data-minSelect="'+value.required+'" data-maxSelect="'+value.max_required+'">';
            }else{
                html += '<select name="item['+newId+'][upgrade]['+value.option_id+']['+(upgradeCount)+']" class="form-control upgrades overFloW" data-name="upgrade['+value.option_id+']['+upgradeCount+']" data-upgrade-id="'+value.option_id+'" data-rawId="'+newId+'" data-count="'+upgradeCount+'" data-isMulti="'+value.is_multi+'">';
            }
                    
            //html += '<option value=""> No Upgrades </option>';
            $.each(value.option_selections, function (k, v) {
                         if(v.is_has_sub == 1)
                        {
                            $.each(v.sub_options, function (k1, v1) {
                                html += '<option value="'+v1.value+'">'+v1.text+'</option>';
                            });
                        
                        }
                        else{
                html += '<option value="'+v.value+'">'+v.text+'</option>';
                        }
            });
            html += '</select>';
            html += '</div></div></div>';
            html += '<div class="clearfix"></div>';
            html +='</div>';
        }
    });
    html +='</div></div>';
    return html;
}
//Upgrade Detail
function upgradeDetail(attributes,newId){
    //Upgrade Options
    var html = '';
    html += '<div class="upgradeRow-'+newId+'" style="display:none">';
    html += '<div class="upgrade">';
    var first_name='';
    $.each(attributes, function (index, value) {
        var upgradeCount = 1;
        html += '<div class="upgradeDiv">';
        html += '<div class="col-md-3 text-right">';
        html += '<label class="form-control-label"> '+ value.upgrade_name +' : </label>';
        html += '</div>';
        html += '<div class="col-md-7">';
        html += '<div class="p-l-0 m-b-10">';
        html += '<div class="input-group">';

        if(value.is_multi == 1){
            html += '<select name="item['+newId+'][upgrade]['+value.upgrade_id+'][]" class="form-control upgrades overFloW" data-name="upgrade['+value.upgrade_id+']" data-upgrade-id="'+value.upgrade_id+'" multiple data-rawId="'+newId+'" data-count="'+upgradeCount+'" data-isMulti="'+value.is_multi+'" data-minSelect="'+value.required+'" data-maxSelect="'+value.max_required+'" >';
        }else{
            html += '<select name="item['+newId+'][upgrade]['+value.upgrade_id+']['+(upgradeCount)+']" class="form-control upgrades overFloW" data-name="upgrade['+value.upgrade_id+']['+upgradeCount+']" data-upgrade-id="'+value.upgrade_id+'" data-rawId="'+newId+'" data-count="'+upgradeCount+'" data-isMulti="'+value.is_multi+'">';
        }
            
        $.each(value.options, function (k, v) {
            html += '<option value="'+v.option_id+'">'+v.option_name+'</option>';
        });
        html += '</select>';
        html += '</div></div></div>';
        html += '<div class="clearfix"></div>';
        
        //No Need Now , but do not remove this comment
        /*if(!$.isEmptyObject(value.sub)){
            $.each(value.sub, function (index, options) {
                upgradeCount += 1;
                html += '<div class="optionDiv">';
                html += '<div class="col-md-3 text-right">';
                html += '<label class="form-control-label">'+ options.option_name + ' Option : </label>';
                html += '</div>';
                html += '<div class="col-md-7">';
                html += '<div class="p-l-0 m-b-10"><div class="input-group">';
                html += '<select name="item['+newId+'][upgrade]['+value.upgrade_id+']['+(upgradeCount)+']" class="form-control upgradeItems attr__ROW_ID__'+value.upgrade_id+' overFloW" data-name="upgrade['+value.upgrade_id+']['+upgradeCount+']" data-upgrade-id="'+value.upgrade_id+'" data-rawId="'+newId+'">';
                $.each(options.sub_options, function (k, v) {
                    html += '<option value="'+index+'_'+v.option_id+'">'+v.option_name+'</option>';
                });
                html += '</select>';
                html += '</div></div></div></div>';
                html += '<div class="clearfix"></div>';
            });
        }*/
        html +='</div>';
    });
    html +='</div></div>';
    return html;
}
//Generate Product Sub Details
function generateProductSubDetail(product,e,newId,productObject,agentId){

	var productDetailDiv = $('.product-detail-div-'+newId),
 	    product_line = (e.params.data.product_line).substr(0, 1).toUpperCase() + (e.params.data.product_line).substr(1),
 	    isRoomButtonDis = false,
 	    priceHtml = '';

	productDetailDiv.show();
	
	productDetailDiv.find('.txt-product-line').val(e.params.data.product_line);
	productDetailDiv.find('.product-label').html(product_line +' Product :');
	productDetailDiv.find('.product-name').html(e.params.data.name);
	productDetailDiv.find('.product_id').val(e.params.data.product_id);
	productDetailDiv.find('.productDetail').attr('data-duration',product.basicInfo.duration_value);
	productDetailDiv.find('.productDetail').attr('data-durationType',product.basicInfo.duration_unit);
	productDetailDiv.find('.productDetail').attr('data-product_id',e.params.data.product_id);
	productDetailDiv.find('.productDetail').attr('data-product_line',e.params.data.product_line);
	productDetailDiv.find('.productDetail').attr('data-product_price_model',product.basicInfo.product_price_model);
	productDetailDiv.find('.productDetail').attr('data-max_guest_number',product.basicInfo.max_guest_number);

	// room detail
	if(!$.isEmptyObject(product.rates)){

		// if(product.basicInfo.product_price_model == 'person' && e.params.data.product_line == 'tour'){
		// 	isRoomButtonDis = true;
		// }
		// productDetailDiv.append(roomDetail(product.rates,newId,isRoomButtonDis).replace(/_ID_/g,1));

        // start set default rates
        firstTimeRateArray = product.rates
        // end set default rates
	}
	//departure & location
	if(!$.isEmptyObject(product.departureInfo)){
		productDetailDiv.append(departureDetail(product.departureInfo,newId,agentId));
	}

    //upgrade
    if(!$.isEmptyObject(product.attributes)){
        productDetailDiv.append(upgradeDetail(product.attributes,newId,agentId));
    }
    
    //Price Section
    priceHtml +='<div class="priceRow-'+newId+'" style="display:none">';
    priceHtml +='<div class="col-sm-12 sub-total-div">';
    priceHtml +='<div class="pull-left"><span>SubTotal</span></div><div class="pull-right sub-amount">';
    priceHtml +='<div class="amount">';
    priceHtml +='<i class="icofont icofont-cur-dollar"></i><span class="pull-right sub-price">0.00</span>';
    priceHtml +='</div>';
    priceHtml +='<div class="preloader3 customLoader">';
    priceHtml +='<div class="circ1 bg-default cusCircle"></div>';
    priceHtml +='<div class="circ2 bg-default cusCircle"></div>';
    priceHtml +='<div class="circ3 bg-default cusCircle"></div>';
    priceHtml +='</div>';

    priceHtml +='</div>';
    priceHtml +='</div>';
    priceHtml +='</div>';

    productDetailDiv.append(priceHtml);
}
// Generate Passenger as per room wise
function mergeRoomInAtt(passengerAttributes){
    var allAttributes = $('.parentProduct').map(function(){
        return $(this).data('row-id');
    }).get();
    $.each(allAttributes,function(k,value){
        var totalForm = 0;
        var product_line = $('.product-detail-div-'+value).find('.productDetail').attr('data-product_line');

        if(product_line == 'ttd') {
            $('.skuRatesInfo-'+value).find('input[name^="item['+value+'][rate_options]"].rate').each(function(index,rate) {
                totalForm = parseInt(totalForm) + ( parseInt($(this).val()) * parseInt($(this).attr('data-qty_count')) );
            });
        } else {
            $('.roomRow-'+value).find('input[name^="item['+value+'][rate_options]"].rate').each(function(index,rate) {
                totalForm = parseInt(totalForm) + ( parseInt($(this).val()) * parseInt($(this).attr('data-qty_count')) );
            });
        }
                
        if(typeof passengerAttributes[value] !== "undefined"){
            passengerAttributes[value]['total'] = totalForm;
        }
    });
    return passengerAttributes;
}

function getOptions(product_id, rawId) {
    $.ajax({
        method: "POST",
        dataType: 'json',
        async: false,
        url: '/producteditor/get-option-details/' + product_id + '/' + agentId,
        data: dataObj,
        beforeSend: function() {
            $('.priceRow-' + rawId).find('.amount').hide();
            $('.priceRow-' + rawId).find('.customLoader').show();
        },
        success: function(response) {
            if (response.code != 200) {
                if ($.type(response.message) == 'string') {
                    notify(response.message, "danger");
                } else {
                    $.each(response.message, function(k, v) {
                        notify(v, "danger");
                    });
                }

            } else {

                // Update upgrades...
                var upgrades = response.option_detail;

                if (response.option_detail.start_time[0].value != "undefined" && response.option_detail.start_time[0].value != "") {
                    startTime = response.option_detail.start_time[0].value;
                }
                if (!$.isEmptyObject(upgrades)) {
                    var upgradeDiv = $(".upgradeRow-1");
                    $(".upgradeRow-1").html("");
                    // upgradeDiv.innerHtml="";
                    upgradeDiv.append(upgradeDetail(upgrades, rawId, agentId));
                    $('select[multiple]').multiselect();

                    //upgrade details
                    /*  for(var cnt=0;cnt <selectValues.length;cnt++)
                      {
                          $('.product-detail-div-'+rawId).find('.upgradeRow-'+rawId).find('.upgrades')[cnt].value=selectValues[cnt];
                                
                          
                      }*/
                } else {
                    $(".upgradeRow-1").html("");
                }

                //Departure Information update
                var departure = response.price_detail.departure_info;
                selectDrop.empty();

                if (departure != '') {
                    if (typeof departure == 'string') {
                        $(selectDrop).append($('<option>', {
                            value: departure,
                            text: departure
                        }));
                    } else {
                        $.each(departure, function(key, value) {
                            $(selectDrop).append($('<option>', {
                                value: value,
                                text: value
                            }));
                        });
                    }
                }

                $('.priceRow-' + rawId).find('.sub-total-div > .sub-amount > .amount > .sub-price').html(response.price_detail.price.net_total);
                priceArray[rawId]['price'] = response.price_detail.price.net_total;
                //finalProductTotal();
            }
        }
    });
}
//get Price Function
function getPrice(product_id,rawId,onchangeupgrade = 0,onlyUpgradeChange=0,rateChange=0,isTtdProduct=0,passStartTime=0){

    var dataObj = {};
    dataObj['_token'] = token;
    //departure date
    dataObj['departure_date'] = $('.dateRow-'+rawId).find('input[name^="item['+rawId+'][departure_date]"]').val();
    //room details
    dataObj['product_id'] = $('.product-detail-div-'+rawId).find('.productDetail').attr('data-product_id');

    dataObj['upgrade']=[[]];

    if(passStartTime == 1) {
        $(".roomRow-1").remove();
    }
    dataObj['api_source']=api_source;

    selectValues = new Array();

    if(api_source != ''){
        dataObj['start_time'] = $('.departureRow-1'+rawId).find('select[name^="item['+rawId+'][start_time]"]').val();
    }

    var product_line = $('.product-detail-div-'+rawId).find('.productDetail').attr('data-product_line');

    if(product_line == 'ttd') {
        $('.skuRatesInfo-'+rawId).find('input[name^="item['+rawId+'][rate_options]"].rate').each(function(index,rate) {
            dataObj[$(this).attr('data-name')] = $(this).val();
        });
    }

    //departure dropdown Object
    var selectDrop = $('.product-detail-div-'+rawId).find('.departureRow-'+rawId).find('.departure');
    if(product_id != 'undefined'){
           var agentId = $('#agent_id').val();
            dataObj['agent_id']=agentId;// $('#agent_id').val(); //'5000002';
            
            
            if (product_line != 'ttd') {
                if (rateChange == 0) {
                    $('.roomRow-'+rawId).find('input[name^="item['+rawId+'][rate_options]"].rate').each(function(index,rate) {
                        dataObj[$(this).attr('data-name')] = $(this).val();
                    });
                } else {
                    if(api_source == 'fareharbor'){
                        $.each(productRateArray, function (index, value) {
                            var keyDefaultRate      = "rate_options[1]["+value.rate_id+"]";
                            dataObj[keyDefaultRate] = value.min_range;
                        });
                    }else{
                        $.each(firstTimeRateArray, function (index, value) {
                            var keyDefaultRate      = "rate_options[1]["+value.product_rate_type_id+"]";
                            dataObj[keyDefaultRate] = value.min_range;
                        });    
                    }
                    
                }
            }
            if (onchangeupgrade  == 0) {
                getOptinsFromAPI(product_id,agentId,dataObj,rawId,selectValues,0,onlyUpgradeChange,rateChange,passStartTime);
            }
            else {
                getOptinsFromAPI(product_id,agentId,dataObj,rawId,selectValues,1,onlyUpgradeChange,rateChange,passStartTime);
            }

            $('.upgradeRow-'+rawId).find('select[name^="item['+rawId+'][upgrade]"]').each(function(index,rate) {

                if($(this).data('name') !== undefined && $(this).val() != ''){

                    if(typeof dataObj['upgrade'] == "undefined"){
                        dataObj['upgrade'] = [[]];
                    }

                    var getoptionsVals = getOptionId($(this).val());
                    
                    if(Array.isArray(getoptionsVals)){
                        dataObj['upgrade['+$(this).data('upgrade-id')+']'] = getOptionId($(this).val());
                    }else{
                        dataObj['upgrade['+$(this).data('upgrade-id')+']['+(index+1)+']'] = getoptionsVals;
                    }
                    selectValues.push(getOptionId($(this).val()));
                }

            });

            if(product_line == 'ttd') {
                $('.skuRatesInfo-'+rawId).find('input[name^="item['+rawId+'][rate_options]"].rate').each(function(index,rate) {
                    dataObj[$(this).attr('data-name')] = $(this).val();
                });
            } else {
                var adultRateTypeId = '';
                
                $.each(productRateArray, function (index, value) {
                    if (value.name == 'Adults' || productRateArray.length == 1) {
                        return adultRateTypeId = value.rate_id;
                    }
                });

                $('.roomRow-'+rawId).find('input[name^="item['+rawId+'][rate_options]"].rate').each(function(index,rate) {
                    if (adultRateTypeId == $(this).attr('data-key') && $(this).val() == 0 && api_source == 'fareharbor') {
                        
                        var minBooking = productPriceArray.min_booking_size;
                        if (minBooking == undefined || minBooking == 0) {
                            var minBooking = 1;
                        }

                        dataObj[$(this).attr('data-name')] = minBooking;
                        var attributesKey = $(this).attr('data-key');
                        $('input[name="item[1][rate_options][1]['+attributesKey+'][qty]').val(minBooking);

                    }else{

                        dataObj[$(this).attr('data-name')] = $(this).val();
                        
                    }
                    //dataObj[$(this).attr('data-name')] = $(this).val();
                });
            }

            if (startTime != "0" && rateChange == 1) {
                dataObj['start_time']=startTime;// $('#agent_id').val(); //'5000002';
            }

            if(isTtdProduct != 1) {
                $.ajax({
                    method: "POST",
                    dataType: 'json',
                    url: '/producteditor/get-net-price/'+product_id+'/'+agentId,
                    data: dataObj,
                    beforeSend: function () {
                        $('.priceRow-'+rawId).find('.amount').hide();
                        $('.priceRow-'+rawId).find('.customLoader').show();
                    },
                    success: function(response) {
                        $('.priceRow-'+rawId).find('.amount').show();
                        $('.priceRow-'+rawId).find('.customLoader').hide();
                        if(response.code != 200){
                            if($.type(response.message) == 'string'){
                                notify(response.message,"danger");
                            }else{
                                $.each(response.message,function(k,v){
                                    notify(v,"danger");
                                });
                            }
                                                
                            $('.priceRow-'+rawId).find('.sub-total-div > .sub-amount > .amount > .sub-price').html('0.00');
                        }else{
                                            
                                            // Update upgrades...
                            var priceDetails = response.price_detail;
                            if(!$.isEmptyObject(priceDetails))
                            {
                                              
                               $('.priceRow-'+rawId).find('.sub-total-div > .sub-amount > .amount > .sub-price').html(response.price_detail.price.net_total);
                                   priceArray[rawId]['price'] = response.price_detail.price.net_total;
                               //finalProductTotal();
                            }

                            if(product_line == 'ttd') {
                                var priceRates = response.price_detail.rates;
                                if(priceRates != '') {
                                    $.each(priceRates, function(priceRatesKey, priceRatesValue){
                                        if(priceRatesValue.selected_rates != '') {
                                            $.each(priceRatesValue.selected_rates, function(selPriceRateKey, selPriceRateValue){
                                                $(".selectedSkuPriceRatesInfo-"+selPriceRateValue.rate_id).html('$'+selPriceRateValue.net_price);
                                            });
                                        }
                                    });
                                }
                            }
                        }
                    }
                });
            }
            
    }
}

function getOptinsFromAPI(product_id, agentId, dataObj, rawId, selectValues, onchangeupgrade = 0, onlyUpgradeChange = 0,rateChange = 0,passStartTime = 0) {
    
    $('.roomRow-1').show();
    
    $.ajax({
        method: "POST",
        dataType: 'json',
        async: false,
        url: '/producteditor/get-option-details/' + product_id + '/' + agentId,
        data: dataObj,
        beforeSend: function () {
            $(".loading").show();
        },
        success: function(responseData) {
            $(".loading").hide();
            if (responseData.code != 200) {
                if ($.type(responseData.message) == 'string') {
                    notify(responseData.message, "danger");
                } else {
                    $.each(responseData.message, function(k, v) {
                        notify(v, "danger");
                    });
                }

                return null;
            } else {
                productRateArray = responseData.option_detail.rates;
                productPriceArray = responseData.option_detail.price;

                //Update Passenger Attributes Based on Get-Options
                passengerAttributes[rawId]['attributes'] = responseData.option_detail.passengerAttributes;

                var upgradesOptions = responseData.option_detail.attributes;
                if (responseData.option_detail.start_time != null && responseData.option_detail.start_time.length > 0) {
                    if (responseData.option_detail.start_time[0].value != "undefined" && responseData.option_detail.start_time[0].value != "") {
                        startTime = responseData.option_detail.start_time[0].value;
                    }
                }

                if(rateChange == 1 || passStartTime == 1) {
                    var isRoomButtonDis = false;
                    rateOptions[rawId] = responseData.option_detail.rates;
                    var productDiv          = $('.product-detail-div-'+rawId);
                    var productDetailDiv    = $('.product-detail-div-'+rawId+' .dateRow-1');
                    var product_line        = productDiv.find('.productDetail').attr('data-product_line');
                    var product_price_model = productDiv.find('.productDetail').attr('data-product_price_model');
                    
                    if(product_line == 'ttd') {
                        $(".skuRatesInfo-1").remove();
                        $(".skuRoomRow-1").remove();
                        productDetailDiv.append(skuInfo(responseData.option_detail.rates,rawId).replace(/_ID_/g,1));
                        $('.skuRoomRow-1').show();

                        $.each(responseData.option_detail.rates, function(index, rateInfo){
                            availableSkuInfo[rateInfo.rate_id] = rateInfo;
                        });

                    } else {
                        $.each(responseData.option_detail.rates, function(index, rateInfo){
                            availableRateInfo[rateInfo.rate_id] = rateInfo;
                        });
                        
                        $(".roomRow-1").html("");
                        if(product_price_model == 'person' && product_line == 'tour'){
                            isRoomButtonDis = true;
                        }
                        
                        productDetailDiv.append(roomDetail(responseData.option_detail.rates,rawId,isRoomButtonDis).replace(/_ID_/g,1));
                        $('.roomRow-1').show();
                    }
                    
                }


                if (!$.isEmptyObject(upgradesOptions)) {
                    var upgradeDiv = $(".upgradeRow-1");

                    if (upgradeDiv.length == 0) {
                        $('<div class="upgradeRow-' + rawId + '"><div>').insertBefore($('.product-detail-div-' + rawId).find('.priceRow-' + rawId));
                        upgradeDiv = $(".upgradeRow-1");
                    }

                    if (onchangeupgrade == 0) {
                        $(".upgradeRow-1").html("");
                        upgradeDiv.append(upgradeDetailWithOption(upgradesOptions, rawId, agentId));
                        $('select[multiple]').multiselect();
                    }
                    
                } else {
                    $(".upgradeRow-1").html("");
                }

                if(onlyUpgradeChange != 1){

                    var selectDrop = $('.product-detail-div-' + rawId).find('.departureRow-' + rawId).find('.departure');
                    var selectDropForTime = $('.product-detail-div-' + rawId).find('.departureRow-11').find('.departure');
                    var product_line = $('.product-detail-div-'+rawId).find('.productDetail').attr('data-product_line');

                    //Departure Information update
                    var departure = responseData.option_detail.pickup_points;
                    var departureEndLocation = responseData.option_detail.end_locations;
                    var departureTimes = responseData.option_detail.start_time;


                    selectDrop.empty();
                    var newId = $('.orderProductDiv').find('.well-sm:last').attr('data-row-id');
                    passengerAttributes[newId]['attributes'] = responseData.option_detail.passengerAttributes;
                    //rateOptions[newId] = responseData.option_detail.rates;

                    var firstTimeDetails = (departureTimes[0] != "undefined" && departureTimes[0] != "") ? departureTimes[0] : '';

                    if (onchangeupgrade == 0) {

                        $('.departureRow-1').show();
                        $('.departureRow-11').show();


                        if(product_line != 'ttd') {

                            $("#pickup_location").val("").attr('readonly','readonly');
                            $('.departure_location').html('');
                            $('.departure_location').data('pickup',responseData.option_detail.pickup_on_request);
                            $("#pickup_on_request").val(responseData.option_detail.pickup_on_request);
                            if(responseData.option_detail.pickup_on_request == 1){
                                $.each(customPickup, function(key, value) {
                                    $('.departure_location').append($('<option>', {
                                        value: value,
                                        text: value,
                                        info : key
                                    }));
                                });
                            }else if (departure != ''){
                                $.each(departure, function(key, value) {

                                    var address     = (value.address1 != null || value.address1 != undefined) ? value.address1 : '';
                                    var pickup_key  = (value.pickup_key != null || value.pickup_key != undefined) ? value.pickup_key : '';
                                    var pickup_name = (value.pickup_name != null || value.pickup_name != undefined) ? value.pickup_name : '';
                                    var pickup_text = pickup_name+', '+address;

                                    if(firstTimeDetails != '' && firstTimeDetails != 'undefined' && firstTimeDetails != null){
                                        if(firstTimeDetails['value'] != value.time && api_source == '') {
                                            $('.departure_location').append($('<option>', {
                                                value: pickup_key,
                                                text: pickup_text,
                                                'data-time': value.time,
                                                'disabled':'disabled'
                                            }));
                                        }else{
                                            $('.departure_location').append($('<option>', {
                                                value: pickup_key,
                                                text: pickup_text,
                                                'data-time': value.time,
                                            }));
                                        }
                                    } else {
                                        $('.pickupRow-1').hide();
                                        $('.departure_location').append($('<option>', {
                                            value: pickup_key,
                                            text: pickup_key,
                                            'data-time': value.time,
                                        }));
                                    }
                                });
                            } else {
                                $('.pickupRow-1').hide();
                                $('.departureRow-1').hide();
                            }

                            $('.departureEndLocationRow-1').show();
                            if (departureEndLocation != '' && responseData.option_detail.end_location_on_request == 0) {
                                $('.departureEndLocationOnRequestRow-1').remove();
                                $('.end_location').html('');
                                $.each(departureEndLocation, function(key, value) {

                                    var address = (value.address1 != null || value.address1 != undefined) ? value.address1 : '';
                                    var pickup_name = (value.end_location_name != null || value.end_location_name != undefined) ? value.end_location_name : '';
                                    var time = (value.time != null || value.time != undefined) ? value.time : '';
                                    var conacatBy = '';
                                    if (pickup_name && address) {
                                        conacatBy = ', ';
                                    }
                                    $('.end_location').append($('<option>', {
                                        value: time + ' At ' + pickup_name + conacatBy + address,
                                        text: time + ' At ' + pickup_name + conacatBy + address
                                    }));
                                });
                            } else {
                                $('.departureEndLocationRow-1').hide();
                            }

                            if (responseData.option_detail.end_location_on_request != 0) {
                                $('.departureEndLocationRow-1').remove();
                                $('.departureEndLocationOnRequestRow-1').show();
                            } else {
                                $('.departureEndLocationOnRequestRow-1').hide();
                            }
                        }

                        if(product_line == 'ttd') {

                            if (departure != '' && responseData.option_detail.pickup_on_request == 0) {
                                $('.departureOnRequestRow-1').remove();
                                $('.departure_location').html('');
                                $.each(departure, function(key, value) {

                                    var address = (value.address1 != null || value.address1 != undefined) ? value.address1 : '';
                                    var pickup_name = (value.pickup_name != null || value.pickup_name != undefined) ? value.pickup_name : '';
                                    var time = (value.time != null || value.time != undefined) ? value.time : '';
                                    var conacatBy = '';
                                    if (pickup_name && address) {
                                        conacatBy = ', ';
                                    }
                                    $('.departure_location').append($('<option>', {
                                        value: ((time) ? (time + ' At ') : "" ) + pickup_name + conacatBy + address,
                                        text: ((time) ? (time + ' At ') : "" ) + pickup_name + conacatBy + address
                                        // text: time + ' At ' + pickup_name + conacatBy + address
                                    }));
                                });
                            } else {
                                $('.departureRow-1').hide();
                            }

                            $('.departureEndLocationRow-1').show();
                            if (departureEndLocation != '' && responseData.option_detail.end_location_on_request == 0) {
                                $('.departureEndLocationOnRequestRow-1').remove();
                                $('.end_location').html('');
                                $.each(departureEndLocation, function(key, value) {

                                    var address = (value.address1 != null || value.address1 != undefined) ? value.address1 : '';
                                    var pickup_name = (value.end_location_name != null || value.end_location_name != undefined) ? value.end_location_name : '';
                                    var time = (value.time != null || value.time != undefined) ? value.time : '';
                                    var conacatBy = '';
                                    if (pickup_name && address) {
                                        conacatBy = ', ';
                                    }
                                    $('.end_location').append($('<option>', {
                                        value: time + ' At ' + pickup_name + conacatBy + address,
                                        text: time + ' At ' + pickup_name + conacatBy + address
                                    }));
                                });
                            } else {
                                $('.departureEndLocationRow-1').hide();
                            }

                            if (responseData.option_detail.end_location_on_request != 0) {
                                $('.departureEndLocationRow-1').remove();
                                $('.departureEndLocationOnRequestRow-1').show();
                            } else {
                                $('.departureEndLocationOnRequestRow-1').hide();
                            }

                            if (responseData.option_detail.pickup_on_request != 0) {
                                $('.departureRow-1').remove();
                                $('.departureOnRequestRow-1').show();
                            } else {
                                $('.departureOnRequestRow-1').hide();
                            }
                        }

                        if (departureTimes != '') {
                            $('.start_time').html('');
                            $.each(departureTimes, function(key, value) {
                                var key = (value.value != null || value.value != undefined) ? value.value : '';
                                var text = (value.text != null || value.text != undefined) ? value.text : '';
                                
                                 if(dataObj['start_time'] != null || dataObj['start_time'] != undefined) {
                                    if(dataObj['start_time'] == key) {
                                         $(selectDropForTime).append($('<option value="'+key+'" selected="selected">'+text+'</option>'));
                                         } else {
                                        $(selectDropForTime).append($('<option value="'+key+'">'+text+'</option>'));
                                        }
                                } else {
                                    $(selectDropForTime).append($('<option value="'+key+'">'+text+'</option>'));
                                }   
                            });
                        } else {
                            $('.start_time').html('');
                            $('.departureRow-11').hide();
                        }
                        //product leval additional field
                        if(product_line == 'ttd') {
                            $(".extFieldRow-"+rawId+"").remove();
                            if (!$.isEmptyObject(responseData.option_detail.product_addi_fields)) {
                                 additionalInfoDetail(responseData.option_detail.product_addi_fields,rawId);
                            }
                        }
                    }
                }
            }
        }
    });
}

//[Jaydip_20190605] [Task_3993] TTD Product level additional fields.
function additionalInfoDetail(additionalInfo,rawId)
{
    if (!$.isEmptyObject(additionalInfo)) {
        $(".extFieldRow-"+rawId+"").remove();
        $('<div class="extFieldRow-' + rawId + '"><div class="extField-'+rawId+'"><div class="col-md-3 text-right"><h5>Additional Information</h5></div><div class="col-md-8 text-left"></div></div><div class="clearfix"></div></div>').insertBefore($('.product-detail-div-' + rawId).find('.priceRow-' + rawId));
        $.each(additionalInfo, function(key, value) {
            if(value.type) {
                var html = '';
                html += '<div class="extField-'+rawId+'">';
                html += '<div class="col-md-3 text-right">';
                html += '<label class="col-form-label form-control-label">'+value.label+' :</label>';
                html += '</div>';
                html += '<div class="col-md-8 text-left">';
                html += '<div class="form-group">';
                if(value.type == 'radio' || value.type == 'select') {
                    html += '<select name="item['+rawId+'][additional_info]['+value.name+']" class="form-control additional_info" data-name="'+value.label+'" data-rawId="'+rawId+'">';
                    if (!$.isEmptyObject(value.options)) {
                        $.each(value.options, function(optionKey, optionvalue) {
                            html += '<option value="'+optionvalue+'">'+optionvalue+'</option>';
                        });
                    }
                    html += '</select>';
                } else if(value.type == 'time') {
                    html +='<input type="text" name="item['+rawId+'][additional_info]['+value.name+']" class="form-control additional_info additional_product_level_time" data-name="'+value.label+'" data-rawId="'+rawId+'">';
                } else if(value.type == 'number') {
                    html +='<input type="'+value.type+'" name="item['+rawId+'][additional_info]['+value.name+']" class="form-control additional_info" data-name="'+value.label+'" data-rawId="'+rawId+'" onkeydown="return event.keyCode !== 69">';
                } else if(value.type != 'checkbox') {
                    html +='<input type="'+value.type+'" name="item['+rawId+'][additional_info]['+value.name+']" class="form-control additional_info" data-name="'+value.label+'" data-rawId="'+rawId+'">';
                } else if(value.type == 'checkbox') {
                    html += '<select multiple="multiple" data-maximum-selection-length="'+value.max_value+'" addi_id="'+value.name+'"" class="form-control additional-multi-select" data-name="" data-rawId="'+rawId+'">';
                    if (!$.isEmptyObject(value.options)) {
                        $.each(value.options, function(optionKey, optionvalue) {
                            html += '<option value="'+optionvalue+'">'+optionvalue+'</option>';
                        });
                    }
                    html += '</select>';
                    html +='<input type="hidden" data-name="'+value.label+'" minimum-select="'+value.min_value+'" name="item['+rawId+'][additional_info]['+value.name+']" class="additional-select-'+value.name+' additional-product-level additional_info">';
                }
                html +='</div></div></div><div class="clearfix"></div>';

                $(".extFieldRow-"+rawId+"").append(html);
            }
        });
        $('.additional-multi-select').select2();
        $('.additional_product_level_time').datetimepicker({
            format: 'HH:mm',
            icons: {
                up: "icofont icofont-rounded-up",
                down: "icofont icofont-rounded-down"
            }
        });
    }
}

//function which get Departure Date in Calender
function fetchDate(product_id){
    $.ajax({
        method: "POST",
        dataType: 'json',
        url: '/booking/fetch-departure-dates',
        data: {
          product_id:product_id,
          _token : token,
          departure_start_date : month_first_date,
          departure_end_date : month_end_date,
        },
        beforeSend: function () {
            $(".loading").show();
        },
        success: function(response) {

            calendars.clndr2.setEvents(response);
            if( typeof response[0] != "undefined" && typeof response[0].startDate != "undefined"){
                //calendars.clndr2.options.constraints.startDate = response[0].startDate;
                var date = response[0].startDate.split("-");
                    calendars.clndr2.setYear(date[0]);
                    calendars.clndr2.setMonth(parseInt(date[1]) - 1);
    
            }
            $(".loading").hide();
        }
    });
}
// generate guest Form
function generateGuestForm(passenger_attr) {
    var dynamicForm = '';
    var identifier = new Date().getTime();
    var selectRateOptions =[];
    $.each(passenger_attr, function (newId, product_attr) {
        
        var product_line = $('.product-detail-div-'+newId).find('.productDetail').attr('data-product_line');

        if(product_line == 'ttd') {
            $('.skuRatesInfo-'+newId).find('input[name^="item['+newId+'][rate_options]"].rate').each(function(index,rate) {
                if (!($(this).data('key') in selectRateOptions)) {
                    selectRateOptions[$(this).data('key')] = parseInt($(this).val());
                } else {
                    selectRateOptions[$(this).data('key')] += parseInt($(this).val());
                }
            });
        } else {
            $('.roomRow-'+newId).find('input[name^="item['+newId+'][rate_options]"].rate').each(function(index,rate) {
                if (!($(this).data('key') in selectRateOptions)) {
                    selectRateOptions[$(this).data('key')] = parseInt($(this).val());
                } else {
                    selectRateOptions[$(this).data('key')] += parseInt($(this).val());
                }
            });
        }

        if(product_attr.lead_traveller == 1){
            dynamicForm += '<div class="col-md-12 form-group text-left"><label class="control-label col-sm-8">';
        } else {
            dynamicForm += '<div class="col-md-12 form-group text-left"><label class="control-label col-sm-12">';
        }
        dynamicForm += '<b>'+product_attr.product_name+'</b>';
        dynamicForm += '</label>';
        if(product_attr.lead_traveller == 1) {
            ++identifier;
            dynamicForm += '<div class="pull-left select-user-passengers-btn">';
            dynamicForm += '<label class="control-label col-md-2"><a href="javascript:;" class="text-primary font-italic guest_contact J-customer-contact" data-type="1" id="frequent_contact">Customer</a></label>';
            dynamicForm += '</div>';
            dynamicForm += '<label class="input-checkbox checkbox-primary col-md-2">';
            dynamicForm += '<input type="checkbox" name="item['+newId+'][guest]['+identifier+'][is_frequent_contact]" value="1" checked>';
            dynamicForm += '<span class="checkbox"></span>';
            dynamicForm += 'Save</label></div>';
            dynamicForm += '</div>';
        }
        dynamicForm += '</div>';

        if($.isEmptyObject(product_attr.attributes)){
            dynamicForm += '<div class="col-sm-10 text-left m-l-30"> There is no passenger attributes </div>';
        }else{
             var travelerId = 1;
             
             //product_attr.lead_traveller = 0;
            if(product_attr.lead_traveller == 1){
                var countInc = 1;
                for(var rate in selectRateOptions) {
                    for(var i=1;i<=selectRateOptions[rate];i++) {
                        if(countInc == 1){
                            countInc++;
                            var count = 1;
                            var displayFields = {};
                                    
                            $.each(product_attr.attributes, function (prKey, value) {
        
                                    var attributeFor = value.use_for[0];
                                    var isDisplay = false;
                                    if(attributeFor == 'ALL'){
                                        isDisplay = true;
                                    }else if(rate == attributeFor){
                                        isDisplay = true;
                                    }
                                    if(isDisplay){
                                        displayFields[prKey] = value;
                                    }
                                
                            });

                            var totalElement = Object.keys(displayFields).length;
                            var incrementElement  = 1;

                            $.each(displayFields, function (prKey, value) {
                                if(count % 2 === 1){
                                    dynamicForm += '<div class="row" id="guest_id_1">';
                                }

                                if (value.type == 'text' && (value.label != 'Phone' && value.name != 'phone' && value.name != 'mobile') && value.name != 'weight') {
                                    dynamicForm += '<div class="col-md-6">';
                                    dynamicForm += '<div class="form-group row">';
                                    var tempTravelerTag ="";
                                    if(value.label == 'First name')
                                    {
                                        tempTravelerTag=' [Traveler '+travelerId + '] '
                                        travelerId++;
                                    }

                                    if(value.label == 'Passport') {
                                        dynamicForm += '<label class="control-label col-md-4 text-right">'+'Passport Number'+':</label>';
                                    } else {
                                        dynamicForm += '<label class="control-label col-md-4 text-right">'+ tempTravelerTag +value.label+':</label>';
                                    }
                                    dynamicForm += '<div class="col-md-8">';
                                    dynamicForm += '<input class="form-control" data-value="'+encodeFromInput(value.label)+'" name=item['+newId+'][guest]['+identifier+'][' + encodeFromInput(value.name) + '] type="text">';
                                    dynamicForm += '</div>';
                                    dynamicForm += '</div>';
                                    dynamicForm += '</div>';
                                } else if (value.type == 'date') {
                                    dynamicForm += '<div class="col-md-6">';
                                    dynamicForm += '<div class="form-group row">';
                                    dynamicForm += '<label class="control-label col-md-4 text-right">'+value.label+':</label>';
                                    dynamicForm += '<div class="col-md-8">';
                                    dynamicForm += '<input class="form-control" rateId="'+value.label+'" for="'+rate+'" data-value="'+encodeFromInput(value.label)+'" name=item['+newId+'][guest]['+identifier+'][' + encodeFromInput(value.name) + '] type="date">';
                                    dynamicForm += '</div>';
                                    dynamicForm += '</div>';
                                    dynamicForm += '</div>';
                                } else if (value.type == 'select') {
                                    var options = value.options;
                                    dynamicForm += '<div class="col-md-6">';
                                    dynamicForm += '<div class="form-group row">';
                                    dynamicForm += '<label class="control-label col-md-4 text-right">'+value.label+':</label>';
                                    dynamicForm += '<div class="col-md-8">';
                                    dynamicForm += '<select data-value="'+encodeFromInput(value.label)+'" class="form-control" name=item['+newId+'][guest]['+identifier+'][' + encodeFromInput(value.name) + ']>';
                                    $.each(options, function (key, op) {
                                        dynamicForm += '<option class="form-control" value="' + op + '">' + op + '</option>';
                                    });
                                    dynamicForm += '</select>';
                                    dynamicForm += '</div>';
                                    dynamicForm += '</div>';
                                    dynamicForm += '</div>';
                                }else if (value.type == 'list') {
                                    var options = value.options;
                                    dynamicForm += '<div class="col-md-6">';
                                    dynamicForm += '<div class="form-group row">';
                                    dynamicForm += '<label class="control-label col-md-4 text-right">'+value.label+':</label>';
                                    dynamicForm += '<div class="col-md-8">';
                                    dynamicForm += '<select data-value="'+encodeFromInput(value.label)+'" class="form-control" name=item['+newId+'][guest]['+identifier+'][' + encodeFromInput(value.name) + ']>';
                                    $.each(options, function (key, op) {
                                        dynamicForm += '<option class="form-control" value="' + op + '">' + op + '</option>';
                                    });
                                    dynamicForm += '</select>';
                                    dynamicForm += '</div>';
                                    dynamicForm += '</div>';
                                    dynamicForm += '</div>';
                                } else if (value.type == 'radio') {
                                    var options = value.options;
                                    dynamicForm += '<div class="col-md-6">';
                                    dynamicForm += '<div class="form-group row">';
                                    dynamicForm += '<label class="control-label col-md-4 text-right">'+ value.label +':</label>';
                                    dynamicForm += '<div class="col-md-8">';
                                    dynamicForm += '<div class="options">';
                                    $.each(options, function (key, op) {
                                        if(key == 0){
                                            var rchecked = 'checked';
                                        }else{
                                            
                                            var rchecked = '';
                                        }
                                        dynamicForm += '<label class="custom-control custom-radio">';
                                        dynamicForm += '<input '+rchecked+'  data-value="'+encodeFromInput(value.label)+'" class="custom-control-input"  name=item['+newId+'][guest]['+identifier+'][' + encodeFromInput(value.name) + '] type="radio" value="' + op + '">';
                                        dynamicForm += '<span class="custom-control-indicator"></span>';
                                        dynamicForm += '<span class="custom-control-description">' + op + '</span>';
                                        dynamicForm += '</label>';
                                    });
                                    dynamicForm += '</div></div>';
                                    dynamicForm += '</div>';
                                    dynamicForm += '</div>';
                                } else if (value.type == 'text' && value.name == 'weight') {
                                    dynamicForm += '<div class="col-md-6">';
                                    dynamicForm += '<div class="form-group row">';
                                    dynamicForm += '<label class="control-label col-md-4 text-right">'+value.label+':</label>';
                                    dynamicForm += '<div class="col-md-4">';
                                    dynamicForm += '<input class="form-control" data-value="'+encodeFromInput(value.label)+'" name=item['+newId+'][guest]['+identifier+'][' + encodeFromInput(value.name) + '] type="number">';
                                    dynamicForm += '</div>';
                                    dynamicForm += '<div class="col-md-4">';
                                    var weightOption = ['kg', 'pound'];
                                    dynamicForm += '<select data-value="weight_unit" class="form-control" name=item['+newId+'][guest]['+identifier+'][weight_unit] type="select">';
                                    $.each(weightOption, function (key, op) {
                                        dynamicForm += '<option class="form-control" value="' + op + '">' + op + '</option>';
                                    });
                                    dynamicForm += '</select>';
                                    dynamicForm += '</div>';
                                    dynamicForm += '</div>';
                                    dynamicForm += '</div>';
                                } else if(value.label == 'Phone' || value.type == 'phone' || value.name == 'phone' || value.name == 'mobile') {
                                    dynamicForm += '<div class="col-md-6">';
                                    dynamicForm += '<div class="form-group row">';
                                    dynamicForm += '<label class="control-label col-md-4 text-right">'+value.label+':</label>';
                                    dynamicForm += '<div class="col-md-2" style="padding-right: 0">';
                                    dynamicForm += '<select style="padding: 0 5px; height: 35px" data-value="'+encodeFromInput(value.label)+'" class="form-control" name=item['+newId+'][guest]['+identifier+'][country_code]>';
                                    $.each(areaCodeData, function (key, option) {
                                        var optionVal = (option.country_en == null) ? option.country_cn : option.country_en;
                                        var selectedVal = (option.country_code == 1 && optionVal.toLowerCase() == 'usa') ? "selected" : "";
                                        dynamicForm += '<option class="form-control" '+selectedVal+' value="' + option.country_code + '">' + option.country_code+'('+optionVal+') </option>';
                                    });
                                    dynamicForm += '</select>';
                                    dynamicForm += '</div>';
                                    dynamicForm += '<div class="col-md-6">';
                                    dynamicForm += '<input class="form-control" data-value="'+encodeFromInput(value.label)+'" name=item['+newId+'][guest]['+identifier+'][' + encodeFromInput(value.name) + '] type="'+value.type+'">';
                                    dynamicForm += '</div>';
                                    dynamicForm += '</div>';
                                    dynamicForm += '</div>';
                                }
                                if(count % 2 === 0 || (incrementElement === totalElement)){
                                    dynamicForm += '</div>';
                                }
                                ++count;
                                ++incrementElement;
                                
                            });
                            dynamicForm += '<br>';

                        }
                    }
                }

                
            }else{
                var cntSku = 1;
                for(var rate in selectRateOptions) {
                    if(selectRateOptions[rate] == 0){
                        continue;
                    }
                    for(var i=1;i<=selectRateOptions[rate];i++) {

                            var count = 1;
                            var displayFields = {};
                        
                            $.each(product_attr.attributes, function (prKey, value) {
        
                                var attributeFor = value.use_for[0];
                                var isDisplay = false;

                                // if($.inArray(rate,value.use_for) || $.inArray('ALL',value.use_for)){
                                //     isDisplay = true;
                                // }

                                if(attributeFor == 'ALL'){
                                    isDisplay = true;
                                }else if(value.use_for.indexOf(rate) != -1 || value.use_for.indexOf(parseInt(rate)) != -1 ){
                                    isDisplay = true;
                                }
                                
                                if(isDisplay){
                                    displayFields[prKey] = value;
                                }
                                
                            });

                            var totalElement = Object.keys(displayFields).length;
                            var incrementElement  = 1;
                            ++identifier;

                            if(product_line == 'ttd') {
                                $.each(availableSkuInfo, function (aKey, aValue) {
                                    if(rate == aValue.rate_id) {
                                        dynamicForm += '<div class="col-md-12 form-group text-left"><label class="control-label col-sm-4">';
                                        dynamicForm += '<b>Traveler '+cntSku+' ( '+aValue.label+'  )</b>';
                                        dynamicForm += '</label>';
                                        dynamicForm += '<div class="pull-left select-user-passengers-btn">';
                                        dynamicForm += '<label class="control-label col-md-2"><a href="javascript:;" class="text-primary font-italic guest_contact J-customer-contact" data-type='+cntSku+' id="frequent_contact">Customer</a></label>';
                                        dynamicForm += '</div>';
                                        dynamicForm += '<label class="input-checkbox checkbox-primary col-md-2">';
                                        dynamicForm += '<input type="checkbox" name="item['+newId+'][guest]['+identifier+'][is_frequent_contact]" value="1" checked>';
                                        dynamicForm += '<span class="checkbox"></span>';
                                        dynamicForm += 'Save</label></div>';
                                        dynamicForm += '<input class="form-control" data-value="rate_id" value="'+rate+'" name=item['+newId+'][guest]['+identifier+'][rate_id] type="hidden">';
                                    }
                                });
                            }else {
                                $.each(availableRateInfo, function (aKey, aValue) {
                                    if(rate == aValue.rate_id) {
                                        dynamicForm += '<div class="col-md-12 form-group text-left"><label class="control-label col-sm-4">';
                                        dynamicForm += '<b>Traveler '+cntSku+' ( '+aValue.label+'  )</b>';
                                        dynamicForm += '</label>';
                                        dynamicForm += '<div class="pull-left select-user-passengers-btn">';
                                        dynamicForm += '<label class="control-label col-md-2"><a href="javascript:;" class="text-primary font-italic guest_contact J-customer-contact" data-type='+cntSku+' id="frequent_contact">Customer</a></label>';
                                        dynamicForm += '</div>';
                                        dynamicForm += '<label class="input-checkbox checkbox-primary col-md-2">';
                                        dynamicForm += '<input type="checkbox" name="item['+newId+'][guest]['+identifier+'][is_frequent_contact]" value="1" checked>';
                                        dynamicForm += '<span class="checkbox"></span>';
                                        dynamicForm += 'Save</label></div>';
                                        dynamicForm += '</div>';
                                        dynamicForm += '<input class="form-control" data-value="rate_id" value="'+rate+'" name=item['+newId+'][guest]['+identifier+'][rate_id] type="hidden">';
                                    }
                                });
                            }

                            $.each(displayFields, function (prKey, value) {
                                if(count % 2 === 1){
                                    dynamicForm += '<div class="row" id="guest_id_'+cntSku+'">';
                                }
                                
                                if (value.type == 'text' && (value.label != 'Phone' && value.name != 'phone' && value.name != 'mobile') && value.name != 'weight') {
                                    dynamicForm += '<div class="col-md-6">';
                                    dynamicForm += '<div class="form-group row">';
                                    var tempTravelerTag ="";
                                    if(value.label == 'First name')
                                    {
                                        tempTravelerTag=' [Traveler '+travelerId + '] '
                                        travelerId++;
                                    }

                                    if(value.label == 'Passport') {
                                        dynamicForm += '<label class="control-label col-md-4 text-right">'+'Passport Number'+':</label>';
                                    } else {
                                        dynamicForm += '<label class="control-label col-md-4 text-right">'+ tempTravelerTag +value.label+':</label>';
                                    }

                                    dynamicForm += '<div class="col-md-8">';
                                    dynamicForm += '<input class="form-control" data-value="'+encodeFromInput(value.label)+'" name=item['+newId+'][guest]['+identifier+'][' + encodeFromInput(value.name) + '] type="text">';
                                    dynamicForm += '</div>';
                                    dynamicForm += '</div>';
                                    dynamicForm += '</div>';
                                } else if (value.type == 'date') {
                                    dynamicForm += '<div class="col-md-6">';
                                    dynamicForm += '<div class="form-group row">';
                                    dynamicForm += '<label class="control-label col-md-4 text-right">'+value.label+':</label>';
                                    dynamicForm += '<div class="col-md-8">';
                                    if(product_line == 'ttd') {
                                        var dateVal = "";
                                        if (value.name == 'passport_expire' || value.name == 'passport_issue_date') {
                                            var date        =new Date();
                                            var todayDate   = date.getDate();
                                            var month       = date.getMonth()+1;
                                            todayDate       = todayDate.toString().length == 1 ? '0'+todayDate : todayDate;
                                            month           = month.toString().length == 1 ? '0'+month : month;
                                            var currentDate = date.getFullYear()+"-"+month+"-"+todayDate;
                                            dateVal         = 'value="'+currentDate+'"';
                                        }
                                        dynamicForm += '<input class="form-control" for="'+rate+'" data-value="'+encodeFromInput(value.label)+'" name=item['+newId+'][guest]['+identifier+'][' + encodeFromInput(value.name) + '] type="date"'+dateVal+'>';
                                    } else {
                                        dynamicForm += '<input class="form-control" rateId="'+availableRateInfo[rate]['name']+'" for="'+rate+'" data-value="'+encodeFromInput(value.label)+'" name=item['+newId+'][guest]['+identifier+'][' + encodeFromInput(value.name) + '] type="date">';
                                    }
                                    dynamicForm += '</div>';
                                    dynamicForm += '</div>';
                                    dynamicForm += '</div>';
                                } else if(product_line == 'ttd' && value.type == 'checkbox') {
                                    var options = value.options;
                                    dynamicForm += '<div class="col-md-6">';
                                    dynamicForm += '<div class="form-group row">';
                                    dynamicForm += '<label class="control-label col-md-4 text-right">'+value.label+':</label>';
                                    dynamicForm += '<div class="col-md-8">';
                                    dynamicForm += '<select multiple="multiple" addi-index='+cntSku+' addiGuest_id="'+value.name+'" data-maximum-selection-length="'+encodeFromInput(value.max_value)+'" "data-value="'+encodeFromInput(value.label)+'" class="form-control additional_guest_info">';
                                    $.each(options, function (key, op) {
                                        dynamicForm += '<option class="form-control" value="' + op + '">' + op + '</option>';
                                    });
                                    dynamicForm += '</select>';
                                    dynamicForm += '<input type="hidden" data-value="'+encodeFromInput(value.label)+'" data-type="'+value.type+'" data-name="'+value.label+'" minimum-select="'+value.min_value+'" class="additional-guest-info-'+value.name+cntSku+' additional-passenger-level" name="item['+newId+'][guest]['+identifier+'][' + encodeFromInput(value.name) + ']">'
                                    dynamicForm += '</div>';
                                    dynamicForm += '</div>';
                                    dynamicForm += '</div>';
                                } else if (value.type == 'select') {
                                    if(value.name == 'weight_unit') {
                                        return;
                                    }
                                    var options = value.options;
                                    dynamicForm += '<div class="col-md-6">';
                                    dynamicForm += '<div class="form-group row">';
                                    dynamicForm += '<label class="control-label col-md-4 text-right">'+value.label+':</label>';
                                    dynamicForm += '<div class="col-md-8">';
                                    dynamicForm += '<select data-value="'+encodeFromInput(value.label)+'" class="form-control" name=item['+newId+'][guest]['+identifier+'][' + encodeFromInput(value.name) + ']>';
                                    $.each(options, function (key, op) {
                                        var selectedVal = ((value.name == 'nation' || value.name == 'nationality') && op.toLowerCase() == 'usa') ? "selected" : "";
                                        dynamicForm += '<option class="form-control" '+selectedVal+' value="' + op + '">' + op + '</option>';
                                    });
                                    dynamicForm += '</select>';
                                    dynamicForm += '</div>';
                                    dynamicForm += '</div>';
                                    dynamicForm += '</div>';
                                }else if (value.type == 'list') {
                                    var options = value.options;
                                    dynamicForm += '<div class="col-md-6">';
                                    dynamicForm += '<div class="form-group row">';
                                    dynamicForm += '<label class="control-label col-md-4 text-right">'+value.label+':</label>';
                                    dynamicForm += '<div class="col-md-8">';
                                    dynamicForm += '<select data-value="'+encodeFromInput(value.label)+'" class="form-control" name=item['+newId+'][guest]['+identifier+'][' + encodeFromInput(value.name) + ']>';
                                    $.each(options, function (key, op) {
                                        dynamicForm += '<option class="form-control" value="' + op + '">' + op + '</option>';
                                    });
                                    dynamicForm += '</select>';
                                    dynamicForm += '</div>';
                                    dynamicForm += '</div>';
                                    dynamicForm += '</div>';
                                } else if (value.type == 'radio') {
                                    var options = value.options;
                                    dynamicForm += '<div class="col-md-6">';
                                    dynamicForm += '<div class="form-group row">';
                                    dynamicForm += '<label class="control-label col-md-4 text-right">'+ value.label +':</label>';
                                    dynamicForm += '<div class="col-md-8">';
                                    dynamicForm += '<div class="options">';
                                    $.each(options, function (key, op) {
                                        if(key == 0){
                                            var rchecked = 'checked';
                                        }else{
                                            
                                            var rchecked = '';
                                        }
                                        dynamicForm += '<label class="custom-control custom-radio">';
                                        dynamicForm += '<input '+rchecked+'  data-value="'+encodeFromInput(value.label)+'" class="custom-control-input"  name=item['+newId+'][guest]['+identifier+'][' + encodeFromInput(value.name) + '] type="radio" value="' + op + '">';
                                        dynamicForm += '<span class="custom-control-indicator"></span>';
                                        dynamicForm += '<span class="custom-control-description">' + op + '</span>';
                                        dynamicForm += '</label>';
                                    });
                                    dynamicForm += '</div></div>';
                                    dynamicForm += '</div>';
                                    dynamicForm += '</div>';
                                } else if (value.type == 'text' && value.name == 'weight') {
                                        dynamicForm += '<div class="col-md-6">';
                                        dynamicForm += '<div class="form-group row">';
                                        dynamicForm += '<label class="control-label col-md-4 text-right">'+value.label+':</label>';
                                        dynamicForm += '<div class="col-md-4">';
                                        dynamicForm += '<input class="form-control" data-value="'+encodeFromInput(value.label)+'" name=item['+newId+'][guest]['+identifier+'][' + encodeFromInput(value.name) + '] type="number">';
                                        dynamicForm += '</div>';
                                        dynamicForm += '<div class="col-md-4">';
                                        var weightOption = ['kg', 'pound'];
                                        dynamicForm += '<select data-value="weight_unit" class="form-control" name=item['+newId+'][guest]['+identifier+'][weight_unit] type="select">';
                                        $.each(weightOption, function (key, op) {
                                            dynamicForm += '<option class="form-control" value="' + op + '">' + op + '</option>';
                                        });
                                        dynamicForm += '</select>';
                                        dynamicForm += '</div>';
                                        dynamicForm += '</div>';
                                        dynamicForm += '</div>';
                                } else {
                                    dynamicForm += '<div class="col-md-6">';
                                    dynamicForm += '<div class="form-group row">';
                                    dynamicForm += '<label class="control-label col-md-4 text-right">'+value.label+':</label>';

                                    if(value.label == 'Phone' || value.type == 'phone' || value.name == 'phone' || value.name == 'mobile') {
                                        dynamicForm += '<div class="col-md-2" style="padding-right: 0">';
                                        dynamicForm += '<select style="padding: 0 5px; height: 35px" data-value="'+encodeFromInput(value.label)+'" class="form-control" name=item['+newId+'][guest]['+identifier+'][country_code]>';
                                        $.each(areaCodeData, function (key, option) {
                                            var optionVal = (option.country_en == null) ? option.country_cn : option.country_en;
                                            var selectedVal = (option.country_code == 1 && optionVal.toLowerCase() == 'usa') ? "selected" : "";
                                            dynamicForm += '<option class="form-control" '+selectedVal+' value="' + option.country_code + '">' + option.country_code+'('+optionVal+') </option>';
                                        });
                                        dynamicForm += '</select>';
                                        dynamicForm += '</div>';
                                        dynamicForm += '<div class="col-md-6">';
                                    } else if(value.name == 'weight') {
                                        dynamicForm += '<div class="col-md-4">';
                                    }else {
                                        dynamicForm += '<div class="col-md-8">';
                                    }

                                    if(value.type == 'time') {
                                        dynamicForm += '<input class="form-control additional_pass_level_time timepicker-input" data-value="'+encodeFromInput(value.label)+'" name=item['+newId+'][guest]['+identifier+'][' + encodeFromInput(value.name) + '] type="text">';
                                    } else if(value.type == 'number') {
                                        dynamicForm += '<input class="form-control" data-value="'+encodeFromInput(value.label)+'" name=item['+newId+'][guest]['+identifier+'][' + encodeFromInput(value.name) + '] type="'+value.type+'" onkeydown="return event.keyCode !== 69">';
                                    } else {
                                        dynamicForm += '<input class="form-control" data-value="'+encodeFromInput(value.label)+'" name=item['+newId+'][guest]['+identifier+'][' + encodeFromInput(value.name) + '] type="'+value.type+'">';
                                    }
                                    if(value.name == 'weight') {
                                        var weightKey    = parseInt(prKey)+1;
                                        var weightDetail = displayFields[weightKey];
                                        var options      = weightDetail.options;
                                        dynamicForm += '</div>';
                                        dynamicForm += '<div class="col-md-4">';
                                        dynamicForm += '<select data-value="'+encodeFromInput(weightDetail.label)+'" class="form-control" name=item['+newId+'][guest]['+identifier+'][' + encodeFromInput(weightDetail.name) + '] type="'+weightDetail.type+'">';
                                        $.each(options, function (key, op) {
                                            dynamicForm += '<option class="form-control" value="' + op + '">' + op + '</option>';
                                        });
                                        dynamicForm += '</select>';
                                    }
                                    dynamicForm += '</div>';
                                    dynamicForm += '</div>';
                                    dynamicForm += '</div>';
                                }
                                if(count % 2 === 0 || (incrementElement === totalElement)){
                                    dynamicForm += '</div>';
                                }
                                ++count;
                                ++incrementElement;
                                
                            });
                            dynamicForm += '<br>';
                            cntSku++;
                    
                    }

                }
            }
        }
    });
    dynamicForm +='</div>';
    return dynamicForm;
}
//Check Validation when move on next/previous tab
function checkValidation(){

	var productRaw = $('.parentProduct').map(function(){
	    return $(this).data('row-id');
	}).get();
	var checkValidation ={};
	var date='';
	var countRate =0;
	

	$.each(productRaw,function(k,id){
		checkValidation[id] ={};
		//Empty start Date
		var product_line = $('.product-detail-div-'+id).find('.productDetail').attr('data-product_line');

		date = $('.dateRow-'+id).find('input[name^="item['+id+'][departure_date]"]').val();
		if(date === '' || date === 'undefined'){
			checkValidation[id]['validate'] = false;
			checkValidation[id]['message']  = 'Please select start date of '+id+' Row';
		}

		var pickup = $('.pickupRow-'+id).find('input[name^="item['+id+'][pickup_location]"]').val();
		if($("#pickup_location:not([readonly])").length && ($.trim(pickup) === '' || pickup === 'undefined')){
			checkValidation[id]['validate'] = false;
			checkValidation[id]['message']  = 'Please enter Pickup Information';
		}
		//atleast one Room type
		countRate = 0;

		if(product_line == 'ttd') {
			$('.skuRatesInfo-'+id).find('input[name^="item['+id+'][rate_options]"].rate').each(function(index,rate) {
				countRate = parseInt(countRate) + parseInt($(this).val());
			});
		} else {
			$('.roomRow-'+id).find('input[name^="item['+id+'][rate_options]"].rate').each(function(index,rate) {
				countRate = parseInt(countRate) + parseInt($(this).val());
			});
		}
					
		if(countRate <= 0){
			checkValidation[id]['validate'] = false;
			checkValidation[id]['message']  = 'Please select atleast rate in '+id+' Row';
		}
		if(countRate > minMaxGuestNumber[id]['max_guest_number']){
			checkValidation[id]['validate'] = false;
			checkValidation[id]['message']  = 'You can select maximum '+minMaxGuestNumber[id]['max_guest_number'] +' guest in '+id+' Row';
		}
		var price = $('.priceRow-'+id).find('.sub-price').html();
		if(parseInt(price) <= 0){
			checkValidation[id]['validate'] = false;
			checkValidation[id]['message']  = 'Price of '+id+' Row must be greater than 1';
		}

        if(product_line != 'ttd'){
            var departureLocation = $('.departureRow-' + id).find('select[name^="item[' + id + '][departure_location]"]').val();
            if (departureLocation === '' || departureLocation === 'undefined') {
                checkValidation[id]['validate'] = false;
                checkValidation[id]['message'] = 'Please select Departure Location';
            }
        }
        if(product_line == 'ttd'){
            //product level additional field
            var addiMsg = '';
            if($('.extFieldRow-'+id).find('.additional_info')) {
                 $('.extFieldRow-'+id).find('.additional_info').each(function(index,rate) {
                    var dataName = $(this).attr('data-name');
                    var type     = $(this).attr('type');
                    if($(this).val() === '' || $(this).val() === null) {
                        addiMsg  = ''+dataName+' field is required';
                        return false;
                    } else if(type === 'email') {
                        var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
                        if( !emailReg.test( $(this).val() ) ) {
                            addiMsg  = ''+dataName+' enter a valid email address';
                        }
                    }
                 });
            }
            if(addiMsg == '' && $('.extFieldRow-'+id).find('.additional-product-level')) {
                $('.extFieldRow-'+id).find('.additional-product-level').each(function(index,rate) {
                    var addiVal  = $(this).val();
                    var minValue = $(this).attr('minimum-select');
                    var dataName = $(this).attr('data-name');
                    if(addiVal) {
                        var addiVal  = addiVal.split(',');
                    } else {
                        var addiVal  = [];
                    }

                    if(minValue > addiVal.length) {
                        addiMsg = ' Please minimum select '+minValue+' '+dataName+' option';
                        return false;
                    }
                });
            }
            if(addiMsg != ''){
                checkValidation[id]['validate'] = false;
                checkValidation[id]['message'] = addiMsg;
            }
        }

	});
	return checkValidation;
}
function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^[+a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i);
    return pattern.test(emailAddress);
};

function getOptionId(option_id){

    if(option_id == 'NO_UPGRADES'){
        return option_id;
    }
    if(typeof option_id == 'string'){
        var optionArr = option_id.split('_');
        if(typeof optionArr[1] == 'undefined'){
            return optionArr[0];
        }else{
            return optionArr[1];
        }
    }
    return option_id;
}

function titleCase(str) {
    var splitStr = str.toLowerCase().split('_');
    for (var i = 0; i < splitStr.length; i++) {
        // You do not need to check if i is larger than splitStr length, as your for does that for you
        // Assign it back to the array
        splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);
    }
    // Directly return the joined string
    return splitStr.join(' ');
 }
$(document).ready(function(){
     var flag = true;
    getAreaCode();
            
    /* Order Steps Start */
    $("#order-form").steps({
        headerTag: "h3",
        bodyTag: "section",
        transitionEffect: "slideLeft",
        autoFocus: true,
        onStepChanging: function (event, currentIndex, newIndex){
            //Check Validation Start
            var validationExist = false;
            if(countProductRow <= 0){
                notify('Please Select Date options','danger');
                return false;
            }
            
            $.each(checkValidation(),function(k,value){
                if(value['validate'] == false){
                    validationExist = true;
                    notify(value['message'],'danger');
                }
            });
            if(validationExist) { return false; } //Check Validation End
            //From first step to Second step
            if(currentIndex == 0 && newIndex == 1 && !$.isEmptyObject(passengerAttributes)){
                var multiselects = $('select.upgrades[data-ismulti=1]');
                var chkerror = false;
                $.each(multiselects,function(k,v) {
                    var count = $(this).find('option:selected').length;
                    var minselect = $(this).attr('data-minselect');
                    var maxselect = $(this).attr('data-maxselect');
                    if (count < minselect ) {
                            notify('Please select minimum '+minselect+' options','danger');
                        chkerror = true;
                    }
                    if (count > maxselect) {
                            notify('Please select maximum '+maxselect+' options','danger');
                        chkerror = true;
                        
                    }
                });
                if (chkerror) {
                     return false;
                }
                passengerAttributes = mergeRoomInAtt(passengerAttributes);
                
                $('.passenger-div').html(generateGuestForm(passengerAttributes));
                $('.additional_guest_info').select2();
                $('.additional_pass_level_time').datetimepicker({
                    format: 'HH:mm',
                    icons: {
                        up: "icofont icofont-rounded-up",
                        down: "icofont icofont-rounded-down"
                    }
                });
                totalCnt = 0;
                $.each(passengerAttributes, function(roomId, value) {
                    totalCnt += value.total;
                });
                var htmlContent = '<div class="col-md-4" style="padding: 0">';
                htmlContent += '<select style="height: 35px; padding: 0 5px" name="order_subscriber[country_code]" class="form-control contact_field" name=order_subscriber[country_code]>';
                $.each(areaCodeData, function (key, option) {
                    var optionVal = (option.country_en == null) ? option.country_cn : option.country_en;
                    var selectedVal = (option.country_code == 1 && optionVal.toLowerCase() == 'usa') ? "selected" : "";
                    htmlContent += '<option class="form-control" '+selectedVal+' value="' + option.country_code + '">' + option.country_code+'('+optionVal+') </option>';
                });
                htmlContent += '</select>';
                htmlContent += '</div>';
                htmlContent += '<div class="col-md-8">';
                htmlContent += '<input type="text" name="order_subscriber[telephone]" class="form-control contact_field" id="telephone">';
                htmlContent += '</div>';
                $("#customer_telephone").html(htmlContent);
            }
            //From Second step to third Step
            if(currentIndex == 1 && newIndex == 2 && !$.isEmptyObject(priceArray)){
                
                if($('#first_name').val() === ''){
                    notify('Customer First name field is required','danger');
                    return false;
                }
                if($('#last_name').val() === ''){
                    notify('Customer Last name field is required','danger');
                    return false;
                }
                if($('#email').val() === ''){
                    notify('Customer email field is required','danger');
                    return false;
                }
                if(!isValidEmailAddress($('#email').val())){
                    notify('Customer email is invalid','danger');
                    return false;
                }
                if($('#telephone').val() === ''){
                    notify('Customer telephone field is required','danger');
                    return false;
                }
                
                var $inputItem = $('.passenger-div').find('input[name*=item]');
                var $errors = false;
                $.each($inputItem,function(k,v){
                    var $this = $(this);
                    $this.removeClass('input-danger');
                    var ageArr = {};
                    $.each(rateOptions,function(rKey,room){
                        $.each(room,function(key,rate){
                            ageArr[rate.rate_id] = {
                                'min' : rate.min_age,
                                'max' : rate.max_age,
                                'type' : rate.rate_id,
                            }
                        });
                    });


                    if( $this.val() == "" ) {

                        $errors = true;
                        $this.addClass('input-danger');
                        var $name = $this.attr('name');
                        var $nameKeys = $this.attr('data-value');
                        var $errorsMessage = '';
                        $errorsMessage = titleCase($nameKeys)+" field is required.";
                        $this.attr('placeholder',$errorsMessage);
                        notify($errorsMessage,'danger');
                        
                    }else{

                        var product_line = $('.product-detail-div-1').find('.productDetail').attr('data-product_line');

                        if(product_line == 'ttd') {
                            var $nameKeys = $this.attr('data-value');
                            if($this.attr('type') == 'date' && ($nameKeys == 'birth' || $nameKeys == 'date_of_birth')){
                                var $errorsMessage = '';
                                var userType = $this.attr('for');
                                var enteredDate = $(this).val();
                                  var date1 = new Date();
                                  var date2 = new Date(enteredDate);
                                  var diffDays = parseInt((date2 - date1) / (1000 * 60 * 60 * 24 ));
                                  var years = Math.abs(diffDays/365);
                                  var minage = ageArr[userType].min;
                                  var maxage = ageArr[userType].max;
                                  
                                if(maxage != 0) {
                                    $errorsMessage = "Age should lies between "+minage+" and "+maxage;
                                    if (years < minage) {
                                        $this.addClass('input-danger');
                                        $errors = true;
                                        notify($errorsMessage,'danger');
                                    } else if (years >  maxage) {
                                        $this.addClass('input-danger');
                                        $errors = true;
                                        notify($errorsMessage,'danger');
                                    }
                                }

                                if(maxage == 0 && minage != 0) {
                                    $errorsMessage = "Minimum age should be "+minage;
                                    if (years < minage) {
                                        $this.addClass('input-danger');
                                        $errors = true;
                                        notify($errorsMessage,'danger');
                                    }
                                }
                            }
                            if($nameKeys == 'email' || $this.attr('type') == 'email') {
                                if(!isValidEmailAddress($(this).val())) {
                                    $this.addClass('input-danger');
                                    $errors = true;
                                    notify(''+$nameKeys+' is invalid','danger');
                                }
                            }
                            if($this.attr('data-type') && $this.attr('data-type') == 'checkbox') {
                                var addiVal  = $(this).val();
                                var minValue = $(this).attr('minimum-select');
                                var dataName = $(this).attr('data-name');
                                if(addiVal) {
                                    var addiVal  = addiVal.split(',');
                                } else {
                                    var addiVal  = [];
                                }

                                if(minValue > addiVal.length) {
                                    $errors = true;
                                    notify('Please minimum select '+minValue+' '+dataName+' option','danger');
                                }
                            }
                        } else {
                            var $nameKeys = $this.attr('data-value');
                            if($this.attr('type') == 'date' && ($nameKeys == 'adult_birthday' || $nameKeys == 'child_birthday' || $nameKeys == 'birthday')){
                                var $errorsMessage = '';
                                var userType = $this.attr('for');
                                var rateId = $this.attr('rateId');
                                var enteredDate = $(this).val();
                                var date1 = new Date();
                                var date2 = new Date(enteredDate);
                                var diffDays = parseInt((date2 - date1) / (1000 * 60 * 60 * 24 ));
                                var years = Math.abs(diffDays/365);
                                var nowYear = date1.getFullYear();
                                var pastYear = date2.getFullYear();
                                var extyears = parseInt(nowYear - pastYear);
                                var minage = ageArr[userType].min;
                                var maxage = ageArr[userType].max;
                                if(maxage != 0) {
                                    $errorsMessage = rateId + " Age should lies between "+minage+" and "+maxage;
                                    if (extyears < minage) {
                                        $this.addClass('input-danger');
                                        $errors = true;
                                        notify($errorsMessage,'danger');
                                    } else if (extyears >  maxage) {
                                        $this.addClass('input-danger');
                                        $errors = true;
                                        notify($errorsMessage,'danger');
                                    }
                                }
                            }
                        }   
                    }
                    $this = '';
                    rateId = '';
                });

                if( $errors == true ) {
                    return false;
                }

                var tBody = $('.discountSection').find('table.headingTable > tbody');
                var grandTotal = $('.discountSection').find('table.invoice-total > tbody').find('.grandTotal');
                var nameHtml = '';
                var total    = 0;

                $.each(priceArray,function(k,data){

                    var price = (typeof data['price'] != 'undefined') ? data['price'] : 0;
                    
                    nameHtml +='<tr>';
                    nameHtml +='<td class="product_name">'+data['product_name']+'</td>';
                    nameHtml +='<td class="product_price text-right"> $'+price+'</td>';
                    nameHtml +='<tr>';
                    
                    total += (typeof data['price'] == 'string') ? parseFloat(price.replace(',','')) : price;

                });
                tBody.html(nameHtml);
                grandTotal.html('$'+total.toFixed(2));
            }

            // Save Order Details
            if(currentIndex == 2 && newIndex == 3 ){
                if($("#note_info").val() == ''){
                    notify('Special note is required','danger');
                    return false;
                }

                var form = $("#order-form");
                var response = true;
                $.ajax({
                    method: "post",
                    url: form.attr("action"),
                    data: form.serialize(),
                    beforeSend: function () {
                        $(".loading").show();
                    },
                })
                .done(function( result ) {
                    $(".loading").hide();
                    if (typeof result.code == "undefined"){
                        $.each(result,function(k,v){
                            notify(v,"danger");
                        });
                        response = false;
                    }else if (result.code == -1){
                        if(Array.isArray(result.msg)){
                            var item = result.msg;
                            jQuery.each(item, function(k,v) {
                                notify(v, (result.code == 0) ? 'success' : 'danger');
                            });
                        }else{
                            notify(result.msg,"danger");
                        }
                        response = false;
                    }else {
                        $("#previousBtn").remove();
                        $('.creditCardForm').find('#payNow').attr('data-order-id',result.data.order_id);
                        notify(result.msg,(result.code === 0) ? "success" : "danger");
                        response = true;
                    }
                }).fail( function( jqXhr ) {
                    $(".loading").hide();
                    if( jqXhr.status === 422 ) {
                        var $errors = jqXhr.responseJSON;
                        $.each($errors,function(i,v){
                            notify(v,"danger");
                        });
                    }
                    response = false;
                    
                });
                if(!response){
                    return false;
                }
            }
            if(currentIndex == 3 && (newIndex == 2 || newIndex == 1 || newIndex == 0)){
                notify("You can't redirect to go back","danger");
                return false;
            }
            return true;
        },
        onFinished: function (event, currentIndex) {
            
            window.location="/bookings/tour-orders";
        }
    });
    /* Order Steps End */
      
    /* Product Search Start */
    function showResult(data) {
        if (data.loading) return data.text;
        var markup  = "<div class='select2-result-repository clearfix'>" +
                      "<div class='select2-result-repository__title'>["+data.product_line+"] "+ data.product_id +" ["+ data.name + "] </div>";
            markup += "</div>";
        return markup;
    }
    function showResultSelect(data) {
        return data.name;
    }
    var select = $(".js-product-list");
    select.select2({
        ajax: {
            url: "/product-fetch",
            //url: "/producteditor/match.json",
            delay: 250,
            data: function(params) {
                return {
                    k: params.term
                };
            },
            processResults: function(data, params) {
                return {
                    results: data.data
                };
            }
        },
        escapeMarkup: function(markup) {
            return markup;
        },
        minimumInputLength: 1,
        templateResult: showResult, // omitted for brevity, see the source of this page
        templateSelection: showResultSelect // omitted for brevity, see the source of this page

    }).on("select2:select", function (e) {
        
        displayProductDetails($(this),e);
        $(this).val('');
    });
    /* Product Search End */

    /* Customer Search Start */
    $('#customer').select2({
        placeholder: 'Enter customer name or email',
        // tags:true,
        ajax: {
            url: '/booking/find-customer',
            delay: 250,
            type: 'GET',
            dataType: 'json',
            data: function (params) {
                var query = {
                    keyword: params.term
                }
                return query;
            },
            processResults: function(data, params) {
                return {
                    results: data.data
                };
            }
        },
        escapeMarkup: function(markup) {
            return markup;
        },
        minimumInputLength: 1,
        templateResult: showResultUser, // omitted for brevity, see the source of this page
        templateSelection: showResultSelectUser // omitted for brevity, see the source of this page
    }).on("select2:select", function(e) {
        var orderSubscriber = [];
        var subscriberObj   = {'first_name': e.params.data.first_name,'last_name': e.params.data.last_name,'email': e.params.data.email};
        orderSubscriber.push(subscriberObj);
        if(!$.isEmptyObject(orderSubscriber)){
            $('#first_name').val(orderSubscriber[0]['first_name']);
            $('#last_name').val(orderSubscriber[0]['last_name']);
            $('#email').val(orderSubscriber[0]['email']);
        }else{
            $('#first_name').val('');
            $('#last_name').val('');
            $('#email').val('');
        }
    });
    /* Customer Search End */

    /* Country Code Start */
    function showCountryResult(data) {
        if (data.loading) return data.text;
        var markup  = "<div class='select2-result-repository clearfix'>" +
                      "<div class='select2-result-repository__title'>"+ data.name + "</div>";
            markup += "</div>";
        return markup;
    }
    function showCountryResultSelect(data) {
        return data.name;
    }
    var select = $("#billing_country");
    select.select2({
        ajax: {
            url: "/location/region/country-code-list",
            delay: 250,
            data: function(params) {
                return {
                    q: params.term
                };
            },
            processResults: function(data, params) {
                return {
                    results: data.data
                };
            }
        },
        escapeMarkup: function(markup) {
            return markup;
        },
        minimumInputLength: 1,
        templateResult: showCountryResult, // omitted for brevity, see the source of this page
        templateSelection: showCountryResultSelect // omitted for brevity, see the source of this page
    });
    /* Country Code End */
});
//for close calender when click outside div
$(document).click(function(e) {
    var container = $(".main-departure"),
        calender  = $(".bot-cal"),
        clndr_btn = $(event.target).hasClass('clndr-btn');

    if (!container.is(e.target) && container.has(e.target).length === 0 && clndr_btn === false){
        if( !$(e.target).hasClass('leftButton') && !$(e.target).hasClass('rightButton')){
            calender.hide();
        }
    }
});
// Calendar 2 uses a custom length of time: 2 weeks paging 7 days
$(document).on('click','.departure_date',function() {
    var dateField    = $(this),
        rawId        = dateField.attr('data-rawId'),
        calField     = dateField.parents('.dateRow-'+rawId).find('.bot-cal'),
        focusDiv     = calField.prev(),
        product_id   = dateField.parents('.parentProduct').find('.productDetail').attr('data-product_id'),
        product_line   = dateField.parents('.parentProduct').find('.productDetail').attr('data-product_line'),
        productDetailDiv = dateField.parents('.well-sm'),
        duration = 1,
        durationType='',
        date = new Date(),
        startDate = $.datepicker.formatDate('yy-mm-dd',new Date(date.getFullYear(), date.getMonth(), 1)),
        endDate = $.datepicker.formatDate('yy-mm-dd', new Date(date.getFullYear() + 1, date.getMonth() + 1, 0));
    
    calField.show();

    //focusDiv.css('display','inline-block');
    // fetchDate(startDate,endDate,product_id);
    fetchDate(product_id);
    
    //Calendar 2
    calendars.clndr2 = calField.clndr({
        multiDayEvents: {
            singleDay: 'date',
            endDate: 'endDate',
            startDate: 'startDate'
        },
        constraints: {
            startDate:  startDate,
        },
        //startWithMonth : "2018-12-01",
        template: $('#template-calendar').html(),
        clickEvents: {
            click: function (target) {
                if(target.events.length > 0){
                    $('.upgradeRow-1').show();
                    $('.priceRow-1').show();
                    $('.pickupRow-1').show();
                    $('.roomRow-1').show();


                    dateField.val(target.date._i);
                    duration = productDetailDiv.find('.productDetail').attr('data-duration');
                    durationType = productDetailDiv.find('.productDetail').attr('data-durationType');
                    if(durationType != 'day' || duration == ''){
                        var end_date = target.date._i;
                    }else{
                        var end_date = moment(target.date._i, "YYYY-MM-DD").add(parseInt(duration-1), 'days').format("YYYY-MM-DD");
                    }
                    productDetailDiv.find('.end_date').val(end_date);
                                       
                    $(".upgradeRow-1").html("");

                    var onchangeupgrade   = 0;
                    var onlyUpgradeChange = 0;
                    var rateChange        = 1;
                    var isTtdProduct      = 0;

                    if(product_line == 'ttd') {
                        var isTtdProduct      = 1;
                        $('.priceRow-'+rawId).find('.sub-total-div > .sub-amount > .amount > .sub-price').html('0.00');
                    }

                    getPrice(product_id,rawId,onchangeupgrade,onlyUpgradeChange,rateChange,isTtdProduct);
                }else{
                    dateField.val('');
                }
                calField.hide();
            },
            nextInterval: function () {
                
            },
            previousInterval: function () {
                
            },
            onIntervalChange: function () {
                
            },
            previousMonth: function (month) {
                
                month_first_date = moment(month._d).format('YYYY-MM-DD');
                month_end_date = moment(month_first_date).endOf('month').format('YYYY-MM-DD');
                fetchDate(product_id);
                /*var startDate = moment([month.format('YYYY'), month.format('MM') - 1]),
                    endDate = moment(startDate).endOf('month');
                fetchDate(startDate.format("YYYY-MM-DD"),endDate.format("YYYY-MM-DD"),product_id);*/
            },
            nextMonth: function (month) {
                month_first_date = moment(month._d).format('YYYY-MM-DD');
                month_end_date = moment(month_first_date).endOf('month').format('YYYY-MM-DD');
                fetchDate(product_id);
                //var month_end_date =  moment(startDate).endOf('month');
                /*var startDate = moment([month.format('YYYY'), month.format('MM') - 1]),
                    endDate = moment(startDate).endOf('month');
                fetchDate(startDate.format("YYYY-MM-DD"),endDate.format("YYYY-MM-DD"),product_id);*/
            },
        }
    });
});
//This event call when Add / Substract Room
$(document).on('click','.addRoom', function () {
    var current = $(this),
        rawId = current.attr('data-rawId'),
        parentRoomDetail = $(this).parents('.roomDetail'),
        newRowCount = parentRoomDetail.find('.rooms').length + 1,
        newRoom = roomInfo(rateOptions[rawId],rawId).replace(/_ID_/g,newRowCount),
        product_id  = $(this).parents('.parentProduct').find('.productDetail').attr('data-product_id'),
        product_line = $(this).parents('.parentProduct').find('.txt-product-line').val();
    
    if( product_line == 'tour' ) {
        $(newRoom).insertAfter(parentRoomDetail.find('.rooms:last'));
        parentRoomDetail.find('.rooms:last').attr('data-rawid',newRowCount);
        getPrice(product_id,rawId);
    } else {
        notify('Single day tour must have 1 room!', "danger");
    }

});

$(document).on('change', '.departure_location', function () {
    var value = $(this).val();
    var pickup = $(this).data('pickup');
    if(pickup == 1 && value != customPickup.no_address){
        $("#pickup_location").val('').removeAttr('readonly');
    }else{
        $("#pickup_location").val('').attr('readonly','readonly');
    }
});

$(document).on('click', '.removeRoom', function () {
    var current = $(this),
        rawId = current.attr('data-rawId'),
        parentRoomDetail = $(this).parents('.roomDetail'),
        product_id  = $(this).parents('.parentProduct').find('.productDetail').attr('data-product_id');

    if(parentRoomDetail.find('.rooms').length > 1){
        parentRoomDetail.find('.rooms:last').remove();
        getPrice(product_id,rawId);
    }
});

$(document).on('click', '.removeSkuRoom', function () {
    var current = $(this),
        rawId = current.attr('data-rawId'),
        skuValue = $(this).attr('data-skuValue'),
        parentRoomDetail = $(this).parents('.selectedSku-'+skuValue),
        product_id  = $(this).parents('.parentProduct').find('.productDetail').attr('data-product_id');
        $('li[data-skuValue='+skuValue+']').removeClass("active");
        parentRoomDetail.remove();
        //var skuCount = $('.skuName').find('.active').length;
        var skuCount = $('.skuName-group li.active').length;
        if(skuCount == 0) {
            $(".skuRatesInfo-"+rawId).remove();
        }
        //$('.skuName').removeClass('active');
        getPrice(product_id,rawId);
});
//This event call when plus (+) / minus (-) room total count
$(document).on('click', '.plusRoom', function () {
	var current     = $(this),
		rawId       = current.attr('data-rawId'),
		rateField   = $(this).parents('.rateDiv').find('.rate'),
		maxLength   = rateField.attr('data-max'),
		currentRate = rateField.val(),
		product_id  = $(this).parents('.parentProduct').find('.productDetail').attr('data-product_id');

	var rateCount    = $('.rateDiv').find('.rate');
	var maxRatesData = 0;
	var product_line = current.parents('.parentProduct').find('.productDetail').attr('data-product_line');
	var max_guest_number = current.parents('.parentProduct').find('.productDetail').attr('data-max_guest_number');
	
	if(product_line != 'ttd') {
		var compulsoryNum = current.data("qty-choise").toString();
		if (Object.keys(rateCount).length > 0) {
            $.each(rateCount, function(index, value) {
            	maxRatesData += parseInt($(value).val());
            });
        }

        var nextAssignedQty = parseInt(currentRate)+1;
        if(product_line == "tour" && compulsoryNum != '') {
            var hasRate = false;
            for (var i = nextAssignedQty; i <= 4; i++) {
                if (compulsoryNum.indexOf(i)>=0) {
                    nextAssignedQty = i;
                    hasRate = true;
                    break;
                }
            }
            // if it has no valid rate don't allow to change
            if(!hasRate) {
                return false;
            }
        }
        // i need to fix the code here
        if(nextAssignedQty > parseInt(max_guest_number)) {
        	notify('Maximum guest for booking are '+max_guest_number, "danger");
	    } else {
        	if(nextAssignedQty > maxLength) {
				notify('Max range of this rate type is exceeded', "danger");
			} else {
                rateField.val(nextAssignedQty);
                getPrice(product_id,rawId);
			}
        }

	} else {

		if((parseInt(currentRate)+1) > maxLength) {
			notify('Max range of this rate type is exceeded', "danger");
		} else {
			rateField.val(parseInt(currentRate)+1);
			getPrice(product_id,rawId);
		}
	}
});
$(document).on('click', '.minusRoom', function () {
	var current     = $(this),
		rawId       = current.attr('data-rawId'),
		rateField   = $(this).parents('.rateDiv').find('.rate'),
		minLength   = rateField.attr('data-min'),
		currentRate = rateField.val(),
		product_id  = $(this).parents('.parentProduct').find('.productDetail').attr('data-product_id');

    var product_line = current.parents('.parentProduct').find('.productDetail').attr('data-product_line');
	 
    if(api_source == ''){
        if(product_line != 'ttd') {
            var compulsoryNum = current.data("qty-choise").toString();
            var nextAssignedQty = parseInt(currentRate)-1;
            if(product_line == "tour" && compulsoryNum != '') {
                var hasRate = false;
                for (var i = nextAssignedQty; i >= 1; i--) {
                    if (compulsoryNum.indexOf(i)>=0) {
                        nextAssignedQty = i;
                        hasRate = true;
                        break;
                    }
                }
                // if it has no valid rate don't allow to change
                if(!hasRate) {
                    return false;
                }
            }

    		if(nextAssignedQty < minLength){
    			notify('Min range of this rate type is exceeded', "danger");
    		}else if(currentRate > 0){
    			rateField.val(nextAssignedQty);
    			getPrice(product_id,rawId);
    		}
        } else {
            if((parseInt(currentRate)-1) < minLength){
                notify('Min range of this rate type is exceeded', "danger");
            } else if(currentRate > 0){
                rateField.val(parseInt(currentRate)-1);
                getPrice(product_id,rawId);
            }
        }

	}else if(currentRate > 0){
		var rateCount    = $('.rateDiv').find('.rate');
		var minRatesData = 0;
		
		if(product_line != 'ttd') {
			if (Object.keys(rateCount).length > 0) {
	            $.each(rateCount, function(index, value) {
	            	minRatesData += parseInt($(value).val());
	            });
	        }

			if((parseInt(minRatesData)-1) < 1) {
				notify('Min range of this rate type is exceeded', "danger");
			} else {
				rateField.val(parseInt(currentRate)-1);
				getPrice(product_id,rawId);
			}
		} else {
			rateField.val(parseInt(currentRate)-1);
			getPrice(product_id,rawId);
		}
	}
});
// This Event call when remove selected product
$(document).on('click', '.deleteProduct', function () {
    if($('.parentProduct').length > 1){
        var rawId = $(this).parents('.parentProduct').attr('data-row-id');
        $(this).parents('.parentProduct').remove();
        //remove from passenger Attribute
        delete passengerAttributes[rawId];
        //remove from room options
        delete rateOptions[rawId];
        //remove from price array
        delete priceArray[rawId];
        totalRow();
    }else{
        notify('atleast one product must be selected', "danger");
    }
});
function changeUpgradeName(currentObj,action) {

    var count = currentObj.attr('data-count'),
        rawId = currentObj.attr('data-rawId'),
        upgradeId = currentObj.attr('data-upgrade-id'),
        name = 'item['+rawId+'][upgrade]['+upgradeId+']['+count+']',
        dataName = 'upgrade['+upgradeId+']['+count+']',
        is_multi = currentObj.attr('data-isMulti');
    
    if(action == 'remove'){
        currentObj.removeAttr('name');
        currentObj.removeAttr('data-name');
    }else if(action == 'add'){
        if(is_multi == 1){
            currentObj.attr('name','item['+rawId+'][upgrade]['+upgradeId+'][]');
        }else{
            currentObj.attr('name',name);
        }
        currentObj.attr('data-name',dataName);
    }
}
//Fetch Multiple Upgrade Sub Options
$(document).on('change', '.upgrades, .upgradeItems', function() {

    var flag = true;
    var thisselect = $(this);
    var selected = thisselect.val();
    var is_multi = thisselect.prop('multiple');
    if (selected.length == 2  && is_multi == true) {

        if (selected[selected.length - 1] != '1') {
            var wanted_option = thisselect.children("option[value=1]");
            wanted_option.prop('selected', false);
            $('select[multiple]').multiselect('reload');
        }
        
        if (selected[selected.length - 1] == '1') {
            var all_option = thisselect.children("option");
            all_option.prop('selected', false);
            thisselect.val(1);
            $('select[multiple]').multiselect('reload');
        }

    } else if (selected.length > 1  && is_multi == true) {

        if ($.inArray("1", selected.length) > -1) {
            thisselect.val(1);
            $('select[multiple]').multiselect('reload');
        }

    }
    
    var t = $(this),
        rawId = $(this).attr('data-rawId'),
        product_id = $(this).parents('.parentProduct').find('.productDetail').attr('data-product_id'),
        upgrade_id = t.data('upgrade-id'),
        option_id = $(this).val(),
        rawHtml = '',
        parentDiv = '';

    option_id = getOptionId($(this).val());
    
    //check parent div
    if (t.hasClass('upgrades')) {
        parentDiv = t.parents('.upgradeDiv');
        t.parents('.upgradeDiv').find('.optionDiv').remove();
        // t.parents('.upgradeDiv').find('.clearfix').remove();
    }

    var upgradeCount = $('.attr_' + currentRowID(t) + '_' + $(this).data('upgrade-id')).length + 1;
    
    if (option_id != '') {
        if (api_source == 'tourcms') {
            var requestUrl =  '/booking/get-upgrade/' + upgrade_id + '/option';
            var dataArray = {product_id: product_id, option_id: option_id};
        }else{
            var requestUrl =  '/booking/get-upgrade/' + upgrade_id + '/option/' + option_id;
            var dataArray = {product_id: product_id, option_id: option_id};
        }
        
        $.ajax({
            method: "GET",
            dataType: 'json',
            url: requestUrl,
            data: dataArray,
            beforeSend: function() {},
            success: function(response) {
                if (!$.isEmptyObject(response)) {
                    //first remove old options
                    $.each(response, function(index, value) {
                        rawHtml += '<div class="clearfix"></div>';
                        rawHtml += '<div class="optionDiv">';
                        rawHtml += '<div class="col-md-3 text-right">';
                        rawHtml += '<label class="form-control-label">' + value.option_name + ' Option : </label>';
                        rawHtml += '</div>';
                        rawHtml += '<div class="col-md-7">';
                        rawHtml += '<div class="p-l-0 m-b-10"><div class="input-group">';
                        rawHtml += '<select name="item[_ROW_ID_][upgrade][' + upgrade_id + '][' + upgradeCount + ']" class="form-control upgradeItems attr__ROW_ID__' + upgrade_id + ' overFloW" data-name="upgrade[' + upgrade_id + '][' + upgradeCount + ']" data-upgrade-id="' + upgrade_id + '" data-rawId="' + rawId + '" data-count="' + upgradeCount + '">';
                        $.each(value.sub_options, function(k, v) {
                            rawHtml += '<option value="' + index + '_' + v.option_id + '">' + v.option_name + '</option>';
                        });
                        rawHtml += '</select>';
                        rawHtml += '</div></div></div></div>';
                        rawHtml += '<div class="clearfix"></div>';
                        upgradeCount = upgradeCount + 1;
                    });
                    t.parents('.upgradeDiv').append(rawHtml.replace(/_ROW_ID_/g, currentRowID(t)));
                    changeUpgradeName(t, 'remove');
                } else {
                    changeUpgradeName(t, 'add');
                }
                if (flag) {
                    getPrice(product_id, rawId, 1);
                }
            }
        });

    } else {

        if (flag) {
            getPrice(product_id, rawId, 1);
        }

    }

});

// this function is used only when we change farharbor api products
$(document).on('change','.start_time',function() {

    var rawId = $(this).attr('data-rawId'),
        productId = $(this).parents('.parentProduct').find('.productDetail').attr('data-product_id');
    if(api_source == 'fareharbor'){
        //getPrice(productId,rawId,0,1);
        getPrice(productId,rawId,0,1,0,0,1);
    }else{
        var selectedStartTime = $(this).val();
        var pickup            = $(".departure_location").data("pickup");
        if(pickup != 1){
            $(".departure_location option:selected").removeAttr("selected");

            $(".departure_location option").each(function() {
                var $thisOption = $(this);
                
                if($thisOption.data('time') != selectedStartTime) {
                    $thisOption.attr("disabled", "disabled");
                } else {
                    $thisOption.removeAttr('disabled');
                    $thisOption.attr("selected", "selected");
                }
            });
        }
    }

});

function createOrderFromProductChanges( productId = 0 ) {
    $.ajax({
        url: "/product-fetch?k="+productId,
        data : { _token : window.csrf_token_string},
        delay: 250,
    }).done(function(data){
        
        $.each(data.data,function(k,v){
            if( v.id == productId ) {
                var e = {"params":{"data":{"product_id":v.id,"product_line":v.product_line,"name":v.name}}};
                displayProductDetails($(".js-product-list"),e);
                return false;
            }
        });
        
    });
}

function createOrderFromProductChangesUsingNetPrice( productId = 0,agentId=0) {
    $.ajax({
        url: "/product-fetch?k="+productId+"&agentId="+agentId,
        data : { _token : window.csrf_token_string},
        delay: 10,
    }).done(function(data){

        $.each(data.data,function(k,v){
            if( v.id == productId ) {
                var e = {"params":{"data":{"product_id":v.id,"product_line":v.product_line,"name":v.name}}};
                displayProductDetailsWithNetPrice($(".js-product-list"),e,agentId);
                return false;
            }
        });
        
    });
}
//Order Status Comment CK Editor Code
/*articl ckeditor*/
if($("#status_comment").length > 0){
    var editor = CKEDITOR.replace("status_comment",{
        // Define the toolbar: http://docs.ckeditor.com/#!/guide/dev_toolbar
        // The standard preset from CDN which we used as a base provides more features than we need.
        // Also by default it comes with a 2-line toolbar. Here we put all buttons in a single row.
        toolbar: [{
            name: 'clipboard',
            items: ['Undo', 'Redo']
        }, {
            name: 'styles',
            items: ['Styles', 'Format']
        }, {
            name: 'basicstyles',
            items: ['Bold', 'Italic', 'Strike', '-', 'RemoveFormat']
        }, {
            name: 'paragraph',
            items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote']
        }, {
            name: 'links',
            items: ['Link', 'Unlink']
        }, {
            name: 'insert',
            items: ['Image', 'EmbedSemantic', 'Table']
        }, {
            name: 'tools',
            items: ['Maximize']
        }, {
            name: 'editing',
            items: ['Scayt']
        }],
        // Enabling extra plugins, available in the standard-all preset: http://ckeditor.com/presets-all
        extraPlugins: 'autoembed,embedsemantic,image2,uploadimage,uploadfile',
        imageUploadUrl: '/uploader/upload.php?type=Images',
        uploadUrl: '/uploader/upload.php',
        removePlugins: 'image',
        height: 200,
        bodyClass: 'article-editor',
        format_tags: 'p;h1;h2;h3;pre',
        removeDialogTabs: 'image:advanced;link:advanced',
        stylesSet: [
            /* Inline Styles */
            {
                name: 'Marker',
                element: 'span',
                attributes: {
                    'class': 'marker'
                }
            }, {
                name: 'Cited Work',
                element: 'cite'
            }, {
                name: 'Inline Quotation',
                element: 'q'
            },

            /* Object Styles */
            {
                name: 'Special Container',
                element: 'div',
                styles: {
                    padding: '5px 10px',
                    background: '#eee',
                    border: '1px solid #ccc'
                }
            }, {
                name: 'Compact table',
                element: 'table',
                attributes: {
                    cellpadding: '5',
                    cellspacing: '0',
                    border: '1',
                    bordercolor: '#ccc'
                },
                styles: {
                    'border-collapse': 'collapse'
                }
            }, {
                name: 'Borderless Table',
                element: 'table',
                styles: {
                    'border-style': 'hidden',
                    'background-color': '#E6E6FA'
                }
            }, {
                name: 'Square Bulleted List',
                element: 'ul',
                styles: {
                    'list-style-type': 'square'
                }
            },
            /* Widget Styles */
            // We use this one to style the brownie picture.
            {
                name: 'Illustration',
                type: 'widget',
                widget: 'image',
                attributes: {
                    'class': 'image-illustration'
                }
            },
            // Media embed
            {
                name: '240p',
                type: 'widget',
                widget: 'embedSemantic',
                attributes: {
                    'class': 'embed-240p'
                }
            }, {
                name: '360p',
                type: 'widget',
                widget: 'embedSemantic',
                attributes: {
                    'class': 'embed-360p'
                }
            }, {
                name: '480p',
                type: 'widget',
                widget: 'embedSemantic',
                attributes: {
                    'class': 'embed-480p'
                }
            }, {
                name: '720p',
                type: 'widget',
                widget: 'embedSemantic',
                attributes: {
                    'class': 'embed-720p'
                }
            }, {
                name: '1080p',
                type: 'widget',
                widget: 'embedSemantic',
                attributes: {
                    'class': 'embed-1080p'
                }
            }
        ]
    });
    // The "change" event is fired whenever a change is made in the editor.
    editor.on('change', function( evt ) {
        $('#status_comment').val(evt.editor.getData());
    });
}

$(document).on('click','#payNow', function() {
        var order_id = $(this).data('order-id');
        // check permission
        $.ajax({
            method: "get",
            url: "/dashboard/checkPermission?path=/pay/" + order_id
        }).done(function(result) {
            if (result.code === -1) {
                // 没有权限支付
                notify(result.msg, "danger");
            } else {
                // 有权限支付
                if(order_id != '' && typeof order_id != 'undefined'){
                    
                    swal({
                        title: "Order Pay",
                        text: "You are redirect to payment page so Please do not refresh page",
                        type: "info",
                        showCancelButton: true,
                        confirmButtonClass: "btn btn-primary",
                        confirmButtonText: "Pay",
                        closeOnConfirm: true
                    }, function () {
            
                        window.open('/pay/'+order_id+'?successUrl='+successUrl, '_blank');
                        
                    });
            
                } else {
                    notify('Invalid Order Details,','danger');
                }
            }
        });
});

$(document).on('click','.skuName-group li', function() {
    var rawId = $(this).attr('data-rawId');
    var product_id = $('.product-detail-div-'+rawId).find('.productDetail').attr('data-product_id');
    var selectedSkuInfo = {};
    
    $(this).siblings().removeClass('active');
    $( ".skuRatesInfo-1" ).remove();
    $(this).addClass('active');

    $("li[class='skuName active']").each(function() {
        var selected = $(this).attr('data-skuValue').split(' ');
        $.each(selected, function(index, value) {
            selectedSkuInfo[value] = value;
        });
    });

    var productDetailDiv = $('.product-detail-div-'+rawId+' .dateRow-1');
    productDetailDiv.append(skuRateInfo(selectedSkuInfo,rawId));
    $('.skuRatesInfo-1').show();
    
    getPrice(product_id,rawId);
});

$(document).on('click', '.showAll', function(){
    $(this).hide();
    var rawId = $(this).attr('data-SkuCount');
    $('.skuGroup-'+rawId).find('.skuName').removeClass('showGuest');
    $('html, body').animate({
        scrollTop: ($('.skuRooms').offset().top)
    },1000);
});

$(document).on('change', '.additional-multi-select', function(){
    var addi_id = $(this).attr('addi_id');
    document.getElementsByClassName('additional-select-'+addi_id+'')[0].value = $(this).val().join(",");
})
$(document).on('change', '.additional_guest_info', function(){
    var addiId = $(this).attr('addiGuest_id');
    var addiIndex = $(this).attr('addi-index');
    document.getElementsByClassName('additional-guest-info-'+addiId+addiIndex+'')[0].value = $(this).val().join(",");
})
function getAreaCode() {
    $.ajax({
        url: '/getAreaCode',
        dataType : 'json',
        method:'get',
        beforeSend: function () {
            $(".loading").show();
        }
    }).done(function(data){
        $(".loading").hide();
        if (data.code == 0 && data.data) {
            areaCodeData = data.data;
        }
    });

}