<?php
    
    namespace app\controller;
    // 图像识别用的接口URL前缀，这里是不会变的，由百度AI开放平台提供
    use think\Exception;

    define(
        "IMAGE_URL",
        'https://aip.baidubce.com/rest/2.0/image-classify/v2/advanced_general?access_token='
    );
    // 文字识别的接口URL前缀
    define("ORC_URL", "https://aip.baidubce.com/rest/2.0/ocr/v1/accurate_basic?access_token=");
    
    class Image
    {
    
        /**
         * 发起http post请求(REST API), 并获取REST请求的结果
         * @param string $url
         * @param string $param
         * @return bool|string - http response body if succeeds, else false.
         */
        function request_post($url = '', $param = '')
        {
            if (empty($url) || empty($param)) {
                return false;
            }
            
            $postUrl = $url;
            $curlPost = $param;
            // 初始化curl
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $postUrl);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            // 要求结果为字符串且输出到屏幕上
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            // post提交方式
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
            // 运行curl
            $data = curl_exec($curl);
            curl_close($curl);
            
            return $data;
        }
        
        /**
         * 获取图形识别用的AccessToken，如果之前已经获取，则在文件中提取即可
         * 30天获取一次
         * @param $fileName :文件名称（包含后缀名）
         * @param $apiKey :申请的API Key
         * @param $secretKey :申请的Secret Key
         * @return mixed:返回Access_Token
         */
        public function getImageAccessToken($fileName, $apiKey, $secretKey)
        {
            // 如果AccessToken还没过期，那么就使用之前在文件里写入的AccessToken，大大减少向网络获取的次数
            if (json_decode(file_get_contents($fileName))->expiration > time()) {
                // 读取JSON文件，并获取之前存储的AccessToken
                return json_decode(file_get_contents($fileName))->access_token;
            } // 如果AccessToken过时了，那么就重新向网络（百度AI开放平台）获取
            else {
                $url = 'https://aip.baidubce.com/oauth/2.0/token';
                $post_data['grant_type'] = 'client_credentials';
                // 填写申请的API Key
                $post_data['client_id'] = $apiKey;
                // 填写申请的Secret Key
                $post_data['client_secret'] = $secretKey;
                $o = "";
                foreach ($post_data as $k => $v) {
                    $o .= "$k=" . urlencode($v) . "&";
                }
                $post_data = substr($o, 0, -1);
                // 获取返回的内容，要先经过JSON解码
                $response = json_decode($this->request_post($url, $post_data));
                // 给对象添加自定义属性，为“过期时间expiration”，值是“当前时间戳+Token限定的过期时间”
                $response->expiration = time() + $response->expires_in;
                // 添加完毕后，进行JSON编码并存进JSON文件中
                file_put_contents($fileName, json_encode($response));
                // 返回access_token
                return $response->access_token;
            }
        }
        
        /**
         * 调用通用图片识别接口，前端会将“网络图片的URL”传进来
         */
        public function imageIdentification()
        {
            // 接收前端传来的信息
            $request = json_decode(file_get_contents("php://input"), true);
            // 获取前端传来信息的“imageUrl属性的值”
            $img = base64_encode(file_get_contents(json_decode(sprintf('"%s"', $request['imageUrl']))));
            $body = array(
                'image' => $img
            );
            // IMAGE_URL . $this->getAccessToken()表示“接口前缀+服务器获取的AccessToken”，这里就用于请求
            $res = $this->request_post(
                IMAGE_URL . $this->getImageAccessToken(
                    "access_token.json",
                    "usT8Ccb7U3GSb4EF7WUSZbRl",
                    "GTFqE7jL4cxPIaWbOHtmp3cW89kxcASv"
                ), $body);
            // 最后返回给前端。注意！返回数据不能用echo而是用exit()
            exit($res);
        }
        
        /**
         * 调用通用图片识别接口，前端会将“图片的Base64编码结果”传进来
         */
        public function takeImage()
        {
            // 接收前端传来的信息
            $request = json_decode(file_get_contents("php://input"), true);
            // 获取前端传来信息的“imageBase64属性的值”，因为已经经过Base64编码，所以后端不用再进行编码
            $img = $request['imageBase64'];
            $body = array(
                'image' => $img
            );
            $res = $this->request_post(IMAGE_URL . $this->getImageAccessToken(
                    "access_token.json",
                    "usT8Ccb7U3GSb4EF7WUSZbRl",
                    "GTFqE7jL4cxPIaWbOHtmp3cW89kxcASv"
                ), $body);
            // 注意！返回数据不能用echo而是用exit()
            exit($res);
        }
        
        /**
         * OCR识别（高精度版），能对图片里的文字进行识别，然后返回电子版的文字
         * 此方法会传入图片的URL
         */
        public function urlOcr()
        {
            $request = json_decode(file_get_contents("php://input"), true);
            $img = base64_encode(file_get_contents(json_decode(sprintf('"%s"', $request['imageUrl']))));
            $bodys = array(
                'image' => $img
            );
            // 获取图形识别用的AccessToken
            $res = $this->request_post(ORC_URL . $this->getImageAccessToken(
                    "ocr_access_token.json",
                    "s4Zq7gBMv7TUHnpTUa0a7Gny",
                    "pyYnKuuwGsKANXsAFseSkyhMTd0niqLp"
                ), $bodys);
            exit($res);
        }
        
        /**
         * OCR识别（高精度版），能对图片里的文字进行识别，然后返回电子版的文字
         * 用户会拍照或选择相册，将Base64编码好的图片传到此接口
         */
        public function cameraOcr()
        {
            $request = json_decode(file_get_contents("php://input"), true);
            $img = $request['imageBase64'];
            $bodys = array(
                'image' => $img
            );
            // 获取图形识别用的AccessToken
            $res = $this->request_post(ORC_URL . $this->getImageAccessToken(
                    "ocr_access_token.json",
                    "s4Zq7gBMv7TUHnpTUa0a7Gny",
                    "pyYnKuuwGsKANXsAFseSkyhMTd0niqLp"
                ), $bodys);
            exit($res);
        }
    }