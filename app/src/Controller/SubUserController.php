<?php

namespace App\Controller;

use App\Entity\SubUser;
use App\Service\SubUserManagementService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Hateoas\Hateoas;
use Hateoas\HateoasBuilder;
use Hateoas\UrlGenerator\SymfonyUrlGenerator;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class SubUserController
 * Create CRUD for SubUsers and show for User
 * @package App\Controller
 */
#[Route('/user/sub', name: 'api_sub')]
class SubUserController extends AbstractController
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
     * Creates hateoas serializer (cache, urlGenerator)
     *
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;

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
    public function getSubUser(SubUser $subUser): Response
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
     * @IsGranted("ROLE_USER")
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param ValidatorInterface $validator
     * @param SubUserManagementService $subUserManagement
     * @return Response
     */
    #[Route('/create', name: '_create', methods:['POST'])]
    public function createSubUser(
        Request $request,
        EntityManagerInterface $manager,
        ValidatorInterface $validator,
        SubUserManagementService $subUserManagement
    ): Response
    {
        try {
            $subs = $subUserManagement->addSubUser($request, $this->getUser(), $validator, $manager);
            return new JsonResponse(
                $this->serializer->serialize($subs, 'json', SerializationContext::create()->setGroups("sub_list")),
                JsonResponse::HTTP_CREATED,
                ["location" => $this->urlGenerator->generate("api_sub_create", ["id" => $subs->getId()])],
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
     * @param SubUserManagementService $subUserManagement
     * @return JsonResponse
     */
    #[Route('/{id}', name: '_update', methods:['PUT'])]
    public function updateSubUser(
        SubUser $subUser,
        Request $request,
        EntityManagerInterface $manager,
        ValidatorInterface $validator,
        SubUserManagementService $subUserManagement
    ): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted('USER_OWN', $subUser);

            $subUser = $subUserManagement->editSubUser($request, $subUser, $validator, $manager);

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
     * @param SubUserManagementService $subUserManagement
     * @return JsonResponse
     */
    #[Route('/{id}', name: "_delete", methods: ['DELETE'])]
    public function deleteSubUser( SubUser $subUser, EntityManagerInterface $manager, SubUserManagementService $subUserManagement): JsonResponse
    {
        try {
            $subUserManagement->eraseSubUser($subUser, $manager);

            return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
        }catch (Exception $exception){
            return $this->json([
                'status' => $exception->getCode(),
                'message' => $exception->getMessage()
            ], $exception->getCode());
        }

    }
}
