<?php
    
    namespace app\controller;
    
    // 引入有关邮箱的依赖，打开CMD，在项目根目录下执行“composer require phpmailer/phpmailer”命令
    // 注意！在服务器端要单独使用composer的命令安装
    use app\tools\EmailMethods;
    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\PHPMailer;
    
    /**
     * 有关邮箱的操作
     */
    class TodoEmail
    {
        /**
         * 编写公共的发邮件方法——发送邮件
         * @param array $content
         * @return string 返回结果，可能是“成功”或“失败”，由程序自行判断
         * @throws Exception
         */
        function sendEmail(array $content = [
            'Host' => '',                 //服务器
            'Port' => '',                 //端口
            'Username' => '',             //邮箱的用户名
            'Password' => '',             //邮箱授权码（需要申请）
            'setFrom' => [],              //发件人
            'addAddress' => [],           //收件人
            'addReplyTo' => [],           //回复的时候回复给哪个邮箱 建议和发件人一致
            'addCC' => [],                //抄送
            'addBCC' => [],               //密送
            'addAttachment' => '',        //添加附件
            'Subject' => '',              //邮件标题
            'Body' => '',                 //邮件内容
            'AltBody' => '',              //如果邮件客户端不支持HTML则显示此内容
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
                return '验证码获取失败';
            }
        }
        
        /**
         * 发送邮件的方法
         */
        function postEmail()
        {
            $emailMethods = new EmailMethods();
            $message = json_decode(file_get_contents("php://input"), true);
            $userEmail = json_decode(sprintf('"%s"', $message['email']));
            $verificationCode = json_decode(sprintf('"%s"', $message['verificationCode']));
            try {
                echo $this->sendEmail([
                    // QQ邮箱的服务器，这里是服务器用的
                    'Host' => 'smtp.qq.com',
                    // 端口
                    'Port' => '465',
                    // 邮箱的用户名
                    'Username' => '2690085099@qq.com',
                    // 邮箱授权码（需要申请）
                    'Password' => 'dmjtlmjfqijcdfba',
                    // 发件人
                    'setFrom' => ['2690085099@qq.com', '元昱'],
                    // 收件人的邮箱，这是用户的邮箱，用户要注册
                    'addAddress' => [$userEmail, '元昱'],           //收件人
                    // 回复的时候回复给哪个邮箱 建议和发件人一致
                    'addReplyTo' => ['2690085099@qq.com', '元昱'],
                    // 抄送（在发送邮件时，将内容同时发送给其他人联系人，其他人能够看到被CC的成员）
                    'addCC' => [],
                    // 密送（看不到被BCC的成员）
                    'addBCC' => [],
                    // 添加附件
                    'addAttachment' => '',
                    // 邮件标题
                    'Subject' => '待办事项网站 客服',
                    // 邮件内容（如果要在字符串里嵌入变量，要用双引号""来包裹，而不是单引号''）
//                    'Body' => "<h1>这里是邮件内容</h1><p>您的验证码为：$verificationCode</p>",
                    'Body' => $emailMethods->getHtmlEmail($userEmail,$verificationCode),
                    // 如果邮件客户端不支持HTML则显示此内容
                    'AltBody' => "待办事项客服：尊敬的{$userEmail}客户，您好！您的验证码为：{$verificationCode}",
                ]);
            } catch (Exception $e) {
                exit($e);
            }
        }
    }