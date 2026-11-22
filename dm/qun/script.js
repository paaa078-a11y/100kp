// 使用立即执行函数封装代码，避免全局污染
(function() {
    // 页面状态标志
    let isInitialized = false;
    let isProcessing = false;
    
    // DOM元素
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');
    const districtSelect = document.getElementById('district');
    const loadingElement = document.getElementById('loading');
    const qrcodeBtn = document.getElementById('qrcodeBtn');
    
    // 当前选择的二维码URL
    let currentQrcodeUrl = '';
    let isQrcodeAvailable = false;
    
    // 初始化省份选项
    function initProvinces() {
        provinceSelect.innerHTML = '<option value="">选择省份</option>';
        Object.keys(areaData).forEach(provinceCode => {
            const option = document.createElement('option');
            option.value = provinceCode;
            option.textContent = areaData[provinceCode].name;
            provinceSelect.appendChild(option);
        });
    }
    
    // 初始化城市选项
    function initCities(provinceCode) {
        if (isProcessing) return;
        
        citySelect.innerHTML = '<option value="">选择城市</option>';
        citySelect.disabled = !provinceCode;
        
        if (provinceCode) {
            isProcessing = true;
            setTimeout(() => { // 使用setTimeout避免UI阻塞
                const cities = areaData[provinceCode].children;
                Object.keys(cities).forEach(cityCode => {
                    const option = document.createElement('option');
                    option.value = cityCode;
                    option.textContent = cities[cityCode].name;
                    citySelect.appendChild(option);
                });
                isProcessing = false;
                updateButtonState();
            }, 0);
        } else {
            updateButtonState();
        }
    }
    
    // 初始化区县选项
    function initDistricts(provinceCode, cityCode) {
        if (isProcessing) return;
        
        districtSelect.innerHTML = '<option value="">选择区县</option>';
        districtSelect.disabled = !cityCode;
        
        if (provinceCode && cityCode) {
            isProcessing = true;
            setTimeout(() => {
                const districts = areaData[provinceCode].children[cityCode].children;
                Object.keys(districts).forEach(districtCode => {
                    const option = document.createElement('option');
                    option.value = districtCode;
                    option.textContent = districts[districtCode].name;
                    districtSelect.appendChild(option);
                });
                isProcessing = false;
                updateButtonState();
            }, 0);
        } else {
            updateButtonState();
        }
    }
    
    // 更新按钮状态
    function updateButtonState() {
        const provinceCode = provinceSelect.value;
        const cityCode = citySelect.value;
        const districtCode = districtSelect.value;
        
        if (provinceCode && cityCode && districtCode) {
            qrcodeBtn.classList.remove('disabled');
            qrcodeBtn.classList.add('active');
        } else {
            qrcodeBtn.classList.add('disabled');
            qrcodeBtn.classList.remove('active');
            isQrcodeAvailable = false;
        }
    }
    
    // 保存当前选择
    function saveSelection() {
        if (!isInitialized) return;
        
        const selection = {
            province: provinceSelect.value,
            city: citySelect.value,
            district: districtSelect.value,
            timestamp: Date.now()
        };
        
        // 使用sessionStorage并添加时间戳
        try {
            sessionStorage.setItem('areaSelection', JSON.stringify(selection));
        } catch (e) {
            console.warn('无法保存选择:', e);
        }
    }
    
    // 恢复选择
    function restoreSelection() {
        try {
            const saved = sessionStorage.getItem('areaSelection');
            if (!saved) return;
            
            const selection = JSON.parse(saved);
            // 检查数据是否过期（5分钟内有效）
            if (Date.now() - (selection.timestamp || 0) > 300000) {
                sessionStorage.removeItem('areaSelection');
                return;
            }
            
            // 恢复省份选择
            if (selection.province) {
                provinceSelect.value = selection.province;
                initCities(selection.province);
                
                // 恢复城市选择
                if (selection.city) {
                    setTimeout(() => {
                        if (citySelect.querySelector(`option[value="${selection.city}"]`)) {
                            citySelect.value = selection.city;
                            initDistricts(selection.province, selection.city);
                            
                            // 恢复区县选择
                            if (selection.district) {
                                setTimeout(() => {
                                    if (districtSelect.querySelector(`option[value="${selection.district}"]`)) {
                                        districtSelect.value = selection.district;
                                        districtSelect.dispatchEvent(new Event('change'));
                                    }
                                }, 50);
                            }
                        }
                    }, 50);
                }
            }
        } catch (e) {
            console.warn('恢复选择失败:', e);
            sessionStorage.removeItem('areaSelection');
        }
    }
    
    // 初始化页面
    function initialize() {
        if (isInitialized) return;
        
        // 添加移动端特定处理
        if (/Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
            // 防止iOS橡皮筋效果导致页面刷新
            document.body.style.overscrollBehavior = 'contain';
            
            // 禁用下拉刷新
         //    document.addEventListener('touchmove', function(e) {
          //       if (window.scrollY <= 0) {
          //           e.preventDefault();
         //        }
         //    }, { passive: false });
            
            // 防止表单元素缩放
            document.querySelectorAll('select').forEach(select => {
                select.style.fontSize = '16px';
            });
        }
        
        initProvinces();
        restoreSelection();
        
        // 事件监听
        provinceSelect.addEventListener('change', handleProvinceChange);
        citySelect.addEventListener('change', handleCityChange);
        districtSelect.addEventListener('change', handleDistrictChange);
        qrcodeBtn.addEventListener('click', handleQrcodeClick);
        
        isInitialized = true;
    }
    
    // 事件处理函数
    function handleProvinceChange() {
        const provinceCode = this.value;
        initCities(provinceCode);
        currentQrcodeUrl = '';
        isQrcodeAvailable = false;
        saveSelection();
    }
    
    function handleCityChange() {
        const provinceCode = provinceSelect.value;
        const cityCode = this.value;
        initDistricts(provinceCode, cityCode);
        currentQrcodeUrl = '';
        isQrcodeAvailable = false;
        saveSelection();
    }
    
    function handleDistrictChange() {
        const provinceCode = provinceSelect.value;
        const cityCode = citySelect.value;
        const districtCode = this.value;
        
        if (provinceCode && cityCode && districtCode) {
            const provinceName = areaData[provinceCode].name;
            const cityName = areaData[provinceCode].children[cityCode].name;
            const districtName = areaData[provinceCode].children[cityCode].children[districtCode].name;
            
            loadingElement.style.display = 'block';
            
            const qrcodeFilename = `${provinceName}_${cityName}_${districtName}.png`.replace(/\s+/g, '_');
            currentQrcodeUrl = `qrcodes/${qrcodeFilename}`;
            
            const testImage = new Image();
            testImage.onload = function() {
                loadingElement.style.display = 'none';
                isQrcodeAvailable = true;
                qrcodeBtn.classList.add('active');
                saveSelection();
            };
            
            testImage.onerror = function() {
                loadingElement.style.display = 'none';
                isQrcodeAvailable = false;
                qrcodeBtn.classList.remove('active');
                alert('该地区的二维码未找到，请联系管理员');
                currentQrcodeUrl = '';
            };
            
            testImage.src = currentQrcodeUrl;
        } else {
            isQrcodeAvailable = false;
            qrcodeBtn.classList.remove('active');
        }
        
        saveSelection();
    }
    
    function handleQrcodeClick(e) {
        if (!isQrcodeAvailable) {
            e.preventDefault();
            return;
        }
        
        const features = 'width=500,height=500';
        const provinceName = areaData[provinceSelect.value].name;
        const cityName = areaData[provinceSelect.value].children[citySelect.value].name;
        const districtName = areaData[provinceSelect.value].children[citySelect.value].children[districtSelect.value].name;
        const windowName = `${provinceName}_${cityName}_${districtName}_二维码`;
        
        // 移动端使用_blank目标
        const target = /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent) ? '_blank' : windowName;
        window.open(currentQrcodeUrl, target, features);
    }
    
    // 页面加载完成后初始化
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(initialize, 0);
    } else {
        document.addEventListener('DOMContentLoaded', initialize);
    }
    
    // 处理移动端页面恢复
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            initialize();
        }
    });
})();