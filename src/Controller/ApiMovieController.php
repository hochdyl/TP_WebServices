<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Exception;
use App\Entity\Movie;
use App\Repository\MovieRepository;
use App\Service\EntityUpdaterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

#[Route('api/')]
class ApiMovieController extends ApiAbstractController
{
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
    public function addMovie(Request $request, SerializerInterface $serializer, EntityManagerInterface $em,
                             ValidatorInterface $validator): Response
    {
        // Create entity from request data.
        try {
            $movie = $serializer->deserialize($request->getContent(), Movie::class, $this->inputFormat);
        } catch (NotEncodableValueException) {
            return $this->response(['message' => 'Data is wrongly formatted in ' . $this->inputFormat . '.'], 400);
        } catch (NotNormalizableValueException $e) {
            return $this->response(['message' => $e->getMessage()], 422);
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

        // Handle others input formats.
        try {
            $data = $this->inputDecode($request->getContent());
        } catch (Exception $e) {
            return $this->response(['message' => $e->getMessage()], 400);
        }

        if (!$data) {
            return $this->response(['message' => 'Data is wrongly formatted in ' . $this->inputFormat . '.'], 400);
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