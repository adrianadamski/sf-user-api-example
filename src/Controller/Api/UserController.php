<?php declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/user")
 */
class UserController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @Route("/{email}", methods={"GET"})
     */
    public function show(string $email): Response
    {
        $user = $this->userService->getUserByEmail($email);

        return new JsonResponse([
            'email' => $user->getEmail(),
            'created_at' => $user->getCreatedAt()->format(DATE_ISO8601),
            'roles' => $user->getRoles(),
        ]);
    }

    /**
     * @Route("/create", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        $user = new User();
        $user
            ->setEmail($request->get('email', ''))
            ->setPassword($request->get('password', ''));

        $errors = $this->userService->validate($user);

        if (empty($errors)) {
            $this->userService->encodePassword($user);
            $this->userService->create($user);

            return new JsonResponse(['Ok']);
        }

        return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/{email}", methods={"PUT", "PATCH"})
     */
    public function update(Request $request, string $email): Response
    {
        $user = $this->userService->getUserByEmail($email);
        $user
            ->setEmail($request->get('email', ''))
            ->setPassword($request->get('password', ''));

        $errors = $this->userService->validate($user);

        if (empty($errors)) {
            $this->userService->encodePassword($user);
            $this->userService->update($user);

            return new JsonResponse(['Ok']);
        }

        return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/{email}", methods={"DELETE"})
     */
    public function delete(string $email): Response
    {
        $this->userService->delete($email);

        return new JsonResponse(['Ok']);
    }
}
