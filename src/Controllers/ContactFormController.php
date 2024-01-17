<?php

namespace App\Controllers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class ContactFormController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function process()
    {
        // Check that all fields are filled in
        $name = $this->request->body["name"] ?? null;
        $email = $this->request->body["email"] ?? null;
        $message = $this->request->body["message"] ?? null;

        $contactFormResult = [
            "success" => false,
            "failure" => false,
            "values" => compact("name", "email", "message"),
            "errors" => [
                "name" => !$name,
                "emailMissing" => !$email,
                "emailInvalid" => !preg_match("/.*@.*\.[a-z]+/", $email),
                "message" => !$message,
            ]
        ];

        if (in_array(true, array_values($contactFormResult["errors"]))) {
            $this->twig->display("homepage.html.twig", compact("contactFormResult"));
            exit;
        }

        $mail = new PHPMailer(true);

        $htmlMessage = str_replace(["\n", "\r\n"], "<br/>", $message);

        $emailBody = <<<HTML
            <div>De : <b>$name &lt;$email&gt;</b></div>
            <hr />
            <div>Message :</div>
            <div>$htmlMessage</div>
            HTML;

        $emailAltBody = <<<TEXT
            De : name ($email)
            Message :
            $message
            TEXT;

        try {
            //Server settings
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = $_ENV["SMTP_HOST"];                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $_ENV["SMTP_USER"];                     //SMTP username
            $mail->Password   = $_ENV["SMTP_PASS"];                     //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
            $mail->Port       = $_ENV["SMTP_PORT"];                     //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom($_ENV["MAIL_SENDER_EMAIL"], $_ENV["MAIL_SENDER_NAME"]);
            $mail->addAddress($_ENV["CONTACT_FORM_EMAIL"]);     //Add a recipient

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = "[OCP5] Message de $name ($email)";
            $mail->Body    = $emailBody;
            $mail->AltBody = $emailAltBody;

            $mail->send();

            $contactFormResult["success"] = true;
            $contactFormResult["values"]["message"] = "";
        } catch (\Throwable $th) {
            $contactFormResult["failure"] = true;
            error_logger(new \Exception($mail->ErrorInfo));
        }

        $this->twig->display("homepage.html.twig", compact("contactFormResult"));
    }
}