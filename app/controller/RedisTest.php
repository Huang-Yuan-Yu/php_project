<?php
    
    namespace app\controller;
    
    use think\facade\Cache;
    
    /**
     * Redis学习测试
     */
    class RedisTest
    {
        public function index()
        {
            // 输出关于PHP的各种信息
            // phpinfo();
            
            // Cache类对应到config目录下的cache.php文件，在文件中有关于redis的配置，这里目的是创建Redis对象
            $redis = Cache::store('redis');
            // 设置缓存
            $redis->set('我的名字', '黄元昱');
            // 获取缓存
            $value = $redis->get('我的名字');
            echo $value;
        }
    }