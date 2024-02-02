<?php

namespace App\Controller;

use App\Core\HTTP\HTTPResponse;
use App\Service\ContactFormService;
use App\Service\PostService;

class HomepageController extends Controller
{
    public function show(): HTTPResponse
    {
        $postService = new PostService();
        $latestPosts = $postService->getPostsSummaries(1, 1);

        return $this->response->setHTML(
            $this->twig->render(
                "front/homepage.html.twig",
                compact("latestPosts")
            )
        );
    }

    public function processContactForm(): HTTPResponse
    {
        $postService = new PostService();
        $latestPosts = $postService->getPostsSummaries(1, 1);

        $contactFormData = $this->request->body["contactForm"];

        $contactFormService = new ContactFormService($contactFormData);

        $contactFormResult = $contactFormService->checkContactForm();

        if (in_array(true, array_values($contactFormResult["errors"]))) {
            return $this->response
                ->setCode(400)
                ->setHTML(
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

        return $this->response->setHTML(
            $this->twig->render(
                "front/homepage.html.twig",
                compact("latestPosts", "contactFormResult")
            )
        );
    }
}
