<?php

namespace App\Entity;

use App\Service\TwigService;
use Twig\Environment;
use Twig\Error\LoaderError;

class EmailBody
{
    private Environment $twig;

    public function __construct(
        private string $template,
        private string $subject,
        private array $context,
    ) {
        $this->twig = (new TwigService())->getEnvironment();
    }

    public function getHTML(): string
    {
        return $this->twig->render(
            "email/{$this->template}.html.twig",
            array_merge(
                ["subject" => $this->subject],
                $this->context,
            )
        );
    }

    public function getPlain(): string
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
};
