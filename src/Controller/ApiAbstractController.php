<?php

namespace App\Controller;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
     * @param object|array|null $data The data array.
     * @param int $status The response status.
     * @param array|null $groups Entity groups.
     * @return Response
     */
    protected function response(object|array|null $data, int $status, array $groups = null): Response
    {
        $groups[] = 'public';

        $data = $this->serializer->serialize($data, $this->outputFormat, ['groups' => $groups]);
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

            $decode = json_decode($data, true);

            if (!$decode) {
                throw new Exception('Data is wrongly formatted in ' . $this->inputFormat . '.');
            }

            return $decode;
        } catch (Exception) {
            throw new Exception('Data is wrongly formatted in ' . $this->inputFormat . '.');
        }
    }
}