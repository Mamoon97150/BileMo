<?php

namespace App\Controller;

use App\Entity\SubUser;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Hateoas\Hateoas;
use Hateoas\HateoasBuilder;
use Hateoas\UrlGenerator\SymfonyUrlGenerator;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface as SymfonySerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class SubUserController
 * Create CRUD for SubUsers and show for User
 * @package App\Controller
 */
#[Route('/user', name: 'api')]
class SubUserController extends AbstractController
{
    //Todo: affiner les exceptions?
    /**
     * @var Hateoas|SerializerInterface
     */
    private SerializerInterface|Hateoas $serializer;
    /**
     * @var UrlGeneratorInterface
     */
    private UrlGeneratorInterface $urlGenerator;
    /**
     * @var SymfonySerializerInterface
     */
    private SymfonySerializerInterface $symfonySerializer;

    /**
     * ProductController constructor.
     * Creates hateoas serializer (cache, urlGenerator)
     *
     * @param UrlGeneratorInterface $urlGenerator
     * @param SymfonySerializerInterface $symfonySerializer
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, SymfonySerializerInterface $symfonySerializer)
    {
        $this->urlGenerator = $urlGenerator;
        $this->symfonySerializer = $symfonySerializer;

        $this->serializer = HateoasBuilder::create()
            ->setCacheDir('cache')
            ->setDefaultJsonSerializer()
            ->setUrlGenerator(
                null, // By default all links uses the generator configured with the null name
                new SymfonyUrlGenerator($this->urlGenerator)
            )
            ->build();
    }

    /**
     * Lists all the users able to connect to api
     * @param UserRepository $userRepository
     * @param Request $request
     * @param PaginationService $pagination
     * @return Response
     */
    #[Route('/', name: '_user_index', methods:['GET'])]
    public function index(UserRepository $userRepository, Request $request, PaginationService $pagination): Response
    {
        try {
            $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
            $subs = $pagination->getPaginatedUser($request, $userRepository);

            return new JsonResponse(
                $this->serializer->serialize($subs, 'json', SerializationContext::create()->setGroups(["sub_list", "Default"])),
                JsonResponse::HTTP_OK,
                [],
                true
            );
        }catch (Exception $exception) {
            return $this->json([
                'status' => $exception->getCode(),
                'message' => $exception->getMessage()
            ], $exception->getCode());
        }
    }

    /**
     * Show the details of a User and all his sub users
     * @param User $user
     * @return Response
     */
    #[Route('/{id}', name: '_user_item', methods:['GET'])]
    public function collect(User $user): Response
    {
        try {

            $this->denyAccessUnlessGranted('USER_OWN', $user);
            return new JsonResponse(
                $this->serializer->serialize($user, 'json', SerializationContext::create()->setGroups(array("sub_list", "sub_details", "Default"))),
                JsonResponse::HTTP_OK,
                [],
                true
            );

        }catch (Exception $exception){
            return $this->json([
                'status' => $exception->getCode(),
                'message' => $exception->getMessage()
            ], $exception->getCode());
        }

    }

    /**
     * Show the details of a sub user and the User he is linked to
     * @param SubUser $subUser
     * @return Response
     */
    #[Route('/sub/{id}', name: '_sub_item', methods:['GET'])]
    public function item(SubUser $subUser): Response
    {
        try {
            $this->denyAccessUnlessGranted('USER_OWN', $subUser);
            return new JsonResponse(
                $this->serializer->serialize($subUser, 'json', SerializationContext::create()->setGroups("sub_details")),
                JsonResponse::HTTP_OK,
                [],
                true
            );
        }catch (Exception $exception){
            return $this->json([
                'status' => $exception->getCode(),
                'message' => $exception->getMessage()
            ], $exception->getCode());
        }

    }

    /**
     * Create a new SubUser
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UrlGeneratorInterface $urlGenerator
     * @param ValidatorInterface $validator
     * @param UserRepository $userRepository
     * @return Response
     */
    #[Route('/sub/create', name: '_sub_create', methods:['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $manager,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validator
    ): Response
    {
        try {

            /** @var SubUser $subs */
            $subs = $this->symfonySerializer->deserialize(
                $request->getContent(),
                SubUser::class,
                'json'
            );
            $user = $this->getUser();
            $subs->addUser($user);

            $this->denyAccessUnlessGranted('USER_OWN', $user);

            $errors = $validator->validate($subs);
            if (count($errors) > 0){
                return $this->json($errors, 400);
            }

            $manager->persist($subs);
            $manager->flush();

            return new JsonResponse(
                $this->serializer->serialize($subs, 'json', SerializationContext::create()->setGroups("sub_list")),
                JsonResponse::HTTP_CREATED,
                ["location" => $urlGenerator->generate("api_sub_create", ["id" => $subs->getId()])],
                true
            );

        }catch (Exception $exception){
            return $this->json([
                'status' => $exception->getCode(),
                'message' => $exception->getMessage()
            ], $exception->getCode());
        }
    }

    //TODO: ask if ok to use both serializer

    /**
     * Update an existing SubUser
     *
     * @param SubUser $subUser
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/sub/{id}', name: '_sub_update', methods:['PUT'])]
    public function update(SubUser $subUser,Request $request, EntityManagerInterface $manager, ValidatorInterface $validator): JsonResponse
    {
        try {

            $this->symfonySerializer->deserialize(
                $request->getContent(),
                SubUser::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $subUser]
            );

            $errors = $validator->validate($subUser);

            if (count($errors) > 0){
                return $this->json($errors, 400);
            }

            $manager->flush();

            return $this->json($subUser, 201, [], ['groups' => 'post:read']);
        }catch ( Exception $exception){
            return $this->json([
                'status' => $exception->getCode(),
                'message' => $exception->getMessage()
            ], $exception->getCode());
        }

    }

    /**
     * Delete a SubUser
     *
     * @param SubUser $subUser
     * @param EntityManagerInterface $manager
     * @return JsonResponse
     */
    #[Route('/sub/{id}', name: "_sub_delete", methods: ['DELETE'])]
    public function delete( SubUser $subUser, EntityManagerInterface $manager): JsonResponse
    {
        try {
            $manager->remove($subUser);
            $manager->flush();

            return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
        }catch (Exception $exception){
            return $this->json([
                'status' => $exception->getCode(),
                'message' => $exception->getMessage()
            ], $exception->getCode());
        }

    }
}
