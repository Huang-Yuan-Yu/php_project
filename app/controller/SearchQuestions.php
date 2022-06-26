<?php
    
    namespace app\controller;
    // 表的模型（Model）
    use app\model\SouthChinaUniversity;
    
    /**
     * 题库查询
     */
    class SearchQuestions
    {
        /**
         * 获取题目、、选项、答案等信息
         */
        public function getTopicInformation()
        {
            // 接收前端发来的信息
            $userInput = json_decode(file_get_contents("php://input"), true);
            // 提取用户输入的内容
            $searchContent = json_decode(sprintf('"%s"', $userInput['inputContent']));
            // 查询语句：数据库的like语句，用于模糊搜索，“%内容%”表示“内容”这个字符串可以出现在语句中的任何地方
            $query = SouthChinaUniversity::whereLike("question", "%{$searchContent}%")->select();
            // 返回查询的结果
            exit($query);
        }
    
        /**
         * 用于对表中的问题内容进行格式化
         */
        /*public function test()
        {
            $query = SouthChinaUniversity::field("question")->select();
            // 索引为0，无法索引出数据，待解决
            for ($index = 0; $index < 3547; $index++) {
                $content = $query[$index]->question;
                // 获取匹配的字符串  参数1，规则，2；获取的字符串 3，获得结果集
                preg_match('/[^.]*$/', $content, $resultArray);
                SouthChinaUniversity::where("question", $content)->update(["question" => $resultArray[0]]);
                echo($resultArray[0]);
            }
        }*/
    }