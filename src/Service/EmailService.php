<?php

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use App\Core\ErrorLogger;

class EmailService
{
    private PHPMailer $mail;
    private string $fromAddress;
    private string $fromName = "";
    private array $to = [];
    private array $cc = [];
    private array $bcc = [];
    private string $subject = "";
    private string $htmlBody = "";
    private string $textBody = "";
    private bool $isHTML = true;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
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
            $this->mail->Subject = $this->subject;
            $this->mail->Body    = $this->htmlBody;
            if ($this->textBody) {
                $this->mail->AltBody = $this->textBody;
            }

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

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function setBody(string $html, string $plain = ""): static
    {
        $this->htmlBody = $html;

        if ($plain) {
            $this->textBody = $plain;
        }

        return $this;
    }

    public function setHTML(bool $isHTML)
    {
        $this->isHTML = $isHTML;

        return $this;
    }
}
