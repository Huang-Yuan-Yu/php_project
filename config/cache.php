<?php

// +----------------------------------------------------------------------
// | 缓存设置
// +----------------------------------------------------------------------

return [
    // 默认缓存驱动
    'default' => env('cache.driver', 'file'),

    // 缓存连接方式配置
    'stores'  => [
        'file' => [
            // 驱动方式
            'type'       => 'File',
            // 缓存保存目录
            'path'       => '',
            // 缓存前缀
            'prefix'     => '',
            // 缓存有效期 0表示永久缓存
            'expire'     => 0,
            // 缓存标签前缀
            'tag_prefix' => 'tag:',
            // 序列化机制 例如 ['serialize', 'unserialize']
            'serialize'  => [],
        ],
        // 更多的缓存连接（Redis在cache.php设置完，还得去session.php设置）
        // 配置Reids（通过“composer require marlon/thinkphp-redis”安装相应的依赖）
        'redis'    =>    [
            'type'     => 'redis',
            'host'     => '127.0.0.1',
            'port'     => '6379',
            'password' => '',
            'select'   => '0',
            // 全局缓存有效期（0为永久有效）
            'expire'   => 0,
            // 缓存前缀
            'prefix'   => '',
            'timeout'  => 0,
        ],
    ],
];

