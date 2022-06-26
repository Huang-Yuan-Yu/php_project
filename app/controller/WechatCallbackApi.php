<?php
    
    namespace app\controller;
    
    // 我自定义的Token
    define("TOKEN", "ASD13695102899");
    $wechatObj = new WechatCallbackApi();
    $wechatObj->valid();
    
    /**
     * 微信预拉取Token认证
     */
    class WechatCallbackApi
    {
        public function valid()
        {
            $echoStr = $_GET["echostr"];
            
            //valid signature , option
            if ($this->checkSignature()) {
                echo $echoStr;
                exit;
            }
        }
        
        public function responseMsg()
        {
            // 获取post数据，可能由于环境不同
            $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
            
            // 提取POST数据
            if (!empty($postStr)) {
                $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
                $keyword = trim($postObj->Content);
                $time = time();
                $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            <FuncFlag>0</FuncFlag>
                            </xml>";
                if (!empty($keyword)) {
                    $msgType = "text";
                    $contentStr = "Welcome to wechat world!";
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                } else {
                    echo "Input something...";
                }
                
            } else {
                echo "";
                exit;
            }
        }
        
        private function checkSignature()
        {
            $signature = $_GET["signature"];
            $timestamp = $_GET["timestamp"];
            $nonce = $_GET["nonce"];
            
            $token = TOKEN;
            $tmpArr = array($token, $timestamp, $nonce);
            sort($tmpArr);
            $tmpStr = implode($tmpArr);
            $tmpStr = sha1($tmpStr);
            
            if ($tmpStr == $signature) {
                return true;
            } else {
                return false;
            }
        }
    }