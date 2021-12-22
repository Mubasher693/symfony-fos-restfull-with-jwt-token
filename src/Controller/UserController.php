<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/api", name="api_")
 */
class UserController extends AbstractFOSRestController
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \Doctrine\ORM\EntityRepository|ObjectRepository
     */
    private $objectRepository;

    /**
     * @var JWTTokenManagerInterface
     */
    private $JWTManager;

    public function __construct(EntityManagerInterface $entityManager, JWTTokenManagerInterface $JWTManager)
    {
        $this->entityManager = $entityManager;
        $this->objectRepository = $this->entityManager->getRepository(User::class);
        $this->JWTManager = $JWTManager;
    }

    /**
     * @Rest\Get("/user/{id}", name="user_index", requirements={"id"="\d+"})
     */
    public function index(Request $request, int $id=null): Response
    {
        try {
            $jwtPayload = null;
            $users      = null;
            list($jwtPayload, $isAdmin) = $this->checkRole($request, $jwtPayload);
            if ($id) {
                $users = $this->objectRepository->findBy(['id' => $id, 'username' => $jwtPayload->username]);
            } else if ($isAdmin) {
                $users = $this->objectRepository->findAll();
            }
            if (!$users) {
                return $this->json('No user found or You dont have right to access this URL', 404);
            }
            $data = [];
            foreach ($users as $user) {
                $data[] = [
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'role' => $user->getRole()->getId(),
                    'roleTitle' => $user->getRole()->getName(),
                ];
            }
            return $this->json($data);
        } catch (\Exception $e){
            return $this->json($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Rest\Post("/register", name="user_new")
     */
    public function new(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        try {
            $username = $request->get('username');
            $password = $request->get('password');
            $email = $request->get('email');
            $role = $request->get('role');
            if (empty($username) || empty($password) || empty($email)) {
                return $this->json("Invalid Username or Password or Email");
            }
            $user = new user();
            $role = $this->entityManager->getRepository(Role::class)->findOneBy(array('id' => $role));
            $user->setRole($role);
            $user->setUsername($username);
            $user->setPassword($encoder->encodePassword($user, $password));
            $user->setEmail($email);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            return $this->json('Created new user successfully with id ' . $user->getId());
        } catch (\Exception $e){
            return $this->json($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Rest\Put("/user/{id}", name="user_edit")
     */
    public function edit(Request $request, int $id): Response
    {
        try {
            $user = $this->objectRepository->findOneBy(array('id' => $id));
            if (!$user) {
                return $this->json('No user found for id' . $id, 404);
            }
            $user->setRole($request->request->get('role'));
            $user->setUsername($request->request->get('username'));
            $user->setEmail($request->request->get('email'));
            $this->entityManager->flush();

            $data = [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'description' => $user->getDescription(),
            ];

            return $this->json($data);
        } catch (\Exception $e){
            return $this->json($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Rest\Delete("/user/{id}", name="user_delete")
     */
    public function delete(Request $request, int $id): Response
    {
        try {
            $user = $this->objectRepository->findOneBy(array('id' => $id));
            if (!$user) {
                return $this->json('No user found for id' . $id, 404);
            }
            $this->entityManager->remove($user);
            $this->entityManager->flush();
            return $this->json('Deleted a user successfully with id ' . $id);
        } catch (\Exception $e){
            return $this->json($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Rest\Post("/login_check", name="api_login_check")
     */
    public function getTokenUser(UserInterface $user): JsonResponse
    {
        try {
            return new JsonResponse(['token' => $this->JWTManager->create($user)]);
        } catch (\Exception $e){
            return $this->json($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param Request $request
     * @param $jwtPayload
     * @return array
     */
    private function checkRole(Request $request, $jwtPayload): array
    {
        $isAdmin = false;
        if ($request->headers->has('Authorization')
            && 0 === strpos($request->headers->get('Authorization'), 'Bearer ')) {
            $token = substr($request->headers->get('Authorization'), strpos($request->headers->get('Authorization'), " ") + 1);
            $tokenParts = explode(".", $token);
            $tokenPayload = base64_decode($tokenParts[1]);
            $jwtPayload = json_decode($tokenPayload);
            if (!empty($jwtPayload) && !empty($jwtPayload->roles) && in_array('ADMIN', $jwtPayload->roles)) {
                $isAdmin = true;
            }
        }
        return array($jwtPayload, $isAdmin);
    }

}
