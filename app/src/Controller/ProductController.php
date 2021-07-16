<?php

namespace App\Controller;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/product', name: 'api_product')]
class ProductController extends AbstractController
{
    private $serializer;

    /**
     * ProductController constructor.
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
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
            $this->serializer->serialize($product, 'json'),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }
}
