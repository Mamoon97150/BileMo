<?php

namespace App\Controller;

use App\Entity\SubUser;
use App\Entity\User;
use App\Repository\SubUserRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Type\Exception\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/user/sub', name: 'api_sub')]
class SubUserController extends AbstractController
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

    #[Route('/', name: '_collection', methods:['GET'])]
    public function index(SubUserRepository $userRepository): Response
    {
        $subs = $userRepository->findAll();
        return new JsonResponse(
            $this->serializer->serialize($subs, 'json', SerializationContext::create()->setGroups("sub_list")),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}', name: '_item', methods:['GET'])]
    public function item(SubUser $subUser): Response
    {
        return new JsonResponse(
            $this->serializer->serialize($subUser, 'json', SerializationContext::create()->setGroups("sub_list")),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/create', name: '_create', methods:['POST'])]
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
            $subs = $this->serializer->deserialize(
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

            return new JsonResponse(
                $this->serializer->serialize($subs, 'json', SerializationContext::create()->setGroups("sub_list")),
                JsonResponse::HTTP_CREATED,
                ["location" => $urlGenerator->generate("api_sub_create", ["id" => $subs->getId()])],
                true
            );

        }catch (\Exception $exception){
            return $this->json([
                'status' => 400,
                'message' => $exception->getMessage()
            ], 400);
        }
    }

    #[Route('/{id}/update', name: '_update', methods:['PUT'])]
    public function update(SubUser $subUser,Request $request, EntityManagerInterface $manager, ValidatorInterface $validator): JsonResponse
    {
        try {
            $context= new DeserializationContext();
            $context->setAttribute('target', $subUser);

            /** @var SubUser $subs */
            $subs = $this->serializer->deserialize(
                $request->getContent(),
                SubUser::class,
                'json',
                $context
            );



            $errors = $validator->validate($subs);

            if (count($errors) > 0){
                return $this->json($errors, 400);
            }

            $manager->flush();

            return $this->json($subs, 201, [], ['groups' => 'post:read']);
        }catch ( Exception $exception){
            return $this->json([
                'status' => 400,
                'message' => $exception->getMessage()
            ], 400);
        }

    }


}
