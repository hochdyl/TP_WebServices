<?php

namespace App\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

#[Route('api/')]
abstract class ApiAbstractController extends AbstractController
{
    /**
     * Request input format.
     *
     * @var string
     */
    protected string $inputFormat;

    /**
     * Request output format.
     *
     * @var string
     */
    protected string $outputFormat;

    /**
     * List of supported formats.
     *
     * @var array
     */
    private array $supportedFormats = ['json', 'xml'];

    /**
     * Data serializer.
     *
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * Handle input and output in supported formats.
     *
     * @param RequestStack $request
     * @param SerializerInterface $serializer
     */
    public function __construct(RequestStack $request, SerializerInterface $serializer)
    {
        $request = $request->getCurrentRequest();

        $inputFormat  = strtolower($request->headers->get('X-Data-Format-Input', 'json'));
        $outputFormat = strtolower($request->headers->get('X-Data-Format-Output', 'json'));

        $this->inputFormat  = in_array($inputFormat, $this->supportedFormats)  ? $inputFormat  : 'json';
        $this->outputFormat = in_array($outputFormat, $this->supportedFormats) ? $outputFormat : 'json';

        $this->serializer = $serializer;
    }

    /**
     * Return a response in the chosen format.
     *
     * @param object|array $data The data array.
     * @param int $status The response status.
     * @return Response
     */
    protected function response(object|array $data, int $status): Response
    {
        $data = $this->serializer->serialize($data, $this->outputFormat);
        return new Response($data, $status, ['Content-Type' => 'application/' . $this->outputFormat]);
    }

    /**
     * Handle others input formats.
     *
     * @param string $data Data in a specific format
     * @return array
     * @throws Exception
     */
    protected function inputDecode(string $data): array
    {
        try {
            if ($this->inputFormat === 'xml') {
                $data = json_encode(simplexml_load_string($data));
            }
            return json_decode($data, true);
        } catch (Exception) {
            throw new Exception('XML content is wrongly formatted.');
        }
    }
}