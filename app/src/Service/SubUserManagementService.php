<?php


namespace App\Service;


use App\Entity\SubUser;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface as SymfonySerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SubUserManagementService
{
    /**
     * @var SymfonySerializerInterface
     */
    private SymfonySerializerInterface $symfonySerializer;

    /**
     * SubUserManagementService constructor.
     * @param SymfonySerializerInterface $symfonySerializer
     */
    public function __construct(SymfonySerializerInterface $symfonySerializer)
    {
        $this->symfonySerializer = $symfonySerializer;
    }


    public function addSubUser(Request $request, User $user, ValidatorInterface $validator, EntityManagerInterface $manager)
    {
        /** @var SubUser $subs */
        $subs = $this->symfonySerializer->deserialize(
            $request->getContent(),
            SubUser::class,
            'json'
        );


        $subs->setUser($user);


        $errors = $validator->validate($subs);

        if (count($errors) > 0){
            return $errors;
        }

        $manager->persist($subs);
        $manager->flush();

        return $subs;
    }

    public function editSubUser(Request $request, SubUser $subUser, ValidatorInterface $validator, EntityManagerInterface $manager)
    {
        $this->symfonySerializer->deserialize(
            $request->getContent(),
            SubUser::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $subUser]
        );

        $errors = $validator->validate($subUser);

        if (count($errors) > 0){
            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }

        $manager->flush();

        return $subUser;
    }

    public function eraseSubUser(SubUser $subUser, EntityManagerInterface $manager)
    {
        $manager->remove($subUser);
        $manager->flush();
    }

}