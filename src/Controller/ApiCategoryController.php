<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    #[Route('category', name: 'api_add_category', methods: ['POST'])]
    public function addCategory(Request $request, SerializerInterface $serializer, EntityManagerInterface $em,
                             ValidatorInterface $validator): Response
    {
        // Create entity from request data.
        try {
            $category = $serializer->deserialize($request->getContent(), Category::class, $this->inputFormat);
        } catch (NotEncodableValueException) {
            return $this->response(['message' => 'Data is wrongly formatted in ' . $this->inputFormat . '.'], 400);
        } catch (NotNormalizableValueException $e) {
            return $this->response(['message' => $e->getMessage()], 422);
        }

        // Validate entity.
        $errors = $validator->validate($category);

        if (count($errors)) {
            return $this->response($errors, 422);
        }

        $em->persist($category);
        $em->flush();

        return $this->response($category, 200);
    }
}
