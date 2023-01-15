<?php
    
    namespace app\controller;
    
    use app\BaseController;
    use think\facade\App;
    use think\facade\View;
    
    class Index extends BaseController
    {
        // 一个路径对应一个函数
        public function index()
        {
            // 给模板中的变量赋值，值为ThinkPHP的版本号，模板路径为view/index/index.html
            View::assign("version", App::version());
            // 模板输出：
            return View::fetch("index/index");
        }
        
        // 是通过route目录下的app.php访问此方法的，“http://localhost:8000/hello/黄YY”
        public function hello($name = 'ThinkPHP6')
        {
            return '（Index.php的hello函数）你好，' . $name;
        }
        
        // 参数有默认值，叫命名参数
        public function studentGet($name = '黄YY', $age = 21)
        {
            return 'studentGet：我叫' . $name . "，今年" . $age . "岁了！";
        }
        
        public function qq()
        {
            // 这样写确实能够访问到，在public目录下
            return "<a href='/static/qq/index.php'>进入QQ配置</a>";
            //            return "<a href='/static/qq/example/oauth/index.php'>进入配置教程</a>";
        }
    }
