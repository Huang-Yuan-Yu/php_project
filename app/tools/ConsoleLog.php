<?php
    
    namespace app\tools;
    
    class ConsoleLog
    {
        /**
         * @param $data :传入需要打印到控制台的变量
         */
        public function console_log($data)
        {
            if (is_array($data) || is_object($data)) {
                echo("<script>console.log('" . json_encode($data) . "');</script>");
            } else {
                echo("<script>console.log('" . $data . "');</script>");
            }
        }
    }