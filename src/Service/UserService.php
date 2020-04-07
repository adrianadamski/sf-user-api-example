<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService
{
    protected EntityManagerInterface $entityManager;

    protected UserPasswordEncoderInterface $passwordEncoder;

    protected ValidatorInterface $validator;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->validator = $validator;
    }

    public function getUserByEmail(string $email): User
    {
        /** @var User|null $user */
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneByEmail($email);

        if (!$user) {
            throw new NotFoundHttpException('User not found.');
        }

        return $user;
    }

    public function create(User $user): User
    {
        $this->entityManager->persist($user);
        $user->setRoles($user->getRoles());

        return $this->update($user);
    }

    public function update(User $user): User
    {
        $this->entityManager->flush();

        return $user;
    }

    public function delete(string $email): void
    {
        $user = $this->getUserByEmail($email);
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    public function encodePassword(User $user): User
    {
        $password = $this->passwordEncoder->encodePassword($user, $user->getPassword());

        return $user->setPassword($password);
    }

    public function validate(User $user): array
    {
        $errors = $this->validator->validate($user);
        $response = [];

        foreach ($errors as $error) {
            $response[$error->getPropertyPath()] = $error->getMessage();
        }

        return $response;
    }
}
