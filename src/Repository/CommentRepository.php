<?php

declare(strict_types=1);

namespace Positron48\CommentExtension\Repository;

use Bolt\Storage\Query;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Positron48\CommentExtension\Entity\Comment;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[] findAll()
 * @method Comment[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    /**
     * @var Query
     */
    protected $query;

    public function __construct(ManagerRegistry $registry, Query $query)
    {
        $this->query = $query;
        parent::__construct($registry, Comment::class);
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('comment');
    }

    /**
     * @return int|mixed[]|string
     */
    public function findByContentId(int $contentId)
    {
        return $this->getByContentIdQuery($contentId)
            ->getArrayResult();
    }

    /**
     * @param int $contentId
     * @return \Doctrine\ORM\Query
     */
    public function getByContentIdQuery(int $contentId)
    {
        $qb = $this->getQueryBuilder();

        $query = $qb
            ->andWhere('content_id = :value')
            ->setParameter('value', $contentId);

        return $query
            ->getQuery();
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function getAllQuery()
    {
        $qb = $this->getQueryBuilder();

//        $qb = $qb
//            ->select(
//                [
//                    'comment',
//                    'comment.id',
//                    'comment.authorName',
//                    'comment.authorEmail',
//                    'comment.message',
//                    'comment.contentId',
//                    't.value',
//                    'c.contentType'
//                ]
//            )
//            ->join('Bolt\Entity\Content', 'c', Join::WITH, 'comment.contentId = c.id')
//            ->join('Bolt\Entity\Field', 'f', Join::WITH, 'comment.contentId = f.content')
//            ->join('Bolt\Entity\FieldTranslation', 't', Join::WITH, 'f.id = t.translatable and f.name = \'title\'')
//        ;

        return $qb->getQuery();
    }
}