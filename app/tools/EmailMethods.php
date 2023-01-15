<?php
    
    namespace app\tools;
    
    use PHPMailer\PHPMailer\PHPMailer;

    class EmailMethods
    {
        /**
         * 编写公共的发邮件方法——发送邮件
         * @param array $content
         * @return string 返回结果，可能是“成功”或“失败”，由程序自行判断
         * @throws \PHPMailer\PHPMailer\Exception
         */
        function sendEmail(array $content = [
            'Host' => '',                 // 服务器
            'Port' => '',                 // 端口
            'Username' => '',             // 邮箱的用户名
            'Password' => '',             // 邮箱授权码（需要申请）
            'setFrom' => [],              // 发件人
            'addAddress' => [],           // 收件人
            'addReplyTo' => [],           // 回复的时候回复给哪个邮箱 建议和发件人一致
            'addCC' => [],                // 抄送
            'addBCC' => [],               // 密送
            'addAttachment' => '',        // 添加附件
            'Subject' => '',              // 邮件标题
            'Body' => '',                 // 邮件内容
            'AltBody' => '',              // 如果邮件客户端不支持HTML则显示此内容
        ]): string
        {
            // 创建对象
            $email = new PHPMailer(true);
            $email->isSMTP();               //使用SMTP协议
            $email->isHTML(true);           //是否以HTML文档格式发送
            $email->SMTPAuth = true;        //启用SMTP验证功能
            $email->SMTPSecure = "ssl";     //加密方式（不要随便改成大写！！！）
            $email->CharSet = "UTF-8";       //设定邮件编码
            // $email->SMTPDebug = false;      //设置为 true 可以查看具体的发送日志
        
            //获取参数
            $email->Host = $content['Host'];
            $email->Port = $content['Port'];
            $email->Username = $content['Username'];
            $email->Password = $content['Password'];
            $email->setFrom($content['setFrom'][0], $content['setFrom'][1]);
            $email->addAddress($content['addAddress'][0], $content['addAddress'][1]);
            if (!empty($content['addReplyTo'])) {
                $email->addReplyTo($content['addReplyTo'][0], $content['addReplyTo'][1]);
            }
            if (!empty($content['addCC'])) {
                $email->addCC($content['addCC'][0], $content['addCC'][1]);
            }
            if (!empty($content['addBCC'])) {
                $email->addBCC($content['addBCC'][0], $content['addBCC'][1]);
            }
            if (!empty($content['addAttachment'])) {
                $email->addAttachment = $content['addAttachment'];
            }
            $email->Subject = $content['Subject'];
            $email->Body = $content['Body'];
            if (!empty($content['AltBody'])) {
                $email->AltBody = $content['AltBody'];
            }
            $res = $email->send();
            if ($res) {
                return '验证码已发送到您的邮箱！';
            } else {
                return '验证码获取失败···';
            }
        }
    }