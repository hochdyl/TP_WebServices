<?php

namespace App\Controller;

use Exception;
use App\Entity\Movie;
use App\Repository\MovieRepository;
use App\Service\EntityUpdaterService;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('api/')]
class ApiMovieController extends ApiAbstractController
{
    /**
     * Api resource.
     *
     * @var string $ressource
     */
    private string $resource = Movie::class;

    #[Route('movie', name: 'api_get_movies', methods: ['GET'])]
    public function getMovies(Request $request, MovieRepository $movieRepository): Response
    {
        $page       = $request->query->get('page', 1);
        $size       = $request->query->get('size', 10);
        $search     = $request->query->get('search');

        $movies = $movieRepository->search($page, $size, $search);

        return $this->response($movies, 200);
    }

    #[Route('movie', name: 'api_add_movie', methods: ['POST'])]
    public function addMovie(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        try {
            $movie = $this->deserializeRequest($request, $this->resource);
        } catch (Exception $e) {
            return $this->response(['message' => $e->getMessage()], 400);
        }

        // Validate entity.
        $errors = $validator->validate($movie);

        if (count($errors)) {
            return $this->response($errors, 422);
        }

        $em->persist($movie);
        $em->flush();

        return $this->response($movie, 201);
    }

    #[Route('movie/{movie_id}', name: 'api_get_movie', methods: ['GET'])]
    public function getMovie(int $movie_id, MovieRepository $movieRepository): Response
    {
        $movie = $movieRepository->find($movie_id);

        if(!$movie) {
            return $this->response(['message' => 'The resource you requested could not be found.'], 404);
        }

        return $this->response($movie, 200);
    }

    #[Route('movie/{movie_id}', name: 'api_update_movie', methods: ['PUT'])]
    public function updateMovie(int $movie_id, Request $request, MovieRepository $movieRepository,
                                EntityManagerInterface $em, EntityUpdaterService $updater,
                                ValidatorInterface $validator): Response
    {
        $movie = $movieRepository->find($movie_id);

        if (!$movie) {
            return $this->response(['message' => 'The resource you requested could not be found.'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->response(['message' => 'Data is empty or wrongly formatted in json.'], 400);
        }

        // Update entity from request data.
        try {
            $movie = $updater->update($movie, $data);
        } catch (Exception $e) {
            return $this->response(['message' => $e->getMessage()], 422);
        }

        // Validate entity.
        $errors = $validator->validate($movie);

        if (count($errors)) {
            return $this->response($errors, 422);
        }

        $em->persist($movie);
        $em->flush();

        return $this->response($movie, 200);
    }

    #[Route('movie/{movie_id}', name: 'api_delete_movie', methods: ['DELETE'])]
    public function deleteMovie(int $movie_id, MovieRepository $movieRepository, EntityManagerInterface $em): Response
    {
        $movie = $movieRepository->find($movie_id);

        if(!$movie) {
            return $this->response(['message' => 'The resource you requested could not be found.'], 404);
        }

        $em->remove($movie);
        $em->flush();

        return $this->response(null, 204);
    }

    #[Route('movie/{movie_id}/category/{category_id}', name: 'api_add_movie_category', methods: ['POST'])]
    public function addMovieCategory(int $movie_id, int $category_id, MovieRepository $movieRepository,
                                        CategoryRepository $categoryRepository, EntityManagerInterface $em): Response
    {
        $movie    = $movieRepository->find($movie_id);
        $category = $categoryRepository->find($category_id);

        if(!$movie || !$category) {
            return $this->response(['message' => 'The resource you requested could not be found.'], 404);
        }

        $movie->addCategory($category);
        $em->flush();

        return $this->response($movie, 201);
    }

    #[Route('movie/{movie_id}/category/{category_id}', name: 'api_delete_movie_category', methods: ['DELETE'])]
    public function deleteMovieCategory(int $movie_id, int $category_id, MovieRepository $movieRepository,
                                        CategoryRepository $categoryRepository, EntityManagerInterface $em): Response
    {
        $movie    = $movieRepository->find($movie_id);
        $category = $categoryRepository->find($category_id);

        if(!$movie || !$category) {
            return $this->response(['message' => 'The resource you requested could not be found.'], 404);
        }

        $movie->removeCategory($category);
        $em->flush();

        return $this->response($movie, 200);
    }
}