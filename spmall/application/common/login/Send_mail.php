<?php
namespace app\common\login;

use think\Controller;
use PHPMailer\PHPMailer;

/**
*
* 	邮箱类
* 	@param time  2018/06/07 
* 
*/
class Send_mail extends Controller
{
	
	/**
 * 系统邮件发送函数
 * @param string $tomail 接收邮件者邮箱
 * @param string $name 接收邮件者名称
 * @param string $subject 邮件主题
 * @param string $body 邮件内容
 * @param string $attachment 附件列表
 * @return boolean
 * @author static7 <static7@qq.com>
 */

//发件人   收件人    标题    标题2    内容
public function send_out_mail($setmail,$getmail,$title, $subject = '', $body = '', $attachment = null)
{
	 $mail=new \PHPMailer\PHPMailer\PHPMailer();       
        try{
            //邮件调试模式
            $mail->SMTPDebug = 0;  
            //设置邮件使用SMTP
            $mail->isSMTP();
            // 设置邮件程序以使用SMTP
            $mail->Host = 'smtp.qq.com';
            // 设置邮件内容的编码
            $mail->CharSet='UTF-8';
            // 启用SMTP验证
            $mail->SMTPAuth = true;
            // SMTP username
            $mail->Username = '2697419714@qq.com';
            // SMTP password
            $mail->Password = 'ohdvoydtaltwdfif';
            // 启用TLS加密，`ssl`也被接受
//            $mail->SMTPSecure = 'tls';
            // 连接的TCP端口
//            $mail->Port = 587;
            //设置发件人
            $mail->setFrom($setmail, $title);
           //  添加收件人1
            $mail->addAddress($getmail);     // Add a recipient
//            $mail->addAddress('ellen@example.com');               // Name is optional
//            收件人回复的邮箱
            $mail->addReplyTo($setmail,  $title);
            // 将电子邮件格式设置为HTML
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
//            $mail->AltBody = '这是非HTML邮件客户端的纯文本';
            $mail->send();
            $data['state'] = 1;
            $data['message'] = "邮箱发送成功";
             $mail->isSMTP();
             return json_encode($data);
        }catch (Exception $e){
        	$data['state'] = 0;
            $data['message'] = $mail->ErrorInfo;
            return json_encode($data);
        }
}
}