<?php


namespace App\Security;


use App\Entity\SubUser;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class UserVoter extends Voter
{
    private Security $security;
    const OWN = 'USER_OWN';

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::OWN])) {
            return false;
        }

        // only vote on `User` or 'SubUser objects
        if (!$subject instanceof User && !$subject instanceof SubUser) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {

        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $subject is a User or SubUser object, thanks to `supports()`
        /** @var User|SubUser $userDetails */
        $userDetails = $subject;

        switch ($attribute) {
            case self::OWN:
                return $this->canView($userDetails, $user);
            /*case self::EDIT:
                return $this->canEdit($userDetails, $user);*/
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(User|SubUser $userDetails, User $user): bool
    {
        if ($userDetails instanceof SubUser)
        {
            return $userDetails->getUsers()->contains($user);
        }
        return $user === $userDetails;
    }
}