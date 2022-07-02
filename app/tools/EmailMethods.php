<?php
    
    namespace app\tools;
    
    class EmailMethods
    {
        /**
         * 获取电子邮箱用的HTML模板（模板仿制“蒸汽平台（Steam国内代理平台）”的邮箱验证码，并会传入用户特定的信息
         * 因为将一大串HTML Email写在主类里会显得冗余，所以单独分出来一个类，作为方法调用
         * @param $userEmail :用户的邮箱
         * @param $verificationCode :验证码
         * @return string:邮箱HTML用的
         */
        public function getHtmlEmail($userEmail, $verificationCode)
        {
            return "<table border='0'>
        <tr>
            <td class='p-80 mpy-35 mpx-15' bgcolor='#212429' style='padding: 50px'>
                <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                    <tbody>
                        <tr>
                            <td
                                class='img pb-45'
                                style='font-size: 0; line-height: 0; text-align: left; padding-bottom: 40px'
                            >
                                <a
                                    href='https://www.hyy666.top/'
                                    target='_blank'
                                    rel='noopener'
                                    style='text-decoration: none'
                                >
                                    <h1 style='font-size: 40px; text-align: center; color: white'>待办事项</h1>
                                </a>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                    <tbody>
                                        <tr>
                                            <td
                                                class='title-36 pb-30 c-grey6 fw-b'
                                                style='
                                                    font-size: 36px;
                                                    line-height: 42px;
                                                    font-family: Arial, sans-serif, Motiva Sans;
                                                    text-align: left;
                                                    padding-bottom: 30px;
                                                    color: #bfbfbf;
                                                    font-weight: bold;
                                                '
                                            >
                                                <a
                                                    href='mailto:{$userEmail}'
                                                    rel='noopener'
                                                    target='_blank'
                                                    style='text-decoration: none; color: #bfbfbf'
                                                    >{$userEmail}</a
                                                >，您好！
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                    <tbody>
                                        <tr>
                                            <td
                                                class='text-18 c-grey4 pb-30'
                                                style='
                                                    font-size: 18px;
                                                    line-height: 25px;
                                                    font-family: Arial, sans-serif, Motiva Sans;
                                                    text-align: left;
                                                    color: #dbdbdb;
                                                    padding-bottom: 30px;
                                                '
                                            >
                                                您的帐户 <a
                                                    href='mailto:{$userEmail}'
                                                    rel='noopener'
                                                    target='_blank'
                                                    style='text-decoration: none; color: #bfbfbf'
                                                    >{$userEmail}</a
                                                > 所需的 待办事项网站验证码 为：
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                    <tbody>
                                        <tr>
                                            <td class='pb-70 mpb-50' style='padding-bottom: 70px'>
                                                <table
                                                    width='100%'
                                                    border='0'
                                                    cellspacing='0'
                                                    cellpadding='0'
                                                    bgcolor='#17191c'
                                                >
                                                    <tbody>
                                                        <tr>
                                                            <td class='py-30 px-56' style='padding: 30px 56px'>
                                                                <table
                                                                    width='100%'
                                                                    border='0'
                                                                    cellspacing='0'
                                                                    cellpadding='0'
                                                                >
                                                                    <tbody>
                                                                        <tr>
                                                                            <td
                                                                                class='title-48 c-blue1 fw-b a-center'
                                                                                style='
                                                                                    font-size: 48px;
                                                                                    line-height: 52px;
                                                                                    font-family: Arial, sans-serif,
                                                                                        Motiva Sans;
                                                                                    color: #3a9aed;
                                                                                    font-weight: bold;
                                                                                    text-align: center;
                                                                                '
                                                                            >
                                                                                {$verificationCode}
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                    <tbody>
                                        <tr>
                                            <td
                                                class='text-18 c-grey4 pb-30'
                                                style='
                                                    font-size: 18px;
                                                    line-height: 25px;
                                                    font-family: Arial, sans-serif, Motiva Sans;
                                                    text-align: left;
                                                    color: #dbdbdb;
                                                    padding-bottom: 30px;
                                                '
                                            >
                                                您会收到这封自动产生的邮件，是由于有人试图通过网页或移动设备操作您的帐户，且提供了正确的帐户名称与密码。<br /><br />
                                                待办事项网站的验证码是完成指定操作所必需的。<span
                                                    style='color: #ffffff; font-weight: bold'
                                                    >没有人能够不访问这封电子邮件就访问您的帐户。</span
                                                ><br /><br />
                                                <span style='color: #ffffff; font-weight: bold'>如果您未曾尝试操作</span
                                                >，那么请更改您的 待办事项网站
                                                密码，并考虑更改您的电子邮件密码，以确保您的帐户安全。
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                    <tbody>
                                        <tr>
                                            <td
                                                class='text-18 c-blue1 pb-40'
                                                style='
                                                    font-size: 18px;
                                                    line-height: 25px;
                                                    font-family: Arial, sans-serif, Motiva Sans;
                                                    text-align: left;
                                                    color: #7abefa;
                                                    padding-bottom: 40px;
                                                '
                                            ></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                    <tbody>
                                        <tr>
                                            <td class='pt-30' style='padding-top: 30px'>
                                                <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                                    <tbody>
                                                        <tr>
                                                            <td
                                                                class='img'
                                                                width='3'
                                                                bgcolor='#3a9aed'
                                                                style='font-size: 0; line-height: 0; text-align: left'
                                                            ></td>
                                                            <td
                                                                class='img'
                                                                width='37'
                                                                style='font-size: 0; line-height: 0; text-align: left'
                                                            ></td>
                                                            <td>
                                                                <table
                                                                    width='100%'
                                                                    border='0'
                                                                    cellspacing='0'
                                                                    cellpadding='0'
                                                                >
                                                                    <tbody>
                                                                        <tr>
                                                                            <td
                                                                                class='text-16 py-20 c-grey4 fallback-font'
                                                                                style='
                                                                                    font-size: 16px;
                                                                                    line-height: 22px;
                                                                                    font-family: Arial, sans-serif,
                                                                                        Motiva Sans;
                                                                                    text-align: left;
                                                                                    padding-top: 20px;
                                                                                    padding-bottom: 20px;
                                                                                    color: #f1f1f1;
                                                                                '
                                                                            >
                                                                                祝您愉快，<br />
                                                                                黄YY
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>";
        }
    }