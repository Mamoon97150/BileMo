<?php

namespace App\Controller;

use App\Entity\SubUser;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Hateoas\Hateoas;
use Hateoas\HateoasBuilder;
use Hateoas\UrlGenerator\SymfonyUrlGenerator;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
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
#[Route('/user/sub', name: 'api_sub')]
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
            ->setUrlGenerator(
                null, // By default all links uses the generator configured with the null name
                new SymfonyUrlGenerator($this->urlGenerator)
            )
            ->build();
    }

    /**
     *Show the details of a Sub-User
     * @OA\Tag(name="Sub-User")
     *
     * @OA\Response(response="200", description="Success")
     * @OA\Response(response="401", description="Not authorized")
     * @OA\Response(response="403", description="Access denied")
     * @OA\Response(response="404", description="Not found")
     *
     * @Security(name="Bearer")
     *
     * @param SubUser $subUser
     * @return Response
     */
    #[Route('/{id}', name: '_item', methods:['GET'])]
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
            ], 400);
        }

    }

    /**
     * Create a new SubUser
     * @OA\Tag(name="Sub-User")
     *
     * @OA\RequestBody(
     *     description="Login credentials",
     *     required=true,
     *     @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(
     *                  property="username",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="email",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     *
     * @OA\Response(response="201", description="Success")
     * @OA\Response(response="401", description="Not authorized")
     * @OA\Response(response="403", description="Access denied")
     * @OA\Response(response="400", description="Not right format")
     *
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UrlGeneratorInterface $urlGenerator
     * @param ValidatorInterface $validator
     * @return Response
     */
    #[Route('/create', name: '_create', methods:['POST'])]
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

            /** @var User $user */
            $user = $this->getUser();
            $subs->setUser($user);

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

    /**
     * Update an existing SubUser
     * @OA\Tag(name="Sub-User")
     *
     * @OA\Response(response="200", description="Success")
     * @OA\Response(response="400", description="Bad request")
     * @OA\Response(response="401", description="Not authorized")
     * @OA\Response(response="403", description="Access denied")
     * @OA\Response(response="404", description="Not found")
     *
     * @Security(name="Bearer")
     *
     * @param SubUser $subUser
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/{id}', name: '_update', methods:['PUT'])]
    public function update(SubUser $subUser,Request $request, EntityManagerInterface $manager, ValidatorInterface $validator): JsonResponse
    {
        try {

            $this->denyAccessUnlessGranted('USER_OWN', $subUser);

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

            return $this->json($subUser, JsonResponse::HTTP_OK, [], ['groups' => 'post:read']);
        }catch ( Exception $exception){
            return $this->json([
                'status' => $exception->getCode(),
                'message' => $exception->getMessage()
            ], $exception->getCode());
        }

    }

    /**
     * Delete a SubUser
     * @OA\Tag(name="Sub-User")
     *
     * @OA\Response(response="204", description="Success")
     * @OA\Response(response="401", description="Not authorized")
     * @OA\Response(response="403", description="Access denied")
     * @OA\Response(response="404", description="Not found")
     *
     *
     * @Security(name="Bearer")
     *
     * @param SubUser $subUser
     * @param EntityManagerInterface $manager
     * @return JsonResponse
     */
    #[Route('/{id}', name: "_delete", methods: ['DELETE'])]
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
