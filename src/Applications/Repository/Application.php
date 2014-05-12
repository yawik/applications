<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013-2014 Cross Solution (http://cross-solution.de)
 * @license   AGPLv3
 */
namespace Applications\Repository;

use Core\Repository\AbstractRepository;
use Core\Entity\EntityInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\Parameters;
use Core\Paginator\Adapter\EntityList;
use Applications\Entity\ApplicationInterface;
use Doctrine\ODM\MongoDB\Events;
use Applications\Entity\Application as ApplicationEntity;
use Applications\Entity\CommentInterface;
use Zend\Stdlib\ArrayUtils;

/**
 * class for accessing applications
 * 
 * @package Applications
 */
class Application extends AbstractRepository
{   
    /**
     * Gets a pointer to an application
     * 
     * @param array $params
     */
    public function getPaginatorCursor($params)
    {
        return $this->getPaginationQueryBuilder($params)
                    ->getQuery()
                    ->execute();
    }
    
    /**
     * Gets a query builder to search for applications
     * 
     * @param array $params
     * @return unknown
     */
    protected function getPaginationQueryBuilder($params)
    {
        $filter = $this->getService('filterManager')->get('Applications/PaginationQuery');
        $qb = $filter->filter($params, $this->createQueryBuilder());
        
        return $qb;
    }
    
    /**
     * Gets a result list of applications
     * 
     * @param array $params
     * @return \Applications\Repository\PaginationList
     */
    public function getPaginationList($params)
    {
        $qb = $this->getPaginationQueryBuilder($params);
        $cursor = $qb->hydrate(false)
                     ->select('_id')
                     ->getQuery()
                     ->execute();
        
        $list = new PaginationList(array_keys(ArrayUtils::iteratorToArray($cursor)));
        return $list;
    }
    
    /**
     * Get unread applications
     *
     * @param unknown $job
     */
    public function getUnreadApplications($job) 
    {
        $auth=$this->getService('AuthenticationService');
        $qb=$this->createQueryBuilder()
                  ->field("readBy")->notIn($auth->getUser()->id)
                  ->field("job")->equals( new \MongoId($job->id));
        return $qb->getQuery()->execute();          
    }
    
    /**
     * Get comments of an applications
     * 
     * @param unknown $commentOrId
     * @return unknown|NULL
     */
    public function findComment($commentOrId)
    {
        if ($commentOrId instanceOf CommentInterface) {
            $commentOrId = $commentOrId->getId();
        }
        
        $application = $this->findOneBy(array('comments.id' => $commentOrId));
        foreach ($application->getComments() as $comment) {
            if ($comment->getId() == $commentOrId) {
                return $comment;
            }
        }
        return null;
            
    }
    
    /**
     * Gets social profiles of an application
     * 
     * @param String $profileId
     * @return unknown|NULL
     */
    public function findProfile($profileId)
    {
        $application = $this->findOneBy(array('profiles._id' => new \MongoId($profileId)));
        foreach ($application->getProfiles() as $profile) {
            if ($profile->getId() == $profileId) {
                return $profile;
            }
        }
        return null;
    }
    
    /**
     * @deprecated
     * @param unknown $jobId
     * @return unknown
     */
    public function fetchByJobId($jobId)
    {
        $collection = $this->getMapper('application')->fetch(
            array('jobId' => $jobId),
            array('cv'),
            true
        );
        return $collection;
    }
    
    public function fetchRecent($limit=5)
    {
        $collection = $this->getMapper('application')->fetchRecent($limit);
        return $collection;
    }
    
    /**
     * @deprecated
     * counts the number of applications of 
     */    
    public function countBy($userOrId, $onlyUnread=false)
    {
        if ($userOrId instanceOf \Auth\Entity\UserInterface) {
            $userOrId = $userOrId->getId();
        }
        $criteria = array('user' => $userOrId);
        if ($onlyUnread) {
            $criteria['readBy'] = array ('$ne' => $userOrId);
        }
        return $this->findBy($criteria)->count();
    }
    
    /**
     * Save an application
     * 
     * @param ApplicationInterface $application
     * @param string $resetModifiedDate
     */
    public function save(ApplicationInterface $application, $resetModifiedDate=true)
    {
        if ($resetModifiedDate) {
            $application->setDateModified('now');
        }
        $this->dm->persist($application);
        $this->dm->flush();
    }
    
    /**
     * delete an application
     * 
     * @param EntityInterface $entity
     * @return \Applications\Repository\Application
     */
    public function delete(EntityInterface $entity)
    {
        $this->dm->remove($entity);
        $this->dm->flush();
        return $this;
    }
    
     
}