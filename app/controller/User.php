<?php
    // 命名空间，相当于包
    namespace app\controller;
    
    use app\BaseController;
    use app\model\Students;
    use think\db\exception\DataNotFoundException;
    use think\db\exception\DbException;
    use think\db\exception\ModelNotFoundException;
    use think\facade\Db;
    use think\facade\View;
    use think\response\Redirect;
    
    // 注意！导入的是think/facade/View而不是think\View！
    
    
    class User extends BaseController
    {
        
        public function index(): string
        {
            return '此文件为User.php';
        }
        
        public function hello($name = '黄YY'): string
        {
            return '你好！' . $name;
        }
        
        /**
         * 通过——http://localhost:8000/user/login来访问
         * @return string 返回字符串
         */
        public function login(): string
        {
            // 模板变量，赋值批量赋值
            View::assign([
                'name' => 'ThinkPHP',
                'email' => 'thinkphp@qq.com'
            ]);
            // 模板输出，这里面的参数就是view目录下文件的名称
            return View::fetch('Php_mysql');
            // return "这是登录方法";
        }
        
        public function mysql()
        {
            $query = null;
            try {
                $query = (new Db)->table("student")->select();
            } catch (DataNotFoundException | ModelNotFoundException | DbException $e) {
            }
            
            // 条件查询，where()里面是两个参数
            // $query = Db::table("student")->where("name", "赵六")->select();
            
            // 添加
            // $data = ["name" => "老六", "studentID" => "6583315"];
            // Db::name("student")->save($data);
            
            // 删除：
            // Db::table("student")->where("id", 13)->delete();
            
            // 更新：
            // Db::name("student")->where("id", 9)->update(["name" => "修改后的名字"]);
            
            // 使用Student类，可以简化代码，此类已经引入，在app\model目录下：
            try {
                (new Students)->select();
            } catch (DataNotFoundException | ModelNotFoundException | DbException $e) {
            }
            return $query;
        }
        
        public function Php_mysql(): string
        {
            $query = null;
            try {
                $query = (new Students)->select();
            } catch (DataNotFoundException | ModelNotFoundException | DbException $e) {
            }
            View::assign([
                "studentArray" => $query,
            ]);
            
            // 模板输出到View目录下的一个文件，View是类，“::"也像是Java中的"."
            return View::fetch('Php_mysql');
        }
        
        /**
         * 添加记录到学生表
         */
        public function addRecord()
        {
            // file_get_contents()返回的是字符串！而不是对象，所以使用json_decode解码，转换为“双列数组”，即Java的Map集合，有键值对
            // 注意！POST里存储的数据是Unicode编码，需要转码才能用
            $student = json_decode(file_get_contents("php://input"), true);
            // 添加数据到学生表中：
            Students::create(
                [
                    // 然后将Unicode编码转换为中文，最后将变量转换为对象，即JSON编码：
                    "name" => json_decode(sprintf('"%s"', $student['name'])),
                    "studentID" => json_decode(sprintf('"%s"', $student['studentId']))
                ]
            );
            
            // 添加记录到学生表，使用input()助手，获取post的内容，studentName和studentId都是HTML的input中的name属性的值
//            Students::create(["name" => $studentName, "studentID" => $studentId]);
            // 然后重定向回来
//            return redirect("http://localhost:8000/user/Php_mysql");
        }
        
        /**
         * 查询学生
         */
        public function studentLogin()
        {
            $student = json_decode(file_get_contents("php://input"), true);
            $query = (new Students)
                ->where("name", json_decode(sprintf('"%s"', $student['name'])))
                ->where("studentID", json_decode(sprintf('"%s"', $student['studentId'])))
                ->select();
            
            echo $query;
        }
        
        /**
         * 修改学生的学号
         *
         * “->”用于访问对象的属性和方法（是由类实例化而成的对象）
         * “::”用于访问类的静态方法和静态属性，因为是静态的，所以才能直接访问
         * “=>”符号，如果在数组中，时“连接键值对”的符号；如果用于函数，则表示箭头函数
         */
        public function modifyRecords(): Redirect
        {
            (new Students)->where("studentID", input("post.beforeStudentId"))
                ->update(["studentID" => input("post.afterStudentId")]);
            return redirect("http://localhost:8000/user/Php_mysql");
        }
        
        /**
         * 删除一条学生记录的方法
         */
        public function deleteStudent(): Redirect
        {
            try {
                (new Students)->find("name")->delete();
            } catch (DataNotFoundException | ModelNotFoundException | DbException $e) {
            }
            return redirect("http://localhost:8000/user/Php_mysql");
        }
        
        public function giveVue()
        {
//            header("Access-Control-Allow-Origin: *");
//            $arr = array('userid' => '20314115', 'name' => '黄YY', 'age' => 21);
            // 两种方式都行，但exit()能够输出一条信息后后终止脚本
//            echo json_encode($arr);
//            exit(json_encode($arr));
            $query = (new Students)->select();
            exit(json_encode($query));
        }
        
        
    }
