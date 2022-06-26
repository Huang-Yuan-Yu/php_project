<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
    use think\facade\Route;
    
    // 首先使用pip安装httpie，httpie可以模拟发送各种请求
    
    // http://localhost:8000/think可以输出“hello,ThinkPHP6!”，在route目录下的app.php有定义
    Route::get('think', function () {
        return '你好，ThinkPHP6！';
    });
    
    // http://localhost:8000/hello/黄YY，:name是想跟着参数，是一个变量
    // index/hello是app/controller/Index.php下的函数
    Route::get('hello/:name', 'index/hello');
    
    // 使用http :8000/test命令，可以发出GET请求（http后面没加POST等参数，默认为GET方式），test是route/app.php里的函数
    Route::get("test", function () {
        return '我的GET请求';
    });
    
    // http POST :8000/testPost是POST请求
    Route::post("testPost", function () {
        return '我的POST请求';
    });
    
    // gotoHello是路径名，第二个参数，index是app/controller/Index.php下的，hello是函数名
    // http :8000/gotoHello
    Route::get("gotoHello", "index/hello");
    
    // 浏览器默认地址栏请求的是GET请求
    // 使用浏览器访问http://localhost:8000/student/黄YY/18
    Route::get('student/:name/:age', "index/studentGet");
    // http :8000/student/黄YY/21
    Route::post('studentPost/:name/:age', "index/studentPost");