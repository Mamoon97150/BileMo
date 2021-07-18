<?php


namespace App\Controller;


use App\Entity\User;
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

    /**
     * Login to gain access to API
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