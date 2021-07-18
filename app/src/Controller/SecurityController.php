<?php


namespace App\Controller;


use App\Entity\SubUser;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: "api")]
class SecurityController extends AbstractController
{

    #[Route('login', name: "_user_login", methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $user = $this->getUser();

        return $this->json([
            'username' => $user->getUserIdentifier(),
            'roles' => $user->getRoles()
        ]);
    }


    public function getToken(User $user)
    {

    }

    public function checkTokenValidity($token)
    {

    }

}