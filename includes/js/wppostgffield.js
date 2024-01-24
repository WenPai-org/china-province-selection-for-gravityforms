

jQuery(document).ready(function($) {
   // 省市区数据，需要根据实际数据调整
   let areaData;

   // 从提供的 URL 加载数据
   $.getJSON('/wp-content/plugins/gravityformswpfield/includes/js/areaData.json', function (data) {
       areaData = data;

   });

   // 显示弹出层
   $('#areaField').click(function () {
       $('#areaPopup').show();
       $('.area-list').hide();
       $('#province-container').show();
       $('.van-overlay').fadeIn(); // 显示遮罩层
       setActiveTab('#tab-province');
       loadProvinces();
   });

   function setActiveTab(tabId) {
       $('.van-tab').removeClass('van-tab--active');
       $(tabId).addClass('van-tab--active');
   }

   // 加载省份
   function loadProvinces() {
       const provinces = Object.keys(areaData);
       const provinceContainer = $('#province-container');
       provinceContainer.empty();
       $.each(provinces, function (index, province) {
           provinceContainer.append($('<div>').addClass('area-container').text(province).click(function () {
               loadCities(province);
               setActiveTab('#tab-city');
           }));
       });
   }

   // 加载城市并更新标签页
   function loadCities(province) {
       // 更新省份标签页的文本
       $('#tab-province').text(province);

       var cities = Object.keys(areaData[province] || {});
       var cityContainer = $('#city-container');
       cityContainer.empty().show();
       $('#province-container, #district-container, #town-container').hide();
       $.each(cities, function (index, city) {
           cityContainer.append($('<div>').addClass('area-container').text(city).click(function () {
               // 更新城市标签页的文本并加载区县
               $('#tab-city').text(city);
               loadDistricts(province, city);
               setActiveTab('#tab-district');
           }));
       });
   }

   // 加载区县并更新标签页
   function loadDistricts(province, city) {
       // 更新城市标签页的文本
       $('#tab-city').text(city);

       var districts = Object.keys(areaData[province][city] || {});
       var districtContainer = $('#district-container');
       districtContainer.empty().show();
       $('#province-container, #city-container, #town-container').hide();
       $.each(districts, function (index, district) {
           districtContainer.append($('<div>').addClass('area-container').text(district).click(function () {
               // 更新区县标签页的文本并加载镇
               $('#tab-district').text(district);
               loadTowns(province, city, district);
               setActiveTab('#tab-town');
           }));
       });
   }

   // 加载镇并更新标签页
   function loadTowns(province, city, district) {
       // 更新区县标签页的文本
       $('#tab-district').text(district);

       var towns = areaData[province][city][district] || [];
       var townContainer = $('#town-container');
       townContainer.empty().show();
       $('#province-container, #city-container, #district-container').hide();
       $.each(towns, function (index, town) {
           townContainer.append($('<div>').addClass('area-container').text(town).click(function () {
// 更新镇标签页的文本
               $('#tab-town').text(town);
               var input_val = province + ' / ' + city + ' / ' + district + ' / ' + town
               set_wppfield_cascader(input_val)
               $('#areaPopup').hide();
               $('.van-overlay').fadeOut(); // 隐藏遮罩层
           }));
       });
   }

   $(document).click(function (event) {
       if (!$(event.target).closest('#areaField, #areaPopup').length) {
           $('#areaPopup').hide();
       }
   });
   $('.van-overlay').click(function () {
       $('#areaPopup').hide();
       $('.van-overlay').fadeOut(); // 隐藏遮罩层
   });

    var $form = $("form[data-formid]");
    var formId = $form.attr("data-formid");
    var defaultArrInput = $("#cascader_wrap_input_"+formId).children('input')
    if(defaultArrInput[0] && defaultArrInput[0].value) {
        $('#selectedArea').val(defaultArrInput[0].value);
    }

    function set_wppfield_cascader(val)
    {
        $('#selectedArea').val(val);
        $("#cascader_wrap_input_"+formId).children('input').val(val)
    }
})