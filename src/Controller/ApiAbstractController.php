<?php

namespace App\Controller;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class ApiAbstractController extends AbstractController
{
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
    protected SerializerInterface $serializer;

    /**
     * Handle input and output in supported formats.
     *
     * @param RequestStack $request
     * @param SerializerInterface $serializer
     */
    public function __construct(RequestStack $request, SerializerInterface $serializer)
    {
        $request = $request->getCurrentRequest();

        $outputFormat = strtolower($request->headers->get('X-Output-Format', 'json'));
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
        if (!$groups) {
            $groups[] = 'public';
        }

        $data = $this->serializer->serialize($data, $this->outputFormat, ['groups' => $groups]);
        return new Response($data, $status, ['Content-Type' => 'application/' . $this->outputFormat]);
    }

    /**
     * Handle a request content.
     *
     * @throws Exception
     */
    protected function deserializeRequest($request, string $resource)
    {
        try {
            $data = $this->serializer->deserialize($request->getContent(), $resource, 'json');
        } catch (NotEncodableValueException $e) {
            return throw new Exception('Data is wrongly formatted in json.');
        }

//        $contentType = $request->getContentType();
        return $data;
    }
}