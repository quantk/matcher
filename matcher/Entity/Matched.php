<?php
/**
 * Created by PhpStorm.
 * User: Alexander <kladoas@ite-ng.ru>
 * Date: 28.11.17
 * Time: 9:28
 */

namespace Artifly\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * Class Matched
 * @ORM\Entity()
 * @ORM\Table(name="matched",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="unq_match", columns={"inner_id", "inner_class"})
 *      }
 * )
 */
class Matched
{
    use SoftDeleteableEntity;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $id;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    private $innerId;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $innerClass;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    private $outerId;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $outerClass;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $outerSystemId;

    /**
     * @return int
     */
    public function getInnerId()
    {
        return $this->innerId;
    }

    /**
     * @param int $innerId
     *
     * @return Matched
     */
    public function setInnerId($innerId)
    {
        $this->innerId = $innerId;

        return $this;
    }

    /**
     * @return string
     */
    public function getInnerClass()
    {
        return $this->innerClass;
    }

    /**
     * @param string $innerClass
     *
     * @return Matched
     */
    public function setInnerClass($innerClass)
    {
        $this->innerClass = $innerClass;

        return $this;
    }

    /**
     * @return int
     */
    public function getOuterId()
    {
        return $this->outerId;
    }

    /**
     * @param int $outerId
     *
     * @return Matched
     */
    public function setOuterId($outerId)
    {
        $this->outerId = $outerId;

        return $this;
    }

    /**
     * @return string
     */
    public function getOuterClass()
    {
        return $this->outerClass;
    }

    /**
     * @param string $outerClass
     *
     * @return Matched
     */
    public function setOuterClass($outerClass)
    {
        $this->outerClass = $outerClass;

        return $this;
    }

    /**
     * @return string
     */
    public function getOuterSystemId()
    {
        return $this->outerSystemId;
    }

    /**
     * @param string $outerSystemId
     *
     * @return Matched
     */
    public function setOuterSystemId($outerSystemId)
    {
        $this->outerSystemId = $outerSystemId;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}