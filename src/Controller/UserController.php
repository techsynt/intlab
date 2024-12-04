<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Exception\ValidatorException;

#[Route('/api')]
class UserController extends AbstractController
{
    public function __construct(private readonly UserService $userService) {}

    #[Route('/user', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $this->userService->create($request);
        } catch (BadRequestHttpException $e) {
            return $this->buildResponse(
                'failed',
                json_decode($e->getMessage(), true),
                null,
                409
            );
        } catch (ValidatorException $e) {
            return $this->buildResponse(
                'failed',
                json_decode($e->getMessage(), true),
                null,
                400
            );
        }

        return $this->buildResponse(data: ['email' => $request->getPayload()->get('email'),
            'message' => 'Пользователь успешно создан', ], statusCode: 201);
    }

    #[Route('/user/{id}', methods: ['DELETE'])]
    public function delete($id): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Нет прав админа');
        } catch (AccessDeniedException $e) {
            return $this->json($e->getMessage());
        }

        try {
            $this->userService->delete($id);

            return $this->buildResponse(statusCode: 204);
        } catch (EntityNotFoundException $e) {
            return $this->buildResponse('failed', 'Пользователь не найден', statusCode: 404);
        } catch (\TypeError $e) {
            return $this->buildResponse('failed', 'Передан неправильный id', statusCode: 400);
        }
    }

    #[Route('/user/{id}', methods: ['GET'])]
    public function show(UserService $userService, $id): JsonResponse
    {
        try {
            $user = $userService->get($id);
        } catch (EntityNotFoundException $e) {
            return $this->buildResponse('failed', 'Пользователь не найден', statusCode: 404);
        } catch (\TypeError $e) {
            return $this->buildResponse('failed', 'Передан неправильный id', statusCode: 400);
        }

        return $this->buildResponse(data: $this->normalize($user));
    }

    #[Route('/user/{id}', methods: ['PUT'])]
    public function update(Request $request, $id, #[CurrentUser] ?User $user): JsonResponse
    {
        if ($user && ($user->getId() == $id || $this->isGranted('ROLE_ADMIN'))) {
            try {
                $this->userService->update($request, $id);
            } catch (\TypeError $e) {
                return $this->buildResponse('failed', 'Передан неправильный id', statusCode: 400);
            } catch (ValidatorException $e) {
                return $this->buildResponse('failed', json_decode($e->getMessage(), true), statusCode: 422);
            } catch (EntityNotFoundException $e) {
                return $this->buildResponse('failed', 'Пользователь c id: '.$id.' не найден', statusCode: 404);
            }

            return $this->buildResponse(data: ['message' => 'Пользователь успешно обновлен']);
        }

        throw new AccessDeniedException('Нет доступа к обновлению пользователя');
    }

    private function buildResponse(
        string $status = 'success',
        $errors = null,
        $data = null,
        int $statusCode = 200
    ): JsonResponse {
        if ('failed' === $status) {
            return new JsonResponse(
                [
                    'status' => $status,
                    'errors' => $errors,
                ],
                $statusCode
            );
        }

        return $this->json([
            'status' => $status,
            'data' => $data,
        ], status: $statusCode);
    }

    private function normalize(User $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
        ];
    }
}
