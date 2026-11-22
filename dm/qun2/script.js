// 全局状态管理
const appState = {
    isLoading: false,
    searchTimer: null,
    currentSelection: {
        province: '',
        city: '',
        district: ''
    }
};

// DOM加载完成后初始化
document.addEventListener('DOMContentLoaded', function() {
    // 初始化地区数据
    initRegionData();
    
    // 填充省份数据
    fillProvinces();
    
    // 设置搜索功能
    setupSearch();
    
    // 设置下拉框事件
    setupSelectEvents();
    
    // 显示默认二维码
    showDefaultQR();
    
    // 性能监控
    if (typeof window.PerformanceObserver !== 'undefined') {
        const observer = new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
                console.log('[性能监控]', entry.name, entry.duration);
            }
        });
        observer.observe({ entryTypes: ['measure'] });
    }
});

// 初始化地区数据
function initRegionData() {
    if (!window.allRegions) {
        window.allRegions = [];
        
        // 遍历省份
        Object.keys(areaData).forEach(province => {
            allRegions.push({
                type: 'province',
                name: province,
                fullName: province
            });
            
            // 遍历城市
            const cities = areaData[province].cities;
            Object.keys(cities).forEach(city => {
                allRegions.push({
                    type: 'city',
                    name: city,
                    fullName: `${province} ${city}`,
                    province: province
                });
                
                // 遍历区县
                const districts = cities[city].districts;
                districts.forEach(district => {
                    allRegions.push({
                        type: 'district',
                        name: district,
                        fullName: `${province} ${city} ${district}`,
                        province: province,
                        city: city
                    });
                });
            });
        });
    }
}

// 设置搜索功能
function setupSearch() {
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    
    // 输入事件
    searchInput.addEventListener('input', function() {
        clearTimeout(appState.searchTimer);
        
        const keyword = this.value.trim();
        searchResults.innerHTML = '';
        searchResults.style.display = 'none';
        
        if (keyword.length < 1) return;
        
        appState.searchTimer = setTimeout(() => {
            performSearch(keyword, searchResults);
        }, 300);
    });
    
    // 点击其他地方关闭搜索结果
    document.addEventListener('click', function(e) {
        if (e.target !== searchInput) {
            searchResults.style.display = 'none';
        }
    });
    
    // 手机端触摸事件处理
    searchInput.addEventListener('touchstart', function(e) {
        e.stopPropagation();
    }, { passive: true });
}

// 执行搜索
function performSearch(keyword, container) {
    if (appState.isLoading) return;
    
    const results = allRegions.filter(region => 
        region.name.includes(keyword) || 
        region.fullName.includes(keyword)
    ).slice(0, 10);
    
    if (results.length === 0) {
        container.style.display = 'none';
        return;
    }
    
    container.innerHTML = '';
    results.forEach(result => {
        const item = document.createElement('div');
        item.className = 'search-result-item';
        item.textContent = result.fullName;
        
        item.addEventListener('click', function() {
            handleSearchResultClick(result);
            container.style.display = 'none';
            document.getElementById('searchInput').value = result.fullName;
        });
        
        container.appendChild(item);
    });
    
    container.style.display = 'block';
}

// 设置下拉框事件
function setupSelectEvents() {
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');
    const districtSelect = document.getElementById('district');
    
    // 使用事件委托
    provinceSelect.addEventListener('change', function() {
        if (appState.isLoading) return;
        appState.currentSelection.province = this.value;
        loadCities();
    }, { passive: true });
    
    citySelect.addEventListener('change', function() {
        if (appState.isLoading) return;
        appState.currentSelection.city = this.value;
        loadDistricts();
    }, { passive: true });
    
    districtSelect.addEventListener('change', function() {
        if (appState.isLoading) return;
        appState.currentSelection.district = this.value;
        showQRCode();
    }, { passive: true });
}

// 填充省份数据
function fillProvinces() {
    const provinceSelect = document.getElementById('province');
    
    // 清空现有选项
    provinceSelect.innerHTML = '<option value="">请选择省份</option>';
    
    // 按拼音排序省份
    const sortedProvinces = Object.keys(areaData).sort((a, b) => {
        return a.localeCompare(b, 'zh-CN');
    });
    
    // 添加省份选项
    sortedProvinces.forEach(province => {
        const option = document.createElement('option');
        option.value = province;
        option.textContent = province;
        provinceSelect.appendChild(option);
    });
}

