<?php


namespace App\Controller;


use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\PaginationService;
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

#[Route('/user', name: 'api_user')]
class UserController extends AbstractController
{
    /**
     * @var Hateoas|SerializerInterface
     */
    private Hateoas|SerializerInterface $serializer;

    /**
     * @var UrlGeneratorInterface
     */
    private UrlGeneratorInterface $urlGenerator;

    /**
     * UserController constructor.
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
     * Lists all the users able to connect to api
     *
     * @OA\Tag(name="User")
     *
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
     *
     * @Security(name="Bearer")
     *
     * @OA\Response(response="200", description="Success")
     * @OA\Response(response="401", description="Not authorized")
     * @OA\Response(response="403", description="Access denied")
     *
     * @param UserRepository $userRepository
     * @param Request $request
     * @param PaginationService $pagination
     * @return Response
     */
    #[Route('/', name: '_index', methods:['GET'])]
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
     * @OA\Tag(name="User")
     *
     * @OA\Response(response="200", description="Success")
     * @OA\Response(response="401", description="Not authorized")
     * @OA\Response(response="403", description="Access denied")
     * @OA\Response(response="404", description="Not found")
     *
     * @Security(name="Bearer")
     *
     * @param User $user
     * @return Response
     */
    #[Route('/{id}', name: '_item', methods:['GET'])]
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
            ], 400);
        }

    }
}