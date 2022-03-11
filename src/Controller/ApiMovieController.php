<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Repository\MovieRepository;
use App\Service\EntityUpdaterService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('api/')]
class ApiMovieController extends AbstractController
{
    /**
     * Request input format.
     *
     * @var string
     */
    private string $inputFormat;

    /**
     * Request output format.
     *
     * @var string
     */
    private string $outputFormat;

    /**
     * List of supported formats.
     *
     * @var array
     */
    private array $supportedFormats = ['json', 'xml'];

    public function __construct(RequestStack $request)
    {
        $request = $request->getCurrentRequest();

        $inputFormat  = strtolower($request->headers->get('X-Data-Format-Input', 'json'));
        $outputFormat = strtolower($request->headers->get('X-Data-Format-Output', 'json'));

        $this->inputFormat  = in_array($inputFormat, $this->supportedFormats)  ? $inputFormat  : 'json';
        $this->outputFormat = in_array($outputFormat, $this->supportedFormats) ? $outputFormat : 'json';
    }

    #[Route('movie/{movie_id}', name: 'api_get_movie', defaults: ['movie_id' => false], methods: ['GET'])]
    public function getMovie(false|int $movie_id, MovieRepository $movieRepository,
                             SerializerInterface $serializer): Response
    {
        $movies = $movie_id ?
            $movieRepository->find($movie_id) :
            $movieRepository->findAll();

        if(!$movies && $movie_id) {
            return $this->json(['message' => 'The resource you requested could not be found.'], 404);
        }

        $movies = $serializer->serialize($movies, $this->outputFormat);
        return new Response($movies, 200, ['Content-Type' => 'application/' . $this->outputFormat]);
    }

    #[Route('movie', name: 'api_add_movie', methods: ['POST'])]
    public function addMovie(Request $request, SerializerInterface $serializer, EntityManagerInterface $em,
                             ValidatorInterface $validator): Response
    {
        // Create entity from request data.
        try {
            $movie = $serializer->deserialize($request->getContent(), Movie::class, $this->inputFormat);
        } catch (NotEncodableValueException) {
            return $this->json(['message' => 'Data is wrongly formatted in ' . $this->inputFormat . '.'], 400);
        } catch (NotNormalizableValueException $e) {
            return $this->json(['message' => $e->getMessage()], 422);
        }

        // Validate entity.
        $errors = $validator->validate($movie);

        if (count($errors)) {
            return $this->json($errors, 422);
        }

        $em->persist($movie);
        $em->flush();

        $movie = $serializer->serialize($movie, $this->outputFormat);
        return new Response($movie, 200, ['Content-Type' => 'application/' . $this->outputFormat]);
    }

    #[Route('movie/{movie_id}', name: 'api_update_movie', methods: ['PUT'])]
    public function updateMovie(int $movie_id, Request $request, MovieRepository $movieRepository,
                                SerializerInterface $serializer, EntityManagerInterface $em,
                                EntityUpdaterService $updater, ValidatorInterface $validator): Response
    {
        $movie = $movieRepository->find($movie_id);

        if (!$movie) {
            return $this->json(['message' => 'The resource you requested could not be found.'], 404);
        }

        $data = $request->getContent();

        // Handle json_decode method with xml data.
        if ($this->inputFormat === 'xml') {
            try {
                $data = json_encode(simplexml_load_string($data));
            } catch (Exception) {
                return $this->json(['message' => 'XML content is wrongly formatted.'], 400);
            }
        }

        $data = json_decode($data, true);

        if (!$data) {
            return $this->json(['message' => 'Data is wrongly formatted in ' . $this->inputFormat . '.'], 400);
        }

        // Update entity from request data.
        try {
            $movie = $updater->update($movie, $data);
        } catch (Exception $e) {
            return $this->json(['message' => $e->getMessage()], 422);
        }

        // Validate entity.
        $errors = $validator->validate($movie);

        if (count($errors)) {
            return $this->json($errors, 422);
        }

        $em->persist($movie);
        $em->flush();

        $movie = $serializer->serialize($movie, $this->outputFormat);
        return new Response($movie, 200, ['Content-Type' => 'application/' . $this->outputFormat]);
    }

    #[Route('movie/{movie_id}', name: 'api_delete_movie', defaults: ['movie_id' => false], methods: ['DELETE'])]
    public function deleteMovie(false|int $movie_id, MovieRepository $movieRepository, SerializerInterface $serializer,
                                EntityManagerInterface $em): Response
    {
        $movies = $movie_id ?
            $movieRepository->findBy(['id' => $movie_id]) :
            $movieRepository->findAll();

        if(!$movies && $movie_id) {
            return $this->json(['message' => 'The resource you requested could not be found.'], 404);
        }

        foreach($movies as $movie) {
            $em->remove($movie);
        }

        $em->flush();

        return new Response(null, 204, ['Content-Type' => 'application/' . $this->outputFormat]);
    }
}