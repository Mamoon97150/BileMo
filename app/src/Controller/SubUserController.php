<?php

namespace App\Controller;

use App\Entity\SubUser;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Hateoas\HateoasBuilder;
use Hateoas\UrlGenerator\SymfonyUrlGenerator;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface as SymfonySerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/user', name: 'api')]
class SubUserController extends AbstractController
{
    private SerializerInterface $serializer;
    private UrlGeneratorInterface $urlGenerator;
    private SymfonySerializerInterface $symfonySerializer;

    /**
     * ProductController constructor.
     * @param SerializerInterface $serializer
     * @param UrlGeneratorInterface $urlGenerator
     * @param SymfonySerializerInterface $symfonySerializer
     */
    public function __construct(SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator, SymfonySerializerInterface $symfonySerializer)
    {
        $this->urlGenerator = $urlGenerator;
        $this->symfonySerializer = $symfonySerializer;

        $this->serializer = HateoasBuilder::create()
            ->setDefaultJsonSerializer()
            ->setUrlGenerator(
                null, // By default all links uses the generator configured with the null name
                new SymfonyUrlGenerator($this->urlGenerator)
            )
            ->build();
    }

    #[Route('/', name: '_user_index', methods:['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $subs = $userRepository->findAll();
        return new JsonResponse(
            $this->serializer->serialize($subs, 'json', SerializationContext::create()->setGroups("sub_list")),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}', name: '_user_item', methods:['GET'])]
    public function collect(User $user): Response
    {
        return new JsonResponse(
            $this->serializer->serialize($user, 'json', SerializationContext::create()->setGroups(array("sub_list", "sub_details"))),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/sub/{id}', name: '_sub_item', methods:['GET'])]
    public function item(SubUser $subUser): Response
    {
        return new JsonResponse(
            $this->serializer->serialize($subUser, 'json', SerializationContext::create()->setGroups("sub_details")),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }

    //TODO: get the user fromm connection
    #[Route('/sub/create', name: '_sub_create', methods:['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $manager,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validator,
        UserRepository $userRepository
    ): Response
    {
        $user = $userRepository->find(rand(0, 10));
        try {

            /** @var SubUser $subs */
            $subs = $this->symfonySerializer->deserialize(
                $request->getContent(),
                SubUser::class,
                'json'
            );
            $subs->addUser($user);

            $errors = $validator->validate($subs);
            if (count($errors) > 0){
                return $this->json($errors, 400);
            }

            $manager->persist($subs);
            $manager->flush();

            dump($subs);

            return new JsonResponse(
                $this->serializer->serialize($subs, 'json', SerializationContext::create()->setGroups("sub_list")),
                JsonResponse::HTTP_CREATED,
                ["location" => $urlGenerator->generate("api_sub_create", ["id" => $subs->getId()])],
                true
            );

        }catch (Exception $exception){
            return $this->json([
                'status' => 404,
                'message' => $exception->getMessage()
            ], 404);
        }
    }

    //TODO: ask if ok to use both serializer
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
                'status' => 400,
                'message' => $exception->getMessage()
            ], 400);
        }

    }

    #[Route('/sub/{id}', name: "_sub_delete", methods: ['DELETE'])]
    public function delete( SubUser $subUser, EntityManagerInterface $manager): JsonResponse
    {
        try {
            $manager->remove($subUser);
            $manager->flush();

            return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
        }catch (NotFoundHttpException $exception){
            return $this->json([
                'status' => 400,
                'message' => $exception->getMessage()
            ], 400);
        }

    }
}
