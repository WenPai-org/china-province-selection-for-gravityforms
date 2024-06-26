// format path by town.code
function findPath(dataArray, targetCode) {
    let path = '';
  
    function traverse(currentNode, currentPath) {
      if (currentNode.code === targetCode) {
        path = currentPath + currentNode.name;
        return true;
      }
  
      if (currentNode.children) {
        for (const child of currentNode.children) {
          if (traverse(child, currentPath + currentNode.name + ' / ')) {
            return true;
          }
        }
      }
  
      return false;
    }
  
    for (const topLevelNode of dataArray) {
      if (traverse(topLevelNode, '')) {
        break;
      }
    }
  
    return path;
}

// check num
function isNumericStr(str) {
    return Number.isFinite(Number(str)) && !Number.isNaN(Number(str));
}

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
        switchTab('#tab-province', '#province-container');
        loadProvinces();
    });

    // Tab 切换逻辑
    function switchTab(tabId, containerId) {
        $('.van-tab').removeClass('van-tab--active');
        $(tabId).addClass('van-tab--active');
        $('.area-list').hide();
        $(containerId).show();
    }

    // 加载省份
    function loadProvinces() {
        const provinces = Object.keys(areaData);
        const provinceContainer = $('#province-container');
        provinceContainer.empty();
        $.each(areaData, function(code, province) {
            provinceContainer.append($('<div>').addClass('area-container').text(province.name).data('code', code).click(function() {
                loadCities(code);
                $('#tab-province').text(province.name);
                switchTab('#tab-city', '#city-container');
            }));
        });
    }

    // 加载城市并更新标签页
    function loadCities(provinceCode) {
        // 更新省份标签页的文本


        var cities = areaData[provinceCode]['children'];
        var cityContainer = $('#city-container');
        cityContainer.empty().show();
        $('#district-container').empty(); // 清空区县列表
        $('#town-container').empty(); // 清空镇列表
        $('#tab-city').text('城市');
        $('#tab-district').text('区县');
        $('#tab-town').text('镇');
        $('#province-container, #district-container, #town-container').hide();
        $.each(cities, function (code, city) {
            cityContainer.append($('<div>').addClass('area-container').text(city.name).data('code', code).click(function () {
                // 更新城市标签页的文本并加载区县
                $('#tab-city').text(city.name);
                loadDistricts(provinceCode, code);
                switchTab('#tab-district', '#district-container');
            }));
        });
    }

    // 加载区县并更新标签页
    function loadDistricts(provinceCode, cityCode) {
        // 更新城市标签页的文本

        var districts = areaData[provinceCode]['children'][cityCode]['children'];
        var districtContainer = $('#district-container');
        districtContainer.empty().show();
        $('#town-container').empty(); // 清空镇列表

        $('#tab-district').text('区县');
        $('#tab-town').text('街道');

        $('#province-container, #city-container, #town-container').hide();
        $.each(districts, function (code, district) {
            districtContainer.append($('<div>').addClass('area-container').text(district.name).data('code', code).click(function () {
                // 更新区县标签页的文本并加载镇
                $('#tab-district').text(district.name);
                loadTowns(provinceCode, cityCode, code);
                switchTab('#tab-town', '#town-container');
            }));
        });
    }

    // 加载镇并更新标签页
    function loadTowns(provinceCode, cityCode, districtCode) {
        // 更新区县标签页的文本

        var towns = areaData[provinceCode]['children'][cityCode]['children'][districtCode]['children'] || {};
        var townContainer = $('#town-container');
        townContainer.empty().show();
        $('#tab-town').text('街道');
        $('#province-container, #city-container, #district-container').hide();
        $.each(towns, function (code, town) {
            townContainer.append($('<div>').addClass('area-container').text(town.name).data('code', town.code).click(function () {
// 更新镇标签页的文本
                $('#tab-town').text(town.name);

                // 获取省市区镇的详细名称
                const provinceName = areaData[provinceCode]['name'];
                const cityName = areaData[provinceCode]['children'][cityCode]['name'];
                const districtName = areaData[provinceCode]['children'][cityCode]['children'][districtCode]['name'];
                const areaVal = provinceName + ' / ' + cityName + ' / ' + districtName + ' / ' + town.name
                // 给#selectedArea赋值详细地址
                $('#selectedArea').val(areaVal);
                set_wppfield_cascader(town.code)

                $('#areaPopup').hide();
                $('.van-overlay').fadeOut(); // 隐藏遮罩层
            }));
        });
    }


    // 为省份标签添加点击事件
    $('#tab-province').click(function() {
        switchTab('#tab-province', '#province-container');
    });

    // 为城市标签添加点击事件
    $('#tab-city').click(function() {
        if ($('#city-container').children().length > 0) { // 检查是否已加载城市数据
            switchTab('#tab-city', '#city-container');
        }
    });

    // 为区县标签添加点击事件
    $('#tab-district').click(function() {
        if ($('#district-container').children().length > 0) { // 检查是否已加载区县数据
            switchTab('#tab-district', '#district-container');
        }
    });
