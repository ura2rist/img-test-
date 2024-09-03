<?php

namespace App\Controller;

use App\Form\SearchFormType;
use App\Service\ImagesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(
      Request $request,
      ImagesService $imagesService
    ): Response
    {
        $result = [];
        $form = $this->createForm(SearchFormType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $url = $imagesService->formattingUrl($form->get('url')->getData());
            $html = $imagesService->getHtml($url);
            $imgUrls = $imagesService->getImgUrls($html);
            $result = $imagesService->resultImagesForLinks($imgUrls, $url);
        }

        return $this->render('user/search.html.twig', [
          'form' => $form,
          'result' => $result
        ]);
    }
}
