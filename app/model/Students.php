<?php
    // 注意这里是反斜杆
    namespace app\model;
    // Model大写
    use think\Model;

    class Students extends Model
    {
        // 设置当前模型对应的完整数据表名称
        protected $table = 'student';
    }