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
                // 添加数据到学生表中：
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
         * 用户登录的方法
         */
        public function login()
        {
            // 初始结果是登录失败：
            $response['result'] = 'failed';
            try {
                $user = json_decode(file_get_contents("php://input"), true);
                $name = json_decode(sprintf('"%s"', $user['name']));
                $password = json_decode(sprintf('"%s"', $user['password']));
                // 查询数据库的表
                $query = (new TodoListUser)
                    // 两个查询条件
                    ->where("name", $name)
                    ->where("password", $password)
                    ->select();
                
                // 用户名和密码正确，则签发Token
                if ($name == $query[0]->name && $password == $query[0]->password) {
                    // 获取当前时间，作为签发时间
                    $nowTime = time();
                    // 注意！Token里不能存放重要、敏感的内容，因为可以通过Token解析出下面的实际内容
                    $token = [
                        // 签发者（服务器）的地址
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
                    $jwt = JWT::encode($token, KEY, "HS256");
                    // 登录成功就赋值为success
                    $response['result'] = '登录成功';
                    // 将编码后的Token发送给客户端
                    $response['jwt'] = $jwt;
                }
            } catch (Exception $exception) {
                $response['msg'] = '用户名或密码错误!';
            }
            echo json_encode($response);
        }
        
        /**
         * 验证Token
         */
        public function verification()
        {
            try {
                // 注意！！！这里能获取客户端的HTTP请求头的某个字段，比如“TOKEN”，注意要大写（否则会报500错,即使客户端传的token名称是小写）
                // 前缀固定为”HTTP-“，后面为具体的字段名。HS256方式，这里要和签发的时候对应
                $decoded = JWT::decode($_SERVER['HTTP_TOKEN'], new Key(KEY, "HS256"));
                $arr = $decoded;
                if (time() > $arr->exp) {
                    $response["message"] = "登录已过期，请重新登录！";
                } else {
                    $response["message"] = "处于登录状态";
                }
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
            $query = TodoListData::
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
            // 添加数据到学生表中：
            TodoListData::create(
                [
                    // 然后将Unicode编码转换为中文，最后将变量转换为对象，即JSON编码：
                    "name" => json_decode(sprintf('"%s"', $data['name'])),
                    "mission" => json_decode(sprintf('"%s"', $data['mission'])),
                    "done" => json_decode(sprintf('"%s"', $data['done'])),
                ]
            );
            
            $query = TodoListData::where("name", json_decode(sprintf('"%s"', $data['name'])))
                ->value("time");
            exit(json_encode($query));
        }
        
        /**
         * 删除事项
         */
        public function deleteData()
        {
            // file_get_contents()返回的是字符串！而不是对象，所以使用json_decode解码，转换为“双列数组”，即Java的Map集合，有键值对
            // 注意！POST里存储的数据是Unicode编码，需要转码才能用
            $data = json_decode(file_get_contents("php://input"), true);
            // 删除指定用户的指定记录，使用两个条件，从而不会删错用户的信息
            TodoListData::where("name", json_decode(sprintf('"%s"', $data['name'])))
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
            $data = json_decode(file_get_contents("php://input"), true);
            // 如果$user['beforeContent']已经存在，证明调用者发的是修改事项内容的请求（isset()检测变量是否已设置且不为null
            if (isset($data['beforeContent'])) {
                TodoListData::where("name", json_decode(sprintf('"%s"', $data['name'])))
                    ->where("mission", json_decode(sprintf('"%s"', $data['beforeContent'])))
                    ->update(["mission" => json_decode(sprintf('"%s"', $data['mission']))]);
            } // 如果是修改事项的完成情况：
            else if (isset($data['nowDone'])) {
                // 以用户名和记录内容作为条件查询，用户名和记录都不会出现重复的情况，因为在前端和后端都已做判断
                TodoListData::where("name", json_decode(sprintf('"%s"', $data['name'])))
                    ->where("mission", json_decode(sprintf('"%s"', $data['mission'])))
                    ->update(["done" => json_decode(sprintf('"%s"', $data['nowDone']))]);
            }
        }
        
        /**
         * 完成所有的待办事项
         */
        public function finishAllTodo()
        {
            $data = json_decode(file_get_contents("php://input"), true);
            TodoListData::where("name", json_decode(sprintf('"%s"', $data['name'])))
                // 查找该用户下的所有未完成的事项，将其更新为完成
                ->where("done", 0)->update(["done" => 1]);
        }
    
        /**
         * 取消完成所有待办事项
         */
        public function noFinishAllTodo()
        {
            $data = json_decode(file_get_contents("php://input"), true);
            TodoListData::where("name", json_decode(sprintf('"%s"', $data['name'])))
                // 查找该用户下的所有完成的事项，将其更新为未完成
                ->where("done", 1)->update(["done" => 0]);
        }
        
        /**
         * 返回用户上次登录的时间，以及这次登录时间
         */
        public function updateLoginTime()
        {
            $response = [];
            $user = json_decode(file_get_contents("php://input"), true);
            $userName = json_decode(sprintf('"%s"', $user['name']));
            // 查询用户上次登录的时间
            $query = TodoListUser::where("name", $userName)->value("login_time");
            // 更新为当前时间
            TodoListUser::where("name", $userName)->update(["login_time" => date('Y-m-d H:i:s')]);
            // 装填结果
            $response["上次登录时间"] = $query;
            $response["本次登录时间"] = date('Y-m-d H:i:s');
            // 返回结果（注意！如果要返回一个对象，必须要经过JSON编码后才能传输给前端！）
            exit(json_encode($response));
        }
    
        /**
         * 提供给前端，用于测试网络的连通性
         */
        public function ping()
        {
            exit("网络正常");
        }
    }