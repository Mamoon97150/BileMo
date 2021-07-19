<?php


namespace App\Controller;


use App\Entity\User;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SecurityController
 * @package App\Controller
 */
#[Route('/', name: "api")]
class SecurityController extends AbstractController
{
    //TODO: add token to header
//TODO: when subUser gives info check if part of subUser table -> getUser and connect with him
    /**
     * Login to gain access to API
     * @OA\Tag(name="Login")
     *
     * @OA\Response(response="200", description="Success")
     * @OA\Response(response="401", description="Not authorized")
     *
     * @OA\RequestBody(
     *     description="Login credentials",
     *     required=true,
     *     @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(
     *                  property="name",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="password",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('login', name: "_user_login", methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $user = $this->getUser();
        //subuser credentials

        return $this->json([
            'username' => $user->getUserIdentifier(),
            'roles' => $user->getRoles()
        ]);
    }


    /**
     * @param User $user
     */
    public function getToken(User $user)
    {

    }

}