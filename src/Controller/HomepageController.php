<?php

namespace App\Controller;

use App\Service\ContactFormService;
use App\Repository\PostRepository;

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
