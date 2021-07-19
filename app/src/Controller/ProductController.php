<?php

namespace App\Controller;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use App\Service\PaginationService;
use Hateoas\Hateoas;
use Hateoas\HateoasBuilder;
use Hateoas\UrlGenerator\SymfonyUrlGenerator;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ProductController
 * @package App\Controller
 */
#[Route('/product', name: 'api_product')]
class ProductController extends AbstractController
{
    /**
     * @var Hateoas|SerializerInterface
     */
    private SerializerInterface|Hateoas $serializer;
    /**
     * @var UrlGeneratorInterface
     */
    private UrlGeneratorInterface $urlGenerator;

    /**
     * ProductController constructor.
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
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

    /**
     * Get a lists of  all products
     *
     * @OA\Tag(name="Products")
     * @OA\Parameter(
     *      name="page",
     *      in="query",
     *      description="Current page",
     *      required=false,
     * )
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Results per page",
     *     required=false,
     * )
     * @OA\Response(response="200", description="Success")
     * @OA\Response(response="401", description="Not authorized")
     *
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param ProductsRepository $productsRepository
     * @param PaginationService $pagination
     * @return Response
     */
    #[Route('/', name: '_collection', methods:['GET'])]
    public function index(Request $request, ProductsRepository $productsRepository, PaginationService $pagination): Response
    {
        $products = $pagination->getPaginatedProducts($request, $productsRepository);
        return new JsonResponse(
            $this->serializer->serialize($products, 'json', SerializationContext::create()->setGroups(array('list', 'Default'))),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }

    /**
     * Show one product
     *
     * @OA\Tag(name="Products")
     * @OA\Parameter(
     *     description="Id of the product",
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(type="integer", format="int64")
     * )
     *
     * @OA\Response(response="200", description="Success")
     * @OA\Response(response="401", description="Not authorized")
     * @OA\Response(response="404", description="Not found")
     *
     * @Security(name="Bearer")
     *
     * @param Products $product
     * @return Response
     */
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
