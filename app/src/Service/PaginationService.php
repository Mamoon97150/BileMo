<?php


namespace App\Service;


use App\Repository\ProductsRepository;
use App\Repository\UserRepository;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use Symfony\Component\HttpFoundation\Request;

class PaginationService
{
    public function getPaginatedUser(Request $request,UserRepository $userRepository): PaginatedRepresentation
    {
        $page = (int)$request->query->getInt('page', 1);
        $users = $userRepository->getUserPaginator($page);
        $count = count($userRepository->findAll());

        return new PaginatedRepresentation(
            new CollectionRepresentation($users),
            'api_user_index',
            array(),
            $page,
            UserRepository::PAGINATOR_PER_PAGE,
            ceil($count/ UserRepository::PAGINATOR_PER_PAGE),
            'page',
            'limit',
            true,
            count($users)
        );
    }

    public function getPaginatedProducts(Request $request, ProductsRepository $productsRepository): PaginatedRepresentation
    {
        $page = (int)$request->query->getInt('page', 1);
        $products = $productsRepository->getProductsPaginator($page);
        $count = count($productsRepository->findAll());

        return new PaginatedRepresentation(
            new CollectionRepresentation($products),
            'api_product_collection',
            array(),
            $page,
            ProductsRepository::PAGINATOR_PER_PAGE,
            ceil($count/ ProductsRepository::PAGINATOR_PER_PAGE),
            'page',
            'limit',
            true,
            count($products)
        );
    }
}