<?php

namespace App\Controller;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Hateoas\HateoasBuilder;
use Hateoas\UrlGenerator\SymfonyUrlGenerator;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/product', name: 'api_product')]
class ProductController extends AbstractController
{
    private SerializerInterface $serializer;
    private UrlGeneratorInterface $urlGenerator;

    /**
     * ProductController constructor.
     * @param SerializerInterface $serializer
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;

        $this->serializer = HateoasBuilder::create()
            ->setDefaultJsonSerializer()
            ->setUrlGenerator(
                null, // By default all links uses the generator configured with the null name
                new SymfonyUrlGenerator($this->urlGenerator)
            )
            ->build();
    }

    #[Route('/collection', name: '_collection', methods:['GET'])]
    public function index(ProductsRepository $productsRepository): Response
    {
        $products = $productsRepository->findAll();
        return new JsonResponse(
            $this->serializer->serialize($products, 'json', SerializationContext::create()->setGroups(array('list'))),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}', name: '_item', methods:['GET'])]
    public function item(Products $product): Response
    {
        return new JsonResponse(
            $this->serializer->serialize($product, 'json', SerializationContext::create()->setGroups(array('list', 'details'))),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }
}
