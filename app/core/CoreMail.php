<?php

/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 11/12/15
 * Time: 06:18 PM
 */
class CoreMail
{
    public static function sentMail ($address, $subject, $body, $altBody) {

        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = "mail.lh-si.com";
        $mail->SMTPAuth = true;
        $mail->Username = 'lerny@lh-si.com';
        $mail->Password = 'FI6qiSICn';
        $mail->SMTPSecure = "tls";
        $mail->Port = 587;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mail->setFrom('lerny@lh-si.com', 'Loans');
        foreach($address as $value) {
            $mail->addAddress($value["mail"], $value["user"]);
        }
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $altBody;
        $error = array(
            "msg" => _("El mensaje ha sido enviado."),
            "error" => false
        );
        if(!$mail->send()) {
            $error["msg"] = _("El mensaje no puede ser enviado.Mailer Error: " . $mail->ErrorInfo);
            $error["error"] = true;
        }
        return $error;
    }
}