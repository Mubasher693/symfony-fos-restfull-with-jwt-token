<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * @param $id
     * @param $username
     * @return int|mixed|string|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByUserAndPostId($id, $username)
    {
        return $this->createQueryBuilder('ps')
            ->innerJoin('ps.user', 'usr')
            ->where('usr.username = :username')
            ->andWhere('ps.id = :id')
            ->setParameter('id', $id)
            ->setParameter('username',$username)
            ->getQuery()->getOneOrNullResult();
    }


    /**
     * @param $id
     * @param $username
     * @return int|mixed[]|string
     */
    public function findAllByUserAndPostId($username)
    {
        return $this->createQueryBuilder('ps')
            ->innerJoin('ps.user', 'usr')
            ->where('usr.username = :username')
            ->setParameter('username',$username)
            ->getQuery()->getResult();
    }

}
