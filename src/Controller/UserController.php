<?php

namespace App\Controller;

use App\Service\UserService;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
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

        return $this->buildResponse();
    }

    #[Route('/user/{id}', methods: ['DELETE'])]
    public function delete(UserService $userService, $id): JsonResponse
    {
        try {
            $userService->delete($id);

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

        return $this->buildResponse(data: $user);
    }

    #[Route('/user/{id}', methods: ['PUT'])]
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $this->userService->update($request, $id);
        } catch (\TypeError $e) {
            return $this->buildResponse('failed', 'Передан неправильный id', statusCode: 400);
        } catch (ValidatorException $e) {
            return $this->buildResponse('failed', json_decode($e->getMessage(), true), statusCode: 422);
        } catch (EntityNotFoundException $e) {
            return $this->buildResponse('failed', 'Пользователь c id: '.$id.' не найден', statusCode: 404);
        }

        return $this->buildResponse();
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
        ]);
    }
}
