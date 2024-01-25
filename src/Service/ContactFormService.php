<?php

namespace App\Service;

use App\Service\EmailService;

class ContactFormService
{
    /**
     * @param array $data Contact form data.
     */
    public function __construct(private array $data)
    {
    }

    /**
     * Check the validity of the contact form data  
     * and return an array with the results.
     * 
     * @return array 
     */
    public function checkContactForm(): array
    {
        // Check that all fields are filled in
        $name = $this->data["name"] ?? null;
        $email = $this->data["email"] ?? null;
        $message = $this->data["message"] ?? null;

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

        return $contactFormResult;
    }

    /**
     * Send the email when a contact form is submitted.
     * 
     * @return bool
     */
    public function sendEmail(): bool
    {
        $emailService = new EmailService();

        $senderName = $this->data["name"];
        $senderEmail = $this->data["email"];
        $body = $this->constructEmailBody();

        $emailService
            ->setFrom($_ENV["MAIL_SENDER_EMAIL"], $_ENV["MAIL_SENDER_NAME"])
            ->addTo($_ENV["CONTACT_FORM_EMAIL"])
            ->setSubject("[OCP5] Message de $senderName ($senderEmail)")
            ->setBody($body["html"], $body["text"]);

        return $emailService->send();
    }

    /**
     * Make the email body for the contact form.
     * 
     * @return array Array with HTML and text email body.
     */
    private function constructEmailBody(): array
    {
        $name = $this->data["name"];
        $email = $this->data["email"];
        $message = $this->data["message"];

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

        $emailBody = [
            "html" => $emailBody,
            "text" => $emailAltBody,
        ];

        return $emailBody;
    }
}
