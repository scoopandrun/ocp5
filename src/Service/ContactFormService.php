<?php

namespace App\Service;

use App\Service\{EmailService, TwigService};

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
        $contactForm = $this->data;

        $twig = (new TwigService())->getEnvironment();

        $emailService = new EmailService();

        $subject = "Message de {$contactForm["name"]}";

        $emailBody = $emailService->createEmailBody(
            "contact-form",
            $subject,
            compact("contactForm")
        );

        $emailSent = $emailService
            ->setFrom($_ENV["MAIL_SENDER_EMAIL"], $_ENV["MAIL_SENDER_NAME"])
            ->addTo($_ENV["CONTACT_FORM_EMAIL"])
            ->setSubject($subject)
            ->setBody($emailBody)
            ->send();

        return $emailSent;
    }
}
