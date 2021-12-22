<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api", name="api_")
 */
class FeedbackController extends AbstractFOSRestController
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
        $this->objectRepository = $this->entityManager->getRepository(Feedback::class);
    }

    /**
     * @Rest\Get("/feedback/{id}", name="feedback_index", requirements={"id"="\d+"})
     */
    public function index(int $id=null): Response {
        if($id !== null){
            $feedbacks = $this->objectRepository->findBy(['post'=>$id]);
        } else {
            $feedbacks = $this->objectRepository->findAll();
        }
        $data = [];
        foreach ($feedbacks as $feedback) {
            $data[] = [
                'id' => $feedback->getId(),
                'description' => $feedback->getDescription(),
                'createdAt' => $feedback->getCreatedAt(),
                'updatedAt' => $feedback->getUpdatedAt(),
            ];
        }
        return $this->json($data);
    }

    /**
     * @Rest\Post("/feedback", name="feedback_new")
     */
    public function new(Request $request): Response
    {
        $jwtPayload = null;
        $users      = null;
        list($jwtPayload, $isAdmin) = $this->checkRole($request, $jwtPayload);
        $user          = !empty($request->request->get('logged_in_user')) ?
            $this->entityManager->getRepository(User::class)->findOneBy(array('username' => $jwtPayload->username)) : null;
        $post          = !empty($request->request->get('post_id')) ? $this->entityManager->getRepository(Post::class)->findOneBy(array('id' => $request->request->get('post_id'))) : null;
        if (!$post) {
            return $this->json('No post found', 404);
        }
        $feedback = new feedback();
        if ($user !== null) {
            $feedback->setUser($user);
        }
        $feedback->setPost($post);
        $feedback->setDescription($request->request->get('description'));
        $this->entityManager->persist($feedback);
        $this->entityManager->flush();
        return $this->json('Created new feedback successfully with id ' . $feedback->getId());
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
