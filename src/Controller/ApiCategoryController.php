<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('api/')]
class ApiCategoryController extends ApiAbstractController
{
    #[Route('category', name: 'api_get_categories', methods: ['GET'])]
    public function getCategories(Request $request, CategoryRepository $categoryRepository): Response
    {
        $page   = $request->query->get('page', 1); // Page number, default : 1
        $size   = $request->query->get('size', 10); // Page size, default : 10
        $search = $request->query->get('search'); // Search terms, default : null

        $categories = $categoryRepository->search($page, $size, $search);

        return $this->response($categories, 200);
    }
}
