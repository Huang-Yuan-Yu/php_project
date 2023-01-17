<?php
    
    namespace app\controller;
    
    // 引入Model目录下的TodoListUser类——待办事项
    use app\model\TodoListData;
    use app\model\TodoListUser;
    
    // 引入有关邮箱的依赖，打开CMD，在项目根目录下执行“composer require phpmailer/phpmailer”命令
    // 注意！在服务器端要单独使用composer的命令安装
    use app\Request;
    use app\tools\EmailMethods;
    use DomainException;
    use Firebase\JWT\BeforeValidException;
    use Firebase\JWT\ExpiredException;
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
    use Firebase\JWT\SignatureInvalidException;
    use Psr\SimpleCache\InvalidArgumentException;
    use think\Console;
    use think\console\Command;
    use think\console\Output;
    use think\db\exception\DataNotFoundException;
    use think\db\exception\DbException;
    use think\db\exception\ModelNotFoundException;
    use think\db\exception\PDOException;
    use think\Exception;
    use think\facade\Cache;
    use think\facade\Db;
    use think\facade\View;
    
    // 系统使用北京时间
    date_default_timezone_set("PRC");
    // JSON Web Token的私钥（define()可以定义常量）
    define('KEY', '1gHuiop975cdashyex9Ud23ldsvm2Xq');
    // 包含X-Content-Type-Options，可以防止黑客在浏览器进行MIME类型的嗅探
    header("X-Content-Type-Options:nosniff;Content-Type:text/json;charset=utf-8");
    
    class TodoList
    {
        
        /**
         * 注册
         * @param Request $request 传入name和password参数
         */
        public function register(Request $request): string
        {
            try {
                // 添加数据到用户表中：
                TodoListUser::create(
                    [
                        // 然后将Unicode编码转换为中文，最后将变量转换为对象，即JSON解码：
                        "name" => $request['name'],
                        "password" => $request['password'],
                    ]
                );
                return "注册成功！";
            } catch (PDOException $exception) {
                return "用户名重复，请更换用户名！";
            }
        }
    
        /**
         * 游客登录要用到的匿名注册和顺便登录的接口
         * @param Request $request 传入用户名
         * @return array|string
         * @throws InvalidArgumentException
         * @throws DataNotFoundException
         * @throws DbException
         * @throws ModelNotFoundException
         */
        public function loginAnonymously(Request $request)
        {
            try {
                // 判断是否存在此用户，如果不存在，则开始注册，否则就不用注册
                $todoListUser = new TodoListUser();
                if ($todoListUser->where("name", $request['name'])->find() === null) {
                    // 添加数据到用户表中：
                    TodoListUser::create(
                        [
                            "name" => $request['name'],
                        ]
                    );
                }
                // 查询游客的信息（一定要在判断用户之后查询，如果查询不到信息会报错
                $query = $todoListUser->where("name", $request['name'])->select();
                
                // 获取当前时间，作为Token签发时间
                $nowTime = time();
                // 注意！Token里不能存放重要、敏感的内容，因为可以通过Token解析出下面的实际内容
                $token = [
                    // 签发者地址
                    'iss' => 'http://localhost:8000/',
                    // 客户端地址
                    'aud' => 'http://localhost:8080/',
                    // 签发时间
                    'iat' => $nowTime,
                    // 在什么时间之后该jwt才可用
                    'nbf' => $nowTime,
                    // 过期时间，这里以“秒”作为单位，“+600”表示过10分钟后过期——60*10=600，604800秒等于一个星期
                    'exp' => $nowTime + 604800,
                ];
                // 对Token进行编码，第一个参数为Token，第二个参数为加密公钥
                // 将编码后的Token发送给客户端
                // Cache类对应到config目录下的cache.php文件，在文件中有关于redis的配置，这里目的是创建Redis对象
                $redis = Cache::store('redis');
                // 判断是否存在缓存
                if ($redis->get('Token字符串')) {
                    // 获取缓存并赋值
                    $response['jwt'] = $redis->get('Token字符串');
                } // 如果没有缓存
                else {
                    $tokenString = JWT::encode($token, KEY, "HS256");
                    // 设置缓存
                    $redis->set('Token字符串', $tokenString);
                    $response['jwt'] = $tokenString;
                }
                // 用户头像数据
                $response['avatar'] = $query[0]->avatar;
                // 登录成功就赋值为success
                $response['result'] = '登录成功';
            } catch (PDOException $exception) {
                // 一般是浏览器指纹（name）重复了
                return "抱歉，您的设备不支持游客登录···";
            }
            
            // 最后返回一或多个属性
            return $response;
        }
        
        /**
         * QQ授权后，将信息存储到后端
         */
        public function qqLogin(Request $request)
        {
            // 判断是否存在此用户，如果不存在，则开始注册，否则就不用注册
            $todoListUser = new TodoListUser();
            if ($todoListUser->where("name", $request['name'])->find() === null) {
                // 添加数据到用户表中：
                TodoListUser::create(
                    [
                        "name" => $request['name'],
                        // 头像
                        "avatar" => $request['avatar'],
                    ]
                );
            }
        }
        
        /**
         * 用户登录的方法
         */
        public function login(Request $request)
        {
            // 判断是否存在此用户，如果不存在，则直接返回信息给前端
            $todoListUser = new TodoListUser();
            if ($todoListUser->where("name", $request['name'])->find() === null) {
                $response['result'] = "不存在此用户，请检查账号是否输入正确！";
            } else {
                try {
                    // 查询数据库的表（两个查询条件），查找有无存在此用户，若查询不到，则会报错，要异常捕获
                    $query = $todoListUser->where("name", $request['name'])->where("password", $request['password'])->select();
                    // 用户名和密码正确，则签发Token
                    if ($request['name'] == $query[0]->name && $request['password'] == $query[0]->password) {
                        // 获取当前时间，作为签发时间
                        $nowTime = time();
                        // 注意！Token里不能存放重要、敏感的内容，因为可以通过Token解析出下面的实际内容
                        $token = [
                            // 签发者地址
                            'iss' => 'http://localhost:8000/',
                            // 客户端地址
                            'aud' => 'http://localhost:8080/',
                            // 签发时间
                            'iat' => $nowTime,
                            // 在什么时间之后该jwt才可用
                            'nbf' => $nowTime,
                            /*过期时间，这里以“秒”作为单位，“+600”表示过10分钟后过期——60*10=600，604800秒等于一个星期；
                            2626560秒等于1个月*/
                            'exp' => $nowTime + 2626560,
                        ];
                        // 对Token进行编码，第一个参数为Token，第二个参数为加密公钥
                        // 将编码后的Token发送给客户端
                        $redis = Cache::store('redis');
                        // 判断是否存在缓存
                        if ($redis->get('Token字符串')) {
                            // 获取缓存并赋值
                            $response['jwt'] = $redis->get('Token字符串');
                        } // 如果没有缓存
                        else {
                            $tokenString = JWT::encode($token, KEY, "HS256");
                            // 设置缓存
                            $redis->set('Token字符串', $tokenString);
                            $response['jwt'] = $tokenString;
                        }
                        // 登录成功就赋值为success
                        $response['result'] = '登录成功';
                        // 用户头像数据
                        $response['userAvatarData'] = $query[0]->avatar;
                    }
                } catch (Exception $exception) {
                    $response['msg'] = '用户名或密码错误!';
                }
            }
            
            // 最后返回一或多个属性
            return $response;
        }
        
        /**
         * 验证Token
         */
        public function verification(Request $request)
        {
            $todoListUser = new TodoListUser();
            $avatar = $todoListUser->where("name", $request['name'])->value("avatar");
            try {
                /*注意！！！这里能获取客户端的HTTP请求头的某个字段，比如“TOKEN”
                注意要大写（否则会报“500”错,即使客户端传的token名称是小写）
                前缀固定为”HTTP-“，后面为具体的字段名。HS256方式，这里要和签发的时候对应*/
                $decoded = JWT::decode($_SERVER['HTTP_TOKEN'], new Key(KEY, "HS256"));
                $arr = $decoded;
                if (time() > $arr->exp) {
                    $response["message"] = "登录已过期，请重新登录！";
                } else {
                    // 返回用户头像数据
                    $response["avatar"] = $avatar;
                }
                return $response;
            } catch (SignatureInvalidException $e) {  //签名不正确
                // exit()表示执行此代码后，跳出函数，不执行其他函数中的其他代码，包括其他函数的代码
                exit($e->getMessage());
            } catch (BeforeValidException $e) {  // 签名在某个时间点之后才能用
                exit($e->getMessage());
            } catch (ExpiredException $e) {  // token过期
                exit($e->getMessage());
            } catch (DomainException $e) {  //客户端伪造Token
                exit($e->getMessage());
            } catch (Exception $e) {  //其他错误
                exit($e->getMessage());
            }
            /*Firebase定义了多个 throw new，我们可以捕获多个catch来定义问题，catch加入自己的业务，
            比如token过期可以用当前Token刷新一个新Token*/
        }
        
        /**
         * 获取数据，在PHP中，使用关联数组、然后进行JSON编码，就构成了JSON格式的数据
         */
        public function getObjectArray(Request $request)
        {
            // 在数据表中查找用户的数据：
            $todoListData = new TodoListData();
            $query = $todoListData->
            // 查询某个用户的所有待办事项记录
            where("name", $request['name'])
                ->select();
            // 返回查询的结果
            return $query;
        }
        
        /**
         * 添加事项
         */
        public function addData(Request $request)
        {
            // 添加数据到待办事项表中：
            TodoListData::create(
                [
                    // 然后将Unicode编码转换为中文，最后将变量转换为对象，即JSON编码：
                    "name" => $request['name'],
                    "mission" => $request['mission'],
                    "done" => $request['done'],
                ]
            );
            $todoListData = new TodoListData();
            $query = $todoListData->where("name", $request['name'])->value("time");
            return $query;
        }
        
        /**
         * 删除事项
         */
        public function deleteData(Request $request)
        {
            $todoListData = new TodoListData();
            // 删除指定用户的指定记录，使用两个条件，从而不会删错用户的信息
            $todoListData->where("name", $request['name'])->where("mission", $request['mission'])->delete();
        }
        
        /**
         * 清除已完成的事项
         * 之前清除不了事项，因为数据库的id比较乱，所以应该重置id：
         * alter table 表名 drop column id;
         * alter table 表名 add id mediumint(8) not null primary key auto_increment first;
         */
        public function clearCompletedTodo()
        {
            // 如果前端传递的不是JSON，而是数组，则使用如下表达式接收参数
            $data = json_decode(file_get_contents("php://input"), true);
            // 输出到Command控制台：
            //error_log(print_r($data, true));
            // “销毁destroy”方法，能够一次性删除多条数据，以主键作为依据，这里可以传入一个数组，里面包含表中主键的值
            TodoListData::destroy($data,true);
        }
        
        /**
         * 修改事项内容的方法
         */
        public function modificationData(Request $request)
        {
            $todoListData = new TodoListData();
            // 如果$request['beforeContent']已经存在，证明调用者发的是修改事项内容的请求（isset()检测变量是否已设置且不为null
            if (isset($request['beforeContent'])) {
                $todoListData->where("name", $request['name'])->where("mission", $request['beforeContent'])
                    ->update(["mission" => $request['mission']]);
            } // 如果是修改事项的完成情况：
            else if (isset($request['nowDone'])) {
                // 以用户名和记录内容作为条件查询，用户名和记录都不会出现重复的情况，因为在前端和后端都已做判断
                $todoListData->where("name", $request['name'])->where("mission", $request['mission'])
                    ->update(["done" => $request['nowDone']]);
            }
        }
        
        /**
         * 完成所有的待办事项
         */
        public function finishAllTodo(Request $request)
        {
            $todoListData = new TodoListData();
            $todoListData->where("name", $request['name'])
                // 查找该用户下的所有未完成的事项，将其更新为完成
                ->where("done", 0)->update(["done" => 1]);
        }
        
        /**
         * 取消完成所有待办事项
         */
        public function noFinishAllTodo(Request $request)
        {
            $todoListData = new TodoListData();
            $todoListData->where("name", $request['name'])
                // 查找该用户下的所有完成的事项，将其更新为未完成
                ->where("done", 1)->update(["done" => 0]);
        }
        
        /**
         * 返回用户上次登录的时间，以及这次登录时间
         */
        public function updateLoginTime(Request $request)
        {
            $todoListUser = new TodoListUser();
            // 查询用户上次登录的时间（一定要先查询后更新）
            $query = $todoListUser->where("name", $request['name'])->value("login_time");
            // 更新为当前时间
            $todoListUser->where("name", $request['name'])->update(["login_time" => date('Y-m-d H:i:s')]);
            // 装填结果
            $response["上次登录时间"] = $query;
            $response["本次登录时间"] = date('Y-m-d H:i:s');
            // 返回结果（注意！如果要返回一个对象，必须要经过JSON编码后才能传输给前端！）
            return $response;
        }
        
        /**
         * 重置密码的方法
         */
        public function resetPassword(Request $request)
        {
            try {
                $todoListUser = new TodoListUser();
                // 如果不存在此用户
                if ($todoListUser->where("name", $request['name'])->find() === null) {
                    // exit()执行此语句后，直接跳出此函数，不再执行下面的代码
                    return "不存在此用户，请检查账号是否输入正确！";
                }
                // 查询结果，返回一个数组，如果里面有对象则存在此用户，为空数组则不存在
                $query = $todoListUser->where("name", $request['name'])->select();
                
                // 先查询用户原来的密码，看新密码和旧密码是否一致，一致则提示不必修改
                if ($query[0]->password === $request['password']) {
                    return "新密码和旧密码一致，不必修改！";
                } // 如果不一样
                else {
                    // 则查找用户并更新用户的密码
                    $todoListUser->where("name", $request['name'])->update(["password" => $request['password']]);
                    return "密码重置成功！";
                }
            } catch (PDOException $exception) {
                return "密码重置失败···";
            }
        }
        
        /**
         * 用户上传头像的方法
         */
        public function uploadAvatar(Request $request)
        {
            $todoListUser = new TodoListUser();
            $todoListUser->where("name", $request['userName'])->save(
                [
                    // 然后将Unicode编码转换为中文，最后将变量转换为对象，即JSON编码：
                    "avatar" => $request['base64'],
                ]
            );
            return "头像设置成功！";
        }
        
        /**
         * 从服务器获取时间戳
         */
        public function getDate()
        {
            return time();
        }
        
        /**
         * 提供给前端，用于测试网络的连通性
         */
        public function ping()
        {
            return "网络正常";
        }
        
        /**
         * 发送邮件的方法
         */
        public function postEmail(Request $request)
        {
            $emailMethods = new EmailMethods();
            // 如果已存在此用户
            if (TodoListUser::where("name", $request['email'])->find() !== null) {
                // exit()执行此语句后，直接跳出此函数，不再执行下面的代码
                return "已存在此用户，请更换邮箱地址！";
            }
            return $emailMethods->sendEmail([
                // QQ邮箱的服务器，这里是服务器用的
                'Host' => 'smtp.qq.com',
                // 端口
                'Port' => '465',
                // 邮箱的用户名
                'Username' => '2690085099@qq.com',
                // 邮箱授权码（需要申请）
                'Password' => 'redxvobeegridffj',
                // 发件人
                'setFrom' => ['2690085099@qq.com', '元昱'],
                // 收件人的邮箱，这是用户的邮箱，用户要注册
                'addAddress' => [$request['email'], '元昱'],
                // 回复的时候回复给哪个邮箱 建议和发件人一致
                'addReplyTo' => ['2690085099@qq.com', '元昱'],
                // 抄送（在发送邮件时，将内容同时发送给其他人联系人，其他人能够看到被CC的成员）
                'addCC' => [],
                // 密送（看不到被BCC的成员）
                'addBCC' => [],
                // 添加附件
                'addAttachment' => '',
                // 邮件标题
                'Subject' => '待办事项网站的客服',
                // 邮件内容
                'Body' => View::fetch(
                // todolist目录中的email.html文件（充当模板）
                    "todolist/email",
                    // 给模板里的变量赋值
                    ["userEmail" => $request['email'], "verificationCode" => $request['verificationCode']]
                ),
                // 如果邮件客户端不支持HTML则显示此内容
                'AltBody' => "待办事项客服：尊敬的{$request['email']}客户，您好！您的验证码为：{$request['verificationCode']}",
            ]);
        }
    }