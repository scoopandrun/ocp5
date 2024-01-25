<?php

namespace App\Controllers;

use App\Core\ErrorLogger;
use App\Repositories\PostRepository;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class HomepageController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function show(): void
    {
        $postRepository = new PostRepository();
        $latestPosts = $postRepository->getPostsSummaries(1, 1);
        $this->response->sendHTML(
            $this->twig->render(
                "front/homepage.html.twig",
                compact("latestPosts")
            )
        );
    }

    public function processContactForm(): void
    {
        $postRepository = new PostRepository();
        $latestPosts = $postRepository->getPostsSummaries(1, 1);

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
                "emailInvalid" => $email && !preg_match("/.*@.*\.[a-z]+/", $email),
                "message" => !$message,
            ]
        ];

        if (in_array(true, array_values($contactFormResult["errors"]))) {
            $this->response
                ->setCode(400)
                ->sendHTML(
                    $this->twig->render(
                        "front/homepage.html.twig",
                        compact("latestPosts", "contactFormResult")
                    )
                );
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
            (new ErrorLogger(new \Exception($mail->ErrorInfo)))->log();
        }

        $this->response->sendHTML(
            $this->twig->render(
                "front/homepage.html.twig",
                compact("latestPosts", "contactFormResult")
            )
        );
    }
}
