        // 城市代码到城市名称的映射
        const cityMap = {
            'sh': '上海',
            'bj': '北京',
            'gz': '广州',
            'sz': '深圳',
            'hz': '杭州',
            'cd': '成都',
            'nj': '南京',
            'wh': '武汉',
            'xa': '西安',
            'cq': '重庆'
        };
        
        // 城市图标映射
        const cityIcons = {
            '上海': '🏙️',
            '北京': '🏯',
            '广州': '🍵',
            '深圳': '🌆',
            '杭州': '🏞️',
            '成都': '🐼',
            '南京': '🏛️',
            '武汉': '🌸',
            '西安': '🗿',
            '重庆': '🌉'
        };
        
        // 获取当前域名
        const currentDomain = window.location.hostname;
        
        // 提取二级域名
        const domainParts = currentDomain.split('.');
        const cityCode = domainParts.length > 2 ? domainParts[0] : '';
        
        // 根据二级域名获取城市名称
        const cityName = cityMap[cityCode] || '未知城市';
        const cityIcon = cityIcons[cityName] || '🏙️';
        
        // 更新页面内容
        document.getElementById('cityName').textContent = cityName;
        document.getElementById('content').textContent = `${cityName}，我爱你！`;
        document.getElementById('currentDomain').textContent = currentDomain;
        
        // 更新页面标题
        document.title = `${cityName}欢迎您`;
        
        // 更新城市图标
        document.querySelector('.city-icon').textContent = cityIcon;
        
        // 更新keywords和description元标签
        const keywords = `${cityName},${cityName}我爱你`;
        const description = `${cityName}城市是非常漂亮的`;
        
        document.getElementById('metaKeywords').setAttribute('content', keywords);
        document.getElementById('metaDescription').setAttribute('content', description);
        
        // 在页面上显示keywords和description（仅用于演示）
        document.getElementById('keywordsDisplay').textContent = keywords;
        document.getElementById('descriptionDisplay').textContent = description;
        
        // 如果城市未知，添加提示
        if (!cityMap[cityCode]) {
            document.getElementById('content').innerHTML += 
                '<br><small style="color:#888;font-size:1rem;">提示：当前域名可能不是城市子域名</small>';
        }