<?php
    
    namespace app\model;
    // Model大写
    use think\Model;
    
    /**
     * 待办事项
     */
    class TodoListData extends Model
    {
        // 设置当前模型对应的完整数据表名称
        protected $table = 'todo_list';
    }