<?php
    
    namespace app\controller;
    
    use app\model\Students;
    use DomainException;
    use Firebase\JWT\BeforeValidException;
    use Firebase\JWT\ExpiredException;
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
    use Firebase\JWT\SignatureInvalidException;
    use think\Exception;
    
    // 系统使用北京时间
    date_default_timezone_set("PRC");
    // JSON Web Token的私钥（define()可以定义常量）
    define('KEY', '1gHuiop975cdashyex9Ud23ldsvm2Xq');
    
    class JwtTest
    {
        /**
         * 学生登录的方法
         */
        public function login()
        {
            // 初始结果是登录失败：
            $response['result'] = 'failed';
            try {
                $student = json_decode(file_get_contents("php://input"), true);
                $username = json_decode(sprintf('"%s"', $student['name']));
                $password = json_decode(sprintf('"%s"', $student['studentId']));
                // 查询数据库的表
                $query = (new Students)
                    // 两个查询条件
                    ->where("name", $username)
                    ->where("studentID", $password)
                    ->select();
                
                // 用户名和密码正确，则签发Token
                if ($username == $query[0]->name && $password == $query[0]->studentID) {
                    // 获取当前时间，作为签发时间
                    $nowTime = time();
                    // 注意！Token里不能存放重要、敏感的内容，因为可以通过Token解析出下面的实际内容
                    $token = [
                        // 签发者（服务器）的地址
                        'iss' => 'http://localhost:8000/',
                        // 客户端地址
                        'aud' => 'http://localhost:8080',
                        // 签发时间
                        'iat' => $nowTime,
                        // 在什么时间之后该jwt才可用
                        'nbf' => $nowTime,
                        // 过期时间，这里以“秒”作为单位，“+600”表示过10分钟后过期——60*10=600
                        'exp' => $nowTime + 5,
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
        
        public function test()
        {
            $this->verification();
            echo "请求成功！";
        }
    }