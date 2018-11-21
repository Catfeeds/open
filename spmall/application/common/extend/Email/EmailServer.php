<?php
namespace app\common\extend\Email;

use think\Controller;
use PHPMailer\PHPMailer;

/**
*
* 	邮箱类
* 	@param time  2018/06/07 
* 
*/
class EmailServer extends Controller
{
	
/**
 * 系统邮件发送函数
 * @param string $smtp 邮件发送服务器
 * @param string $setmail 邮件账号
 * @param string $pw 授权码
 * @param string $email_dk 端口号
 * @param string $getmail 收件人
 * @param string $body 邮件内容
 * @param string $subject 邮件主题
 * @param string $title 标题
 * @param string $subject 邮件主题
 * @param string $attachment 附件
 * @return boolean
 * @author static7 <static7@qq.com>
 */
//$EmailServer->send_out_mail($smtp,$setmail,$pw,$email_dk,$getmail,$body,$subject,$title,$subject)
public function send_out_mail($smtp,$setmail,$pw,$email_dk,$getmail,$body = '',$subject = '',$title = '',$subject = '', $attachment = null)
{
	 $mail=new \PHPMailer\PHPMailer\PHPMailer();       
        try{
            //邮件调试模式
            $mail->SMTPDebug = 0;  
            //设置邮件使用SMTP
            $mail->isSMTP();
            // 设置邮件程序以使用SMTP
            $mail->Host = $smtp;
            // 设置邮件内容的编码
            $mail->CharSet='UTF-8';
            // 启用SMTP验证
            $mail->SMTPAuth = true;
            // SMTP username
            $mail->Username = $setmail;
            // SMTP password
            $mail->Password = $pw;
            // ohdvoydtaltwdfif
            // 启用TLS加密，`ssl`也被接受
//            $mail->SMTPSecure = 'tls';
            // 连接的TCP端口
           $mail->Port = $email_dk;
            //设置发件人
            $mail->setFrom($setmail, $title);
           //  添加收件人1
            $mail->addAddress($getmail);     // Add a recipient
//            $mail->addAddress('ellen@example.com');               // Name is optional
//            收件人回复的邮箱
            $mail->addReplyTo($setmail, $title);
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