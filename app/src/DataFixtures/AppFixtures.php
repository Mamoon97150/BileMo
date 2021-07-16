<?php

namespace App\DataFixtures;

use App\Entity\Products;
use App\Entity\SubUser;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordHasherInterface
     */
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * AppFixtures constructor.
     * @param UserPasswordHasherInterface $passwordHasher
     */
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        $users = [];
        for ($i = 1; $i <= 10; $i++){
            $user = new User();
            $user->setName(sprintf("company %d", $i))
                ->setPassword($this->passwordHasher->hashPassword($user, "password"));

            $manager->persist($user);

            $users[] = $user;
        }
        foreach ($users as $user){
            for ($j =1; $j <= 5; $j++){
                $product = new Products();
                $product->setName(sprintf("phone %d", $j))
                    ->setBrand(sprintf("brand %d", $faker->randomDigitNotNull()))
                    ->setQuantity($faker->numberBetween(0, 200))
                ;

                shuffle($users);
                foreach (array_slice($users, 0, 5) as $seller){
                    $product->addToClient($seller);
                }
                $manager->persist($product);

                for ($k = 1; $k <= 10; $k++){
                    $subs = new SubUser();
                    $subs->setEmail($faker->email())
                        ->setUsername($faker->userName())
                        ->addUser($user)
                    ;

                    $manager->persist($subs);
                }
            }
        }

        $manager->flush();
    }
}