// 为镇标签添加点击事件
    $('#tab-town').click(function() {
        if ($('#town-container').children().length > 0) { // 检查是否已加载镇数据
            switchTab('#tab-town', '#town-container');
        }
    });

    $(document).click(function (event) {
        if (!$(event.target).closest('#areaField, #areaPopup').length) {
            $('#areaPopup').hide();
        }
    });
    $('.van-overlay').click(function () {
        $('#areaPopup').hide();
        $('.van-overlay').fadeOut(); // 隐藏遮罩层
    });

    const $form = $("form[data-formid]");
    const formId = $form.attr("data-formid") ?? $("input[name=gform_submit]").val();
    const defaultArrInput = $("#cascader_wrap_input_" + formId).children('input');
    if(defaultArrInput[0] && defaultArrInput[0].value) {
        const timer4Int = setInterval(() => {
            if (areaData && isNumericStr(defaultArrInput[0].value)) {
                clearInterval(timer4Int)
                const result = findPath(areaData, defaultArrInput[0].value)
                $('#selectedArea').val(result);
            }
        }, 300)
        const timeout4Int = setTimeout(() => {
            clearInterval(timer4Int);
        }, 30000);
    }

    let defaultSelectedAreaInput = $("#selectedArea_" + formId);
    if(defaultSelectedAreaInput && typeof defaultSelectedAreaInput[0] != 'undefined' && defaultSelectedAreaInput[0].value) {
        const timer4Cascader = setInterval(() => {
            if (areaData && isNumericStr(defaultSelectedAreaInput[0].value)) {
                clearInterval(timer4Cascader);
                const result4Cascader = findPath(areaData, defaultSelectedAreaInput[0].value);
                if(result4Cascader) {
                    $("#selectedArea_" + formId).parent("p")[0].innerHTML = result4Cascader;
                }
            }
        }, 300)
        const timeout4Cascader = setTimeout(() => {
            clearInterval(timer4Cascader);
        }, 30000);
    }

    let selectAreaClassArr = $('.select_area_span');
    if(selectAreaClassArr && selectAreaClassArr.length > 0) {
        let timeArr = [], timeoutArr = []
        selectAreaClassArr.each(function(index, item) {
            const areaStr = item.innerHTML
            timeArr[index] = setInterval(() => {
                if (areaData && isNumericStr(areaStr)) {
                    clearInterval(timeArr[index]);
                    const result4Cascader = findPath(areaData, areaStr);
                    if(result4Cascader) {
                        item.innerHTML = result4Cascader
                    }
                }
            }, 300)
            timeoutArr[index] = setTimeout(() => {
                clearInterval(timer4selectAreaClassArr);
            }, 30000);
        });
    }

    function set_wppfield_cascader(val)
    {
        $("#cascader_wrap_input_"+formId).children('input').val(val)
    }
})