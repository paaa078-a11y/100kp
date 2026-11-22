<?php

/**
 * 对微信小程序用户加密数据的解密示例代码.
 *
 * @copyright Copyright (c) 1998-2014 Tencent Inc.
 */


include_once "errorCode.php";


class WXBizDataCrypt
{
    private $appid;
	private $sessionKey;

	/**
	 * 构造函数
	 * @param $sessionKey string 用户在小程序登录后获取的会话密钥
	 * @param $appid string 小程序的appid
	 */
	public function __construct( $appid, $sessionKey)
	{
		$this->sessionKey = $sessionKey;
		$this->appid = $appid;
	}


	/**
	 * 微信小程序签名解密 检验数据的真实性，并且获取解密后的明文.
	 * @param $encryptedData string 加密的用户数据
	 * @param $iv string 与用户数据一同返回的初始向量
	 * @param $data string 解密后的原文
     *
	 * @return int 成功0，失败返回对应的错误码
	 */
	public function decryptData( $encryptedData, $iv, &$data )
	{
		if (strlen($this->sessionKey) != 24) {
			return ErrorCode::$IllegalAesKey;
		}
		$aesKey=base64_decode($this->sessionKey);

        
		if (strlen($iv) != 24) {
			return ErrorCode::$IllegalIv;
		}
		$aesIV=base64_decode($iv);

		$aesCipher=base64_decode($encryptedData);

		$result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

		$dataObj=json_decode( $result );
		if( $dataObj  == NULL )
		{
			return ErrorCode::$IllegalBuffer;
		}
		if( $dataObj->watermark->appid != $this->appid )
		{
			return ErrorCode::$IllegalBuffer;
		}
		$data = $result;
		return ErrorCode::$OK;
	}

    /**
     * 百度小程序签名解密 检验数据的真实性，并且获取解密后的明文.
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptBaiduData( $encryptedData, $iv, &$data )
    {
        if (strlen($this->sessionKey) != 32) {
            return ErrorCode::$IllegalAesKey;
        }
        $aesKey=base64_decode($this->sessionKey);

        if (strlen($iv) != 24) {
            return ErrorCode::$IllegalIv;
        }
        $aesIV=base64_decode($iv);

        $aesCipher=base64_decode($encryptedData);

        $result=openssl_decrypt( $aesCipher, "AES-192-CBC", $aesKey, 1, $aesIV);

        if (!$result){
            return ErrorCode::$IllegalBuffer;
        }
        // trim pkcs#7 padding
        $pad = ord(substr($result, -1));
        $pad = ($pad < 1 || $pad > 32) ? 0 : $pad;
        $result = substr($result, 0, strlen($result) - $pad);

        // trim header
        $result = substr($result, 16);
        // get content length
        $unpack = unpack("Nlen/", substr($result, 0, 4));

        // get content
        $content = substr($result, 4, $unpack['len']);
        // get app_key
        $app_key_decode = substr($result, $unpack['len'] + 4);

        $data = $content;

        return  $app_key_decode ? ErrorCode::$OK : ErrorCode::$IllegalBuffer;
    }
     /**
     * 抖音小程序签名解密 检验数据的真实性，并且获取解密后的明文.
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptToutiaoData($encryptedData, $iv, &$data )
	{
		if (strlen($this->sessionKey) != 24) {
			return ErrorCode::$IllegalAesKey;
		}
		$aesKey=base64_decode($this->sessionKey);

        
		if (strlen($iv) != 24) {
			return ErrorCode::$IllegalIv;
		}
		$aesIV=base64_decode($iv);

		$aesCipher=base64_decode($encryptedData);

		$result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

		$dataObj=json_decode( $result );
		if( $dataObj  == NULL )
		{
			return ErrorCode::$IllegalBuffer;
		}
		if( $dataObj->watermark->appid != $this->appid )
		{
			return ErrorCode::$IllegalBuffer;
		}
		$data = $result;
		return ErrorCode::$OK;
	}
	/**
	 * 支付宝小程序签名解密 检验数据的真实性，并且获取解密后的明文.
	 * @param $encryptedData string 加密的用户数据
	 * @param $data string 解密后的原文
	 *
	 * @return int 成功0，失败返回对应的错误码
	 */
	public function decryptAlipayData( $encryptedData, $aesKey, &$data )
	{
	    if (substr(PHP_VERSION, 0, 1) == '7') {
	        // php7以上
	        $result = openssl_decrypt(base64_decode($encryptedData), 'AES-128-CBC', base64_decode($aesKey),OPENSSL_RAW_DATA);
	        $dataArr = json_decode($result, true);
	        
	        $data = $result;
	    } else {
	        // php7以下
	        //验签代码
	        $str = base64_decode($encryptedData);
	        $screct_key = base64_decode($aesKey);
	        
	        //设置全0的IV
	        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_CBC);
	        $iv = str_repeat("\0", $iv_size);
	        
	        $decrypt_str = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $screct_key, $str, MCRYPT_MODE_CBC, $iv);
	        $result = $this->stripPKSC7Padding($decrypt_str);
	        $dataArr = json_decode($result, true);
	        
	        $data = $result;
	    }
	    // 为了和其他小程序统一，解密正常的，返回0
	    if (isset($dataArr['code']) &&  $dataArr['code']== 10000){
	        $dataArr['code'] = 0;
	    }
	    return $dataArr['code'];
	}
	/**
	 * 移去填充算法
	 * @param string $source
	 * @return string
	 */
	function stripPKSC7Padding($source){
	    $char = substr($source, -1);
	    $num = ord($char);
	    if($num==62)return $source;
	    $source = substr($source,0,-$num);
	    return $source;
	}
	
	function alipayErr($code){
	    
	    $ErrorCode =  array(
	        10000 => 'OK',
	        40001 => '缺少加密配置',
	        40002 => '加密异常',
	        40003 => '无效的授权关系',
	        40006 => 'ISV权限不足，建议在开发者中心检查对应功能是否已经添加'
	    );
	    return $ErrorCode[$code];
	}
}

