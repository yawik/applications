<?php
/**
 * Cross Applicant Management
 *
 * @filesource
 * @copyright (c) 2013 Cross Solution (http://cross-solution.de)
 * @license   GPLv3
 */

/** Auth mapper mongodb */
namespace Applications\Mapper\MongoDb;

use Core\Mapper\MongoDb\AbstractMapper;
use Core\Model\ModelInterface;
use Applications\Model\Hydrator\ApplicationHydrator;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * User mapper factory
 */
class ApplicationMapper extends AbstractMapper
{

    public function fetchByJobid($jobId)
    {
        $query = array('jobId' => (string) $jobId);
        $cursor = $this->_collection->find($query);
        return $this->_createCollectionFromResult($cursor);    
    }
    
   
}