<?php

namespace Applications\Repository\EntityBuilder;

use Core\Repository\EntityBuilder\AggregateBuilder;
use Core\Repository\RepositoryAwareInterface;
use Core\Repository\Mapper\MapperAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Core\Entity\RelationEntity;
use Core\Entity\RelationCollection;
use Core\Entity\EntityInterface;

class ApplicationBuilder extends AggregateBuilder implements RepositoryAwareInterface, MapperAwareInterface
{
    protected $repositories;
    protected $mappers;
    
    public function setRepositoryManager(ServiceLocatorInterface $repositoryManager)
    {
        $this->repositories = $repositoryManager;
        return $this;
    }
    
    public function getRepositoryManager()
    {
        return $this->repositories;
    }
    
    public function setMapperManager(ServiceLocatorInterface $mapperManager)
    {
        $this->mappers = $mapperManager;
        return $this;
    }
    
    public function getMapperManager()
    {
        return $this->mappers;
    }
    
    public function build($data = array())
    {
        
        $entity = parent::build($data);
        
        if (!$entity->job) {
            $job = new RelationEntity(
                array($this->repositories->get('job'), 'find'),
                array($entity->jobId)
            );
            $entity->injectJob($job);
        }
        
        if (!$entity->user) {
            $userId = $entity->getUserId();
            if ($userId) {
                $user = new RelationEntity(
                    array($this->repositories->get('user'), 'find'),
                    array($userId)
                );
                $entity->injectUser($user);
            }
        }
        
        $attachments = isset($data['refs']['applications-files'])
            ? new RelationCollection(
                array($this->mappers->get('Applications/Files'), 'fetchByIds'),
                array($data['refs']['applications-files'])
              )
            : $this->getCollection();
        $entity->injectAttachments($attachments);
        
        return $entity;
    }
    
    public function unbuild(EntityInterface $entity)
    {
        $data = parent::unbuild($entity);
        /* This is a hack to prevent the "image" property from beeing unset
         * We need to rework the whole repository -> builder -> mapper -> hydrator thing :(
         */
        if (isset($data['contact'])) {
            foreach ($data['contact'] as $prop => $val) {
                $data["contact.$prop"] = $val;
            }
            unset($data['contact']);
        }
        return $data;
    }
    
}