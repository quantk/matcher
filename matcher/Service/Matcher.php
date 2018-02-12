<?php
/**
 * Created by PhpStorm.
 * User: Alexander <kladoas@ite-ng.ru>
 * Date: 28.11.17
 * Time: 9:50
 */

namespace Artifly\Service;


use Artifly\Entity\Matched;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class Matcher
 *
 * @package AppBundle\Service
 */
class Matcher
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var array
     */
    private $matches = [];

    /**
     * @var array
     */
    private $persistedMatches = [];

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    private $matchedRepository;

    /**
     * Matcher constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->matchedRepository = $em->getRepository(Matched::class);
    }

    /**
     * @param $classOrObject
     *
     * @return string
     * @throws \Exception
     */
    private function getRealClass($classOrObject)
    {
        if (is_object($classOrObject)) {
            return ClassUtils::getRealClass(get_class($classOrObject));
        } elseif (is_string($classOrObject)) {
            return ClassUtils::getRealClass($classOrObject);
        } else {
            throw new \Exception('Wrong argument type. Argument of method getRealClass should be object or string class.');
        }
    }

    /**
     * @param $entity
     * @param $outerSystem
     * @param $outerClass
     * @param $outerId
     */
    public function addMatchToQueue($entity, $outerSystem, $outerClass, $outerId)
    {
        if (!$entity || !$outerSystem || !$outerClass || !$outerId) {
            throw new \Exception('Системная ошибка в Matcher::addMatchToQueue');
        }
        $this->matches[] = [
            'entity' => $entity,
            'externalSystem' => $outerSystem,
            'externalClass' => $outerClass,
            'externalId' => $outerId
        ];
    }

    /**
     *
     */
    public function executeMatches()
    {
        try {
            $this->em->flush();
            foreach ($this->matches as $match) {
                $this->createMatch(
                    $match['entity'],
                    $match['externalSystem'],
                    $match['externalClass'],
                    $match['externalId']
                );
            }
            $this->em->flush();
        } catch (\Exception $e) {
            $this->fallbackQueue();
        }
    }

    /**
     * @param $innerEntity
     * @param $outerSystem
     * @param $outerClass
     * @param $outerId
     */
    public function createMatch(
        $innerEntity,
        $outerSystem,
        $outerClass,
        $outerId
    )
    {
        $matched = $this->matchOuter($innerEntity->getId(), $this->getRealClass($innerEntity), $outerSystem);
        if ($this->isDeleted($innerEntity) && $matched) {
            $matched->setDeletedAt($innerEntity->getDeletedAt());
        }
        if (!$matched && !$this->isDeleted($innerEntity)) {
            /**
             * flush for get id from new entity
             */
            $matched = new Matched();
            $matched->setInnerId($innerEntity->getId());
            $matched->setInnerClass($this->getRealClass($innerEntity));
            $matched->setOuterSystemId($outerSystem);
            $matched->setOuterClass($outerClass);
            $matched->setOuterId($outerId);

            $this->em->persist($matched);
            $this->persistedMatches[] = $matched;
        }
    }

    /**
     * @param $innerEntity
     * @param $outerSystem
     *
     * @return bool
     */
    public function checkMatch(
        $innerEntity,
        $outerSystem
    )
    {
        $matched = $this->matchOuter($innerEntity->getId(), $this->getRealClass($innerEntity), $outerSystem);
        return $matched instanceof Matched ? true : false;
    }

    /**
     * @param $outerSystem
     * @param $outerClass
     * @param $outerId
     *
     * @return null|object
     */
    public function matchInner($outerSystem, $outerClass, $outerId)
    {
        /* @var $matched Matched */
        $matched = $this->matchedRepository->findOneBy(
            [
                'outerSystemId' => $outerSystem,
                'outerClass'    => $outerClass,
                'outerId'       => $outerId,
                'deletedAt'     => null,
            ]
        );

        if (!$matched) {
            return null;
        }

        $innerRepository = $this->em->getRepository($matched->getInnerClass());
        $entity = $innerRepository->find($matched->getInnerId());
        return $entity;
    }

    /**
     * @param $innerId
     * @param $innerClass
     * @param $outerSystem
     *
     * @return Matched
     */
    public function matchOuter($innerId, $innerClass, $outerSystem)
    {
        /* @var $matched Matched */
        $matched = $this->matchedRepository->findOneBy(
            [
                'outerSystemId' => $outerSystem,
                'innerId'       => $innerId,
                'innerClass'    => $innerClass
            ]
        );

        return $matched;
    }

    /**
     *
     */
    private function fallbackQueue(): void
    {
        foreach ($this->matches as $match) {
            $this->hardRemoveEntity($match['entity']);
        }
        foreach ($this->persistedMatches as $persistedMatch) {
            $this->hardRemoveEntity($persistedMatch);
        }
        $this->em->flush();
    }

    /**
     * @param $entity
     *
     * @return bool
     */
    private function isSoftdeletableEntity($entity)
    {
        return method_exists($entity, 'setDeletedAt');
    }

    /**
     * @param $innerEntity
     *
     * @return bool
     */
    private function isDeleted($innerEntity): bool
    {
        return method_exists($innerEntity, 'isDeleted') && $innerEntity->isDeleted();
    }

    /**
     * @param $entity
     */
    private function hardRemoveEntity($entity): void
    {
        if ($this->isSoftdeletableEntity($entity)) {
            $entity->setDeletedAt(new \DateTime());
        }
        $this->em->remove($entity);
    }
}