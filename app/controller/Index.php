<?php
    
    namespace app\controller;
    
    use app\BaseController;
    
    class Index extends BaseController
    {
        // 一个路径对应一个函数
        public function index()
        {
            return '<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px;} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:) </h1><p> ThinkPHP V' . \think\facade\App::version() . '<br/><span style="font-size:30px;">黄YY的PHP框架</span></p></div><script type="text/javascript" src="https://tajs.qq.com/stats?sId=64890268" charset="UTF-8"></script><script type="text/javascript" src="https://e.topthink.com/Public/static/client.js"></script><think id="ee9b1aa918103c4fc"></think>';
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