// 加载城市数据
function loadCities() {
    if (appState.isLoading) return;
    appState.isLoading = true;
    
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');
    const districtSelect = document.getElementById('district');
    
    const selectedProvince = provinceSelect.value;
    
    // 重置城市和区县选择
    citySelect.innerHTML = '<option value="">请选择城市</option>';
    citySelect.disabled = !selectedProvince;
    
    districtSelect.innerHTML = '<option value="">请选择区县</option>';
    districtSelect.disabled = true;
    
    // 显示默认二维码
    showDefaultQR();
    
    if (!selectedProvince) {
        appState.isLoading = false;
        return;
    }
    
    // 使用requestAnimationFrame优化性能
    requestAnimationFrame(() => {
        // 获取该省份下的城市
        const cities = areaData[selectedProvince]?.cities || {};
        const sortedCities = Object.keys(cities).sort((a, b) => {
            return a.localeCompare(b, 'zh-CN');
        });
        
        // 添加城市选项
        sortedCities.forEach(city => {
            const option = document.createElement('option');
            option.value = city;
            option.textContent = city;
            citySelect.appendChild(option);
        });
        
        appState.isLoading = false;
    });
}

// 加载区县数据
function loadDistricts() {
    if (appState.isLoading) return;
    appState.isLoading = true;
    
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');
    const districtSelect = document.getElementById('district');
    
    const selectedProvince = provinceSelect.value;
    const selectedCity = citySelect.value;
    
    // 重置区县选择
    districtSelect.innerHTML = '<option value="">请选择区县</option>';
    districtSelect.disabled = !selectedCity;
    
    // 显示默认二维码
    showDefaultQR();
    
    if (!selectedCity) {
        appState.isLoading = false;
        return;
    }
    
    // 使用requestAnimationFrame优化性能
    requestAnimationFrame(() => {
        // 获取该城市下的区县
        const districts = areaData[selectedProvince]?.cities[selectedCity]?.districts || [];
        const sortedDistricts = [...districts].sort((a, b) => {
            return a.localeCompare(b, 'zh-CN');
        });
        
        if (sortedDistricts.length === 0) {
            // 如果没有区县数据，直接显示二维码
            showQRCode();
            appState.isLoading = false;
            return;
        }
        
        // 添加区县选项
        sortedDistricts.forEach(district => {
            const option = document.createElement('option');
            option.value = district;
            option.textContent = district;
            districtSelect.appendChild(option);
        });
        
        appState.isLoading = false;
    });
}

// 显示二维码
function showQRCode() {
    if (appState.isLoading) return;
    
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');
    const districtSelect = document.getElementById('district');
    
    const selectedProvince = provinceSelect.value;
    const selectedCity = citySelect.value;
    let selectedDistrict = districtSelect.value;
    
    if (!selectedProvince || !selectedCity) {
        showDefaultQR();
        return;
    }
    
    // 构造显示文本
    const displayText = `${selectedProvince} ${selectedCity} ${selectedDistrict || ''}`.trim();
    document.getElementById('selectedArea').textContent = displayText;
    
    // 构造文件名
    let fileName;
    if (selectedDistrict) {
        fileName = `${selectedProvince}_${selectedCity}_${selectedDistrict}.png`;
    } else {
        fileName = `${selectedProvince}_${selectedCity}_${selectedCity}.png`;
    }
    
    // 更新显示
    const qrImage = document.getElementById('qrImage');
    qrImage.src = `qrcodes/${fileName}`;
    
    // 图片加载失败时使用默认图片
    qrImage.onerror = function() {
        this.src = 'qrcodes/hao.png';
        document.getElementById('selectedArea').textContent = '错误：未找到该地区的群二维码！';
    };
    
    document.getElementById('qrContainer').style.display = 'block';
}

// 显示默认二维码
function showDefaultQR() {
    document.getElementById('qrImage').src = 'qrcodes/hao.png';
    document.getElementById('selectedArea').textContent = '请选择地区，查看群二维码';
}

// 处理搜索结果点击
function handleSearchResultClick(result) {
    switch(result.type) {
        case 'province':
            selectProvince(result.name);
            break;
        case 'city':
            selectProvince(result.province);
            setTimeout(() => selectCity(result.name), 100);
            break;
        case 'district':
            selectProvince(result.province);
            setTimeout(() => selectCity(result.city), 100);
            setTimeout(() => selectDistrict(result.name), 200);
            break;
    }
}

// 选择省份
function selectProvince(provinceName) {
    const provinceSelect = document.getElementById('province');
    for (let i = 0; i < provinceSelect.options.length; i++) {
        if (provinceSelect.options[i].text === provinceName) {
            provinceSelect.selectedIndex = i;
            provinceSelect.dispatchEvent(new Event('change'));
            break;
        }
    }
}

// 选择城市
function selectCity(cityName) {
    const citySelect = document.getElementById('city');
    for (let i = 0; i < citySelect.options.length; i++) {
        if (citySelect.options[i].text === cityName) {
            citySelect.selectedIndex = i;
            citySelect.dispatchEvent(new Event('change'));
            break;
        }
    }
}

// 选择区县
function selectDistrict(districtName) {
    const districtSelect = document.getElementById('district');
    for (let i = 0; i < districtSelect.options.length; i++) {
        if (districtSelect.options[i].text === districtName) {
            districtSelect.selectedIndex = i;
            districtSelect.dispatchEvent(new Event('change'));
            break;
        }
    }
}