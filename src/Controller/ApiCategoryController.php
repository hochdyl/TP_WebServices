<?php

namespace App\Controller;

use Exception;
use App\Entity\Category;
use App\Repository\MovieRepository;
use App\Repository\CategoryRepository;
use App\Service\EntityUpdaterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('api/')]
class ApiCategoryController extends ApiAbstractController
{
    /**
     * Api resource.
     *
     * @var string $ressource
     */
    private string $resource = Category::class;

    #[Route('category', name: 'api_get_categories', methods: ['GET'])]
    public function getCategories(Request $request, CategoryRepository $categoryRepository): Response
    {
        $page   = $request->query->get('page', 1);
        $size   = $request->query->get('size', 10);
        $search = $request->query->get('search');

        $categories = $categoryRepository->search($page, $size, $search);

        return $this->response($categories, 200);
    }

    #[Route('category', name: 'api_add_category', methods: ['POST'])]
    public function addCategory(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        try {
            $category = $this->deserializeRequest($request, $this->resource);
        } catch (Exception $e) {
            return $this->response(['message' => $e->getMessage()], 400);
        }

        // Validate entity.
        $errors = $validator->validate($category);

        if (count($errors)) {
            return $this->response($errors, 422);
        }

        $em->persist($category);
        $em->flush();

        return $this->response($category, 201);
    }

    #[Route('category/{category_id}', name: 'api_get_category', methods: ['GET'])]
    public function getCategory(int $category_id, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->find($category_id);

        if(!$category) {
            return $this->response(['message' => 'The resource you requested could not be found.'], 404);
        }

        return $this->response($category, 200);
    }

    #[Route('category/{category_id}', name: 'api_update_category', methods: ['PUT'])]
    public function updateCategory(int $category_id, Request $request, CategoryRepository $categoryRepository,
                                   EntityManagerInterface $em, EntityUpdaterService $updater,
                                   ValidatorInterface $validator): Response
    {
        $category = $categoryRepository->find($category_id);

        if (!$category) {
            return $this->response(['message' => 'The resource you requested could not be found.'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->response(['message' => 'Data is empty or wrongly formatted in json.'], 400);
        }

        // Update entity from request data.
        try {
            $category = $updater->update($category, $data);
        } catch (Exception $e) {
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

    #[Route('category/{category_id}', name: 'api_delete_category', methods: ['DELETE'])]
    public function deleteCategory(int $category_id, CategoryRepository $categoryRepository,
                                   EntityManagerInterface $em): Response
    {
        $category = $categoryRepository->find($category_id);

        if(!$category) {
            return $this->response(['message' => 'The resource you requested could not be found.'], 404);
        }

        $em->remove($category);
        $em->flush();

        return $this->response(null, 204);
    }

    #[Route('category/{category_id}/movies', name: 'api_get_category_movies', methods: ['GET'])]
    public function getCategoryMovies(int $category_id, Request $request, MovieRepository $movieRepository): Response
    {
        $page = $request->query->get('page', 1);
        $size = $request->query->get('size', 10);
        $search = $request->query->get('search');

        $movies = $movieRepository->searchByCategory($category_id, $page, $size, $search);

        return $this->response($movies, 200, ['withoutCategories']);
    }
}
