<?php

namespace App\Controller;

use App\Service\ContactFormService;
use App\Service\PostService;

class HomepageController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function show(): void
    {
        $postService = new PostService();
        $latestPosts = $postService->getPostsSummaries(1, 1);
        $this->response->sendHTML(
            $this->twig->render(
                "front/homepage.html.twig",
                compact("latestPosts")
            )
        );
    }

    public function processContactForm(): void
    {
        $postService = new PostService();
        $latestPosts = $postService->getPostsSummaries(1, 1);

        $contactFormData = $this->request->body["contactForm"];

        $contactFormService = new ContactFormService($contactFormData);

        $contactFormResult = $contactFormService->checkContactForm();

        if (in_array(true, array_values($contactFormResult["errors"]))) {
            $this->response
                ->setCode(400)
                ->sendHTML(
                    $this->twig->render(
                        "front/homepage.html.twig",
                        compact("latestPosts", "contactFormResult")
                    )
                );
            return;
        }

        $emailSent = $contactFormService->sendEmail();

        if ($emailSent) {
            $contactFormResult["success"] = true;
            $contactFormResult["values"]["message"] = "";
        } else {
            $contactFormResult["failure"] = true;
        }

        $this->response->sendHTML(
            $this->twig->render(
                "front/homepage.html.twig",
                compact("latestPosts", "contactFormResult")
            )
        );
    }
}
