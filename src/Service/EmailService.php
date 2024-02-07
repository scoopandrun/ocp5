<?php

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use App\Core\ErrorLogger;
use App\Service\TwigService;
use Twig\Environment;
use Twig\Error\LoaderError;

class EmailService
{
    private PHPMailer $mail;
    private Environment $twig;

    private string $fromAddress = "";
    private string $fromName =  "";
    private array $to = [];
    private array $cc = [];
    private array $bcc = [];
    private string $subject = "";
    private string $template = "";
    private array $context = [];
    private bool $isHTML = true;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        $this->twig = (new TwigService())->getEnvironment();
    }

    /**
     * Send an email message.
     * 
     * @return bool `true` if the message was successfuly sent, `false` otherwise
     */
    public function send(): bool
    {
        try {
            //Server settings
            // $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $this->mail->isSMTP();                                         //Send using SMTP
            $this->mail->Host       = $_ENV["SMTP_HOST"];                  //Set the SMTP server to send through
            $this->mail->SMTPAuth   = true;                                //Enable SMTP authentication
            $this->mail->Username   = $_ENV["SMTP_USER"];                  //SMTP username
            $this->mail->Password   = $_ENV["SMTP_PASS"];                  //SMTP password
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;      //Enable implicit TLS encryption
            $this->mail->Port       = $_ENV["SMTP_PORT"];                  //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            // From
            if (!$this->fromAddress && !$this->fromName) {
                $this->setFrom($_ENV["MAIL_SENDER_EMAIL"], $_ENV["MAIL_SENDER_NAME"]);
            }
            $this->mail->setFrom($this->fromAddress, $this->fromName);

            //Recipients (To)
            foreach ($this->to as $recipient) {
                $this->mail->addAddress($recipient["address"], $recipient["name"]);     //Add a recipient
            }

            //Recipients (Cc)
            foreach ($this->to as $recipient) {
                $this->mail->addCC($recipient["address"], $recipient["name"]);     //Add a recipient
            }

            //Recipients (Bcc)
            foreach ($this->to as $recipient) {
                $this->mail->addBCC($recipient["address"], $recipient["name"]);     //Add a recipient
            }

            //Content
            $this->mail->isHTML($this->isHTML);                                  //Set email format to HTML
            $this->mail->CharSet = "UTF-8";
            $this->mail->Subject = $this->subject;
            $this->mail->Body    = $this->makeHTMLBody();
            $this->mail->AltBody = $this->makePlainBody();

            $this->mail->send();

            return true;
        } catch (\Throwable $th) {
            (new ErrorLogger($th))->log();

            if ($this->mail->ErrorInfo) {
                (new ErrorLogger(new \Exception($this->mail->ErrorInfo)))->log();
            }

            return false;
        }
    }

    public function setHTML(bool $isHTML): static
    {
        $this->isHTML = $isHTML;

        return $this;
    }

    public function setFrom(string $address, string $name = ""): static
    {
        $this->fromAddress = $address;

        if ($name) {
            $this->fromName = $name;
        }

        return $this;
    }

    public function addTo(string $address, string $name = ""): static
    {
        array_push($this->to, compact("address", "name"));

        return $this;
    }

    public function addCc(string $address, string $name = ""): static
    {
        array_push($this->cc, compact("address", "name"));

        return $this;
    }

    public function addBcc(string $address, string $name = ""): static
    {
        array_push($this->bcc, compact("address", "name"));

        return $this;
    }

    /**
     * Set the e-mail subject.
     */
    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Set the Twig template.
     * 
     * @param string $template Filename of the template, without the extension.  
     *                         eg: "reject-comment", without "html.twig" or "plain.txt.twig".  
     *                         The template files must be located in the "templates/email" directory.
     */
    public function setTemplate(string $template): static
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Set the context for the Twig template.
     * 
     * @param array $context Associative array to pass to the Twig template renderer.  
     *                       Those variables can be used in the template.
     */
    public function setContext(array $context): static
    {
        $this->context = $context;

        return $this;
    }

    private function makeHTMLBody(): string
    {
        return $this->twig->render(
            "email/{$this->template}.html.twig",
            array_merge(
                ["subject" => $this->subject],
                $this->context,
            )
        );
    }

    private function makePlainBody(): string
    {
        try {
            return $this->twig->render(
                "email/{$this->template}-plain.txt.twig",
                $this->context,
            );
        } catch (LoaderError $e) {
            return "";
        }
    }
}
