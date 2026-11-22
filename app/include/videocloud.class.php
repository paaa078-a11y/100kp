<?php

include_once APP_PATH . 'js/video/aliyun-php-sdk/aliyun-php-sdk-core/Config.php';

use vod\Request\V20170321 as vod;

/*
 * 视频上传、转码、加密、播放云服务：
 * 阿里云“视频点播”服务、https://vod.console.aliyun.com
 */

interface video_interface
{
    /**
     * 获取上传凭证
     * @param $fileName 文件名，例如：abc.mp4
     * @param $size 文件大小（字节数）， 例如： 32393
     */
    function upload($fileName, $size);
}

class video
{
    //当前视频上传服务使用哪个平台
    private $server = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $config;

        $this->server = new alivideo();
    }

    //魔术方法：把所有当前类不存在的方法调用转移到调用server对应方法
    public function __call($method, $params)
    {
        if (!$this->server || !is_callable(array($this->server, $method))) {
            return false;
        }
        return call_user_func_array(array($this->server, $method), $params);
    }

    public function show($video)
    {
        $fileId = $video['file_id'];
        $server = new alivideo();
        return $server->show($fileId);
    }

    public function deleteVideos($videos)
    {

        $aliFileIds = array();
        foreach ($videos as $r) {
            $aliFileIds [] = $r['file_id'];
        }
        if ($aliFileIds) {
            $ali = new alivideo();
            
            $ali->delete(implode(',', $aliFileIds));
        }
    }

}

class commonVideo implements video_interface
{
    public function upload($fileName, $size){}

    //保存数据到video表
    protected function insertVideo($fileId, $size)
    {

        global $db, $db_config, $config;
        $now = time();
        $query = $db->query("INSERT INTO {$db_config['def']}video_info (`file_id`, `size`, `create_time`) VALUE ('{$fileId}', {$size}, {$now});");

        return $query ? $db->insert_id() : '';
    }
}

class alivideo extends commonVideo
{
    private $client;
    private $regionId;
    private $tplId;//转换模板ID
    private $cateId;//视频分类ID

    public function __construct()
    {

        global $config;

        // 点播服务所在的Region，国内请填cn-shanghai，不要填写别的区域
        $this->regionId     =   'cn-shanghai';
        $access_key_id      =   isset($config['video_ali_access_key_id']) && $config['video_ali_access_key_id'] != '' ? $config['video_ali_access_key_id'] : '';
        $access_key_secret  =   isset($config['video_ali_access_key_secret']) && $config['video_ali_access_key_secret'] != '' ? $config['video_ali_access_key_secret'] : '';
        $this->tplId        =   isset($config['video_ali_tplid']) && $config['video_ali_tplid'] != '' ? $config['video_ali_tplid'] : '';
        $this->cateId       =   isset($config['video_ali_cid']) && $config['video_ali_cid'] != '' ? $config['video_ali_cid'] : '';

        $profile            =   DefaultProfile::getProfile($this->regionId, $access_key_id, $access_key_secret);
        $this->client       =   new DefaultAcsClient($profile);
    }

    /**
     *获取视频上传凭证和地址
     * @param $client
     * @param $tplId string 加密转码模板id
     * @param $cateId string 视频分类id
     * @param $fileName
     * @return
     */
    private function create_upload_video($client, $tplId, $cateId, $fileName)
    {

        $request = new vod\CreateUploadVideoRequest();

        global $config;
        //视频源文件标题(必选)
        $request->setTitle($config['sy_webname'].'-视频探秘-'.$fileName);

        //视频源文件名称，必须包含扩展名(必选)
        $request->setFileName($fileName);

        //视频源文件字节数(可选)
        // $request->setFileSize(0);

        //视频源文件描述(可选)
        // $request->setDescription('视频描述');

        //自定义视频封面URL地址(可选)
        // $request->setCoverURL("http://cover.sample.com/sample.jpg");

        //上传所在区域IP地址(可选)
        // $request->setIP("127.0.0.1");

        //视频标签，多个用逗号分隔(可选)
        // $request->setTags("标签1,标签2");

        //视频分类ID(可选)
        $request->setCateId($cateId);

        //设置加密转码模板id
        $request->setTemplateGroupId($tplId);

        $response = $client->getAcsResponse($request);
        return $response;
    }

    //刷新视频上传凭证
    private function refresh_upload_video($client, $fileId)
    {
        $request = new vod\RefreshUploadVideoRequest();
        //视频ID(必选)
        $request->setVideoId($fileId);
        $response = $client->getAcsResponse($request);
        return $response;
    }

    /**
     * 阿里云 “视频点播”服务 获取上传地址、上传凭证
     * 使用AK初始化VOD客户端
     * 获取视频上传地址和凭证
     * 上传本地文件：js sdk方式上传（为了视频文件不走phpyun app所在服务器，直接从web端上传到阿里云）
     *
     * @param $fileName
     * @param $size
     * @return array
     */
    public function upload($fileName, $size)
    {

        $createResponse =   $this->create_upload_video($this->client, $this->tplId, $this->cateId, $fileName);
        $vid    =   $this->insertVideo($createResponse->VideoId, $size);

        return array(
            'UploadAuth'    =>  $createResponse->UploadAuth,
            'UploadAddress' =>  $createResponse->UploadAddress,
            'fileId'        =>  $createResponse->VideoId,
            'vid'           =>  $vid
        );
    }

    //上传超时后的续传，重新申请续传凭证
    public function resumeUpload($fileId)
    {

        $response = $this->refresh_upload_video($this->client, $fileId);
        return array(
            'UploadAuth' => $response->UploadAuth
        );
    }

    //查询视频基本信息
    public function getinfo($fileId)
    {

        $request = new vod\GetVideoInfoRequest();
        $request->setVideoId($fileId);
        $request->setAcceptFormat('JSON');
        return $this->client->getAcsResponse($request);
    }

    //查询视频播放地址
    public function getUrls($fileId)
    {
        $request = new vod\GetPlayInfoRequest();
        $request->setVideoId($fileId);
        $request->setAcceptFormat('JSON');
        $obj = $this->client->getAcsResponse($request);
        $urls = array();
        if (isset($obj->PlayInfoList->PlayInfo)) {
            foreach ($obj->PlayInfoList->PlayInfo as $r) {
                $urls [] = $r->PlayURL;
            }
        }
        return $urls;
    }

    public function show($fileId)
    {
        $request = new vod\GetVideoPlayAuthRequest();
        $request->setAcceptFormat('JSON');
        $request->setRegionId($this->regionId);
        $request->setVideoId($fileId);
        $response = $this->client->getAcsResponse($request);
        return array('playauth' => $response->PlayAuth);
    }

    //删除视频
    public function delete($fileId)
    {
        $request = new vod\DeleteVideoRequest();
        $request->setVideoIds($fileId);// 支持批量删除视频；videoIds为传入的视频ID列表，多个用逗号分隔
        $request->setAcceptFormat('JSON');
        return $this->client->getAcsResponse($request);
    }

    //视频源文件信息
    function get_mezzanine_info($videoId)
    {
        $request = new vod\GetMezzanineInfoRequest();
        $request->setVideoId($videoId);
        $request->setAuthTimeout(3600 * 5);   // 原片下载地址过期时间，单位：秒，默认为3600秒
        $request->setAcceptFormat('JSON');
        return $this->client->getAcsResponse($request);
    }

}