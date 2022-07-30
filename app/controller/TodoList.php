<?php
    
    namespace app\controller;
    
    // 引入Model目录下的TodoListUser类——待办事项
    use app\model\TodoListData;
    use app\model\TodoListUser;
    use DomainException;
    use Firebase\JWT\BeforeValidException;
    use Firebase\JWT\ExpiredException;
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
    use Firebase\JWT\SignatureInvalidException;
    use think\db\exception\PDOException;
    use think\Exception;
    
    // 系统使用北京时间
    date_default_timezone_set("PRC");
    // JSON Web Token的私钥（define()可以定义常量）
    define('KEY', '1gHuiop975cdashyex9Ud23ldsvm2Xq');
    // 包含X-Content-Type-Options，可以防止黑客在浏览器进行MIME类型的嗅探
    header("X-Content-Type-Options:nosniff");
    
    class TodoList
    {
        
        /**
         * 注册
         */
        public function register()
        {
            try {
                // 返回的值：
                $response = [];
                // file_get_contents()返回的是字符串！而不是对象，所以使用json_decode解码，转换为“双列数组”，即Java的Map集合，有键值对
                // 注意！POST里存储的数据是Unicode编码，需要转码才能用
                $user = json_decode(file_get_contents("php://input"), true);
                // 添加数据到用户表中：
                TodoListUser::create(
                    [
                        // 然后将Unicode编码转换为中文，最后将变量转换为对象，即JSON编码：
                        "name" => json_decode(sprintf('"%s"', $user['name'])),
                        "password" => json_decode(sprintf('"%s"', $user['password'])),
                    ]
                );
                exit($response["message"] = "注册成功！");
            } catch (PDOException $exception) {
                exit($response["message"] = "用户名重复，请更换用户名！");
            }
        }
        
        /**
         * 游客登录要用到的匿名注册和顺便登录的接口
         */
        public function loginAnonymously()
        {
            try {
                // 返回的值：
                $response = [];
                $user = json_decode(file_get_contents("php://input"), true);
                $name = json_decode(sprintf('"%s"', $user['name']));
                
                // 判断是否存在此用户，如果不存在，则开始注册，否则就不用注册
                $todoListUser = new TodoListUser();
                if ($todoListUser->where("name", $name)->find() === null) {
                    // 添加数据到用户表中：
                    TodoListUser::create(
                        [
                            "name" => $name,
                        ]
                    );
                }
                // 查询游客的信息（一定要在判断用户之后查询，如果查询不到信息会报错
                $query = $todoListUser->where("name", $name)->select();
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
                $response['jwt'] = JWT::encode($token, KEY, "HS256");
                // 用户头像数据
                $response['avatar'] = $query[0]->avatar;
                // 登录成功就赋值为success
                $response['result'] = '登录成功';
            } catch (PDOException $exception) {
                // 一般是浏览器指纹（name）重复了
                exit($response["result"] = "抱歉，您的设备不支持游客登录···");
            }
            
            // 最后返回一或多个属性
            exit(json_encode($response));
        }
        
        /**
         * QQ授权后，将信息存储到后端
         */
        public function qqLogin()
        {
            $user = json_decode(file_get_contents("php://input"), true);
            $name = json_decode(sprintf('"%s"', $user['name']));
            
            // 判断是否存在此用户，如果不存在，则开始注册，否则就不用注册
            $todoListUser = new TodoListUser();
            if ($todoListUser->where("name", $name)->find() === null) {
                // 添加数据到用户表中：
                TodoListUser::create(
                    [
                        "name" => $name,
                        // 头像
                        "avatar" => json_decode(sprintf('"%s"', $user['avatar'])),
                    ]
                );
            }
        }
        
        /**
         * 用户登录的方法
         */
        public function login()
        {
            $user = json_decode(file_get_contents("php://input"), true);
            $name = json_decode(sprintf('"%s"', $user['name']));
            $password = json_decode(sprintf('"%s"', $user['password']));
            
            // 这里只是为了占位，因为返回给前端的参数可能有多个：
            $response['result'] = "";
            // 判断是否存在此用户，如果不存在，则直接返回信息给前端
            $todoListUser = new TodoListUser();
            if ($todoListUser->where("name", $name)->find() === null) {
                // exit()执行此语句后，直接跳出此函数，不再执行下面的代码
                $response['result'] = "不存在此用户，请检查账号是否输入正确！";
            } else {
                try {
                    // 查询数据库的表（两个查询条件），查找有无存在此用户，若查询不到，则会报错，要异常捕获
                    $query = $todoListUser->where("name", $name)->where("password", $password)->select();
                    // 用户名和密码正确，则签发Token
                    if ($name == $query[0]->name && $password == $query[0]->password) {
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
                        $response['jwt'] = JWT::encode($token, KEY, "HS256");
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
            exit(json_encode($response));
        }
        
        /**
         * 验证Token
         */
        public function verification()
        {
            $todoListUser = new TodoListUser();
            $user = json_decode(file_get_contents("php://input"), true);
            $name = json_decode(sprintf('"%s"', $user['name']));
            $avatar = $todoListUser->where("name", $name)->value("avatar");
            
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
                exit(json_encode($response));
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
        public function getObjectArray()
        {
            $user = json_decode(file_get_contents("php://input"), true);
            // 在数据表中查找用户的数据：
            $todoListData = new TodoListData();
            $query = $todoListData->
            // 查询某个用户的所有待办事项记录
            where("name", json_decode(sprintf('"%s"', $user['name'])))
                ->select();
            // 返回查询的结果
            exit(json_encode($query));
        }
        
        /**
         * 添加事项
         */
        public function addData()
        {
            // file_get_contents()返回的是字符串！而不是对象，所以使用json_decode解码，转换为“双列数组”，即Java的Map集合，有键值对
            // 注意！POST里存储的数据是Unicode编码，需要转码才能用
            $data = json_decode(file_get_contents("php://input"), true);
            // 添加数据到用户表中：
            TodoListData::create(
                [
                    // 然后将Unicode编码转换为中文，最后将变量转换为对象，即JSON编码：
                    "name" => json_decode(sprintf('"%s"', $data['name'])),
                    "mission" => json_decode(sprintf('"%s"', $data['mission'])),
                    "done" => json_decode(sprintf('"%s"', $data['done'])),
                ]
            );
            
            $todoListData = new TodoListData();
            $query = $todoListData->where("name", json_decode(sprintf('"%s"', $data['name'])))
                ->value("time");
            exit(json_encode($query));
        }
        
        /**
         * 删除事项
         */
        public function deleteData()
        {
            $todoListData = new TodoListData();
            // file_get_contents()返回的是字符串！而不是对象，所以使用json_decode解码，转换为“双列数组”，即Java的Map集合，有键值对
            // 注意！POST里存储的数据是Unicode编码，需要转码才能用
            $data = json_decode(file_get_contents("php://input"), true);
            // 删除指定用户的指定记录，使用两个条件，从而不会删错用户的信息
            $todoListData->where("name", json_decode(sprintf('"%s"', $data['name'])))
                ->where("mission", json_decode(sprintf('"%s"', $data['mission'])))
                ->delete();
        }
        
        /**
         * 清除已完成的事项
         */
        public function clearCompletedTodo()
        {
            $data = json_decode(file_get_contents("php://input"), true);
            // “销毁destroy”方法，能够一次性删除多条数据，以主键作为依据，这里可以传入一个数组，里面包含表中主键的值
            TodoListData::destroy($data);
        }
        
        /**
         * 修改事项内容的方法
         */
        public function modificationData()
        {
            $todoListData = new TodoListData();
            $data = json_decode(file_get_contents("php://input"), true);
            // 如果$user['beforeContent']已经存在，证明调用者发的是修改事项内容的请求（isset()检测变量是否已设置且不为null
            if (isset($data['beforeContent'])) {
                $todoListData->where("name", json_decode(sprintf('"%s"', $data['name'])))
                    ->where("mission", json_decode(sprintf('"%s"', $data['beforeContent'])))
                    ->update(["mission" => json_decode(sprintf('"%s"', $data['mission']))]);
            } // 如果是修改事项的完成情况：
            else if (isset($data['nowDone'])) {
                // 以用户名和记录内容作为条件查询，用户名和记录都不会出现重复的情况，因为在前端和后端都已做判断
                $todoListData->where("name", json_decode(sprintf('"%s"', $data['name'])))
                    ->where("mission", json_decode(sprintf('"%s"', $data['mission'])))
                    ->update(["done" => json_decode(sprintf('"%s"', $data['nowDone']))]);
            }
        }
        
        /**
         * 完成所有发的待办事项
         */
        public function finishAllTodo()
        {
            $todoListData = new TodoListData();
            $data = json_decode(file_get_contents("php://input"), true);
            $todoListData->where("name", json_decode(sprintf('"%s"', $data['name'])))
                // 查找该用户下的所有未完成的事项，将其更新为完成
                ->where("done", 0)->update(["done" => 1]);
        }
        
        /**
         * 取消完成所有待办事项
         */
        public function noFinishAllTodo()
        {
            $todoListData = new TodoListData();
            $data = json_decode(file_get_contents("php://input"), true);
            $todoListData->where("name", json_decode(sprintf('"%s"', $data['name'])))
                // 查找该用户下的所有完成的事项，将其更新为未完成
                ->where("done", 1)->update(["done" => 0]);
        }
        
        /**
         * 返回用户上次登录的时间，以及这次登录时间
         */
        public function updateLoginTime()
        {
            $todoListUser = new TodoListUser();
            $response = [];
            $user = json_decode(file_get_contents("php://input"), true);
            $userName = json_decode(sprintf('"%s"', $user['name']));
            // 查询用户上次登录的时间
            $query = $todoListUser->where("name", $userName)->value("login_time");
            // 更新为当前时间
            $todoListUser->where("name", $userName)->update(["login_time" => date('Y-m-d H:i:s')]);
            // 装填结果
            $response["上次登录时间"] = $query;
            $response["本次登录时间"] = date('Y-m-d H:i:s');
            // 返回结果（注意！如果要返回一个对象，必须要经过JSON编码后才能传输给前端！）
            exit(json_encode($response));
        }
        
        /**
         * 重置密码的方法
         */
        public function resetPassword()
        {
            try {
                $todoListUser = new TodoListUser();
                // 返回的值：
                $response = [];
                // file_get_contents()返回的是字符串！而不是对象，所以使用json_decode解码，转换为“双列数组”，即Java的Map集合，有键值对
                // 注意！POST里存储的数据是Unicode编码，需要转码才能用
                $user = json_decode(file_get_contents("php://input"), true);
                $userName = json_decode(sprintf('"%s"', $user['name']));
                $userPassword = json_decode(sprintf('"%s"', $user['password']));
                
                // 如果不存在此用户
                if ($todoListUser->where("name", $userName)->find() === null) {
                    // exit()执行此语句后，直接跳出此函数，不再执行下面的代码
                    exit($response['message'] = "不存在此用户，请检查账号是否输入正确！");
                }
                // 查询结果，返回一个数组，如果里面有对象则存在此用户，为空数组则不存在
                $query = $todoListUser->where("name", $userName)->select();
                
                // 先查询用户原来的密码，看新密码和旧密码是否一致，一致则提示不必修改
                if ($query[0]->password === $userPassword) {
                    exit($response["message"] = "新密码和旧密码一致，不必修改！");
                } // 如果不一样
                else {
                    // 则查找用户并更新用户的密码
                    $todoListUser->where("name", $userName)->update(["password" => $userPassword]);
                    exit($response["message"] = "密码重置成功！");
                }
            } catch (PDOException $exception) {
                exit($response["message"] = "密码重置失败···");
            }
        }
        
        /**
         * 用户上传头像的方法
         */
        public function uploadAvatar()
        {
            $todoListUser = new TodoListUser();
            $message = json_decode(file_get_contents("php://input"), true);
            $userName = json_decode(sprintf('"%s"', $message['userName']));
            $todoListUser->where("name", $userName)->save(
                [
                    // 然后将Unicode编码转换为中文，最后将变量转换为对象，即JSON编码：
                    "avatar" => json_decode(sprintf('"%s"', $message['base64'])),
                ]
            );
            exit("头像设置成功！");
        }
        
        /**
         * 从服务器获取时间戳
         */
        public function getDate()
        {
            echo(time());
        }
        
        /**
         * 提供给前端，用于测试网络的连通性
         */
        public function ping()
        {
            exit("网络正常");
        }
    }