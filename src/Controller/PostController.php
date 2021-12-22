<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Post;

/**
 * @Route("/api", name="api_")
 */
class PostController extends AbstractFOSRestController
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \Doctrine\ORM\EntityRepository|ObjectRepository
     */
    private $objectRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->objectRepository = $this->entityManager->getRepository(Post::class);
    }

    /**
     * @Rest\Get("/post/{type}", name="post_index", requirements={"type"="\d+"})
     */
    public function index(Request $request, int $type=null): Response {
        try {
            $jwtPayload = null;
            list($jwtPayload, $isWriter) = $this->checkRole($request, $jwtPayload);
            if ($type !== null && isset($jwtPayload->username)) {
                $posts = $this->objectRepository->findAllByUserAndPostId($jwtPayload->username);
            } else {
                $posts = $this->objectRepository->findAll();
            }
            $data = [];
            foreach ($posts as $post) {
                $data[] = [
                    'id' => $post->getId(),
                    'name' => $post->getTitle(),
                    'description' => $post->getDescription(),
                    'createdAt' => $post->getCreatedAt(),
                    'updatedAt' => $post->getUpdatedAt(),
                ];
            }
            return $this->json($data);
        } catch (\Exception $e){
            return $this->json($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Rest\Post("/post", name="post_new")
     */
    public function new(Request $request): Response
    {
        try {
            $jwtPayload = null;
            list($jwtPayload, $isWriter) = $this->checkRole($request, $jwtPayload);
            if (!$isWriter) {
                return $this->json('You dont have right to access this URL', 404);
            }
            $post = new post();
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $jwtPayload->username]);
            $post->setUser($user);
            $post->setTitle($request->request->get('title'));
            $post->setDescription($request->request->get('description'));
            $this->entityManager->persist($post);
            $this->entityManager->flush();
            return $this->json('Created new post successfully with id ' . $post->getId());
        } catch (\Exception $e){
            return $this->json($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Rest\Put("/post/{id}", name="post_edit", requirements={"id"="\d+"})
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function edit(Request $request, int $id): Response
    {
        try {
            $jwtPayload = null;
            list($jwtPayload, $isWriter) = $this->checkRole($request, $jwtPayload);
            if (!$isWriter || !isset($jwtPayload->username)) {
                return $this->json('You dont have right to access this URL', 404);
            }
            $post = $this->objectRepository->findByUserAndPostId($id, $jwtPayload->username);
            if (!$post) {
                return $this->json('No post found for id ' . $id, 404);
            }
            $post->setTitle($request->request->get('title'));
            $post->setDescription($request->request->get('description'));
            $this->entityManager->flush();
            $data = [
                'id' => $post->getId(),
                'name' => $post->getTitle(),
                'description' => $post->getDescription(),
                'createdAt' => $post->getCreatedAt(),
                'updatedAt' => $post->getUpdatedAt(),
            ];
            return $this->json($data);
        } catch (\Exception $e){
            return $this->json($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Rest\Delete("/post/{id}", name="post_delete", requirements={"id"="\d+"})
     */
    public function delete(Request $request, int $id): Response
    {
        try {
            $jwtPayload = null;
            list($jwtPayload, $isWriter) = $this->checkRole($request, $jwtPayload);
            if (!$isWriter || !isset($jwtPayload->username)) {
                return $this->json('You dont have right to access this URL', 404);
            }
            $post = $this->objectRepository->findByUserAndPostId($id, $jwtPayload->username);
            if (!$post) {
                return $this->json('No post found for id ' . $id, 404);
            }
            $this->entityManager->remove($post);
            $this->entityManager->flush();
            return $this->json('Deleted a post successfully with id ' . $id);
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
        $isWriter = false;
        if ($request->headers->has('Authorization')
            && 0 === strpos($request->headers->get('Authorization'), 'Bearer ')) {
            $token = substr($request->headers->get('Authorization'), strpos($request->headers->get('Authorization'), " ") + 1);
            $tokenParts = explode(".", $token);
            $tokenPayload = base64_decode($tokenParts[1]);
            $jwtPayload = json_decode($tokenPayload);
            if (!empty($jwtPayload) && !empty($jwtPayload->roles) && in_array('WRITER', $jwtPayload->roles)) {
                $isWriter = true;
            }
        }
        return array($jwtPayload, $isWriter);
    }
}
