<?php
namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $userRepository,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {}

    public function create(Request $request): void
    {
        $user = $this->deserialize($request);

        $plaintextPassword = $user->getPassword();
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );
        $user->setPassword($hashedPassword);

        $validationResult = $this->validate($user);
        if ($validationResult) {
            throw new ValidatorException(json_encode($validationResult));
        }

        // Проверка на уникальность email
        $existingUser = $this->userRepository->findOneBy(['email' => $user->getEmail()]);
        if ($existingUser) {
            throw new BadRequestHttpException(json_encode('Такой Email существует'));
        }

        $this->em->persist($user);
        $this->em->flush();
    }

    public function get(int $id): User
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            throw new EntityNotFoundException();
        }

        return $user;
    }

    public function update(Request $request, $id): void
    {
        $userData = json_decode($request->getContent(), true);
        $user = $this->userRepository->find($id);
        if (!$user) {
            throw new EntityNotFoundException();
        }
        $user->setEmail($userData['email']);
        $user->setPassword($userData['password']);
        $user->setName($userData['name']);

        $validationResult = $this->validate($user);
        if ($validationResult) {
            throw new ValidatorException(json_encode($validationResult));
        }

        $this->em->flush();
    }

    public function delete(int $id): void
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            throw new EntityNotFoundException();
        }
        $this->em->remove($user);
        $this->em->flush();
    }

    private function deserialize(Request $request): User
    {
        return $this->serializer->deserialize($request->getContent(), User::class, 'json', [
            AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false,
        ]);
    }

    private function validate(object $data): array
    {
        $errors = $this->validator->validate($data);
        $formattedErrors = [];
        foreach ($errors as $error) {
            $formattedErrors[$error->getPropertyPath()] = $error->getMessage();
        }

        return $formattedErrors;
    }
}
