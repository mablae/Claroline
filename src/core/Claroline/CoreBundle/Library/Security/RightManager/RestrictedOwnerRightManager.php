<?php

namespace Claroline\CoreBundle\Library\Security\RightManager;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Claroline\CoreBundle\Exception\SecurityException;
use Claroline\CoreBundle\Entity\User;

class RestrictedOwnerRightManager implements RightManagerInterface
{
    /** @var RightManagerInterface */
    private $baseManager;
    
    public function __construct($baseManager)
    {
        $this->baseManager = $baseManager;
    }    
    
    public function addRight($target, $subject, $rightMask)
    {
        if ($this->isOwningMask($rightMask))
        {
            $this->assertThereIsNoOtherOwner($target, $subject);
            $this->assertSubjectIsAUser($subject);
            $this->assertTargetIsNotAClass($target);
        }
        
        return $this->baseManager->addRight($target, $subject, $rightMask);
    }
    
    private function isOwningMask($mask)
    {
        return MaskBuilder::MASK_OWNER == (MaskBuilder::MASK_OWNER & $mask);
    }
    
    private function assertThereIsNoOtherOwner($target, $subject)
    {
        $currentOwners = $this->getUsersWithRight($target, MaskBuilder::MASK_OWNER);
        
        foreach ($currentOwners as $owner)
        {
            if ($owner != $subject)
            {
                throw new SecurityException(
                    'Attempted to set an owner on an object which already have one',
                    SecurityException::MULTIPLE_OWNERS_ATTEMPT
                );
            }
        }        
    }
    
    private function assertSubjectIsAUser($subject)
    {
        if (! $subject instanceof User)
        {
            throw new SecurityException(
                "Only users can be owner", 
                SecurityException::NOT_ALLOWED_OWNER_MASK
            );
        }   
    }
    
    private function assertTargetIsNotAClass($target)
    {
        if (is_string($target))
        {
            throw new SecurityException(
                "Classes are not supposed to be owned", 
                SecurityException::NOT_ALLOWED_OWNER_MASK
            );
        }       
    }
    
    public function setOwner($target, $subject)
    {
        $currentOwners = $this->getUsersWithRight($target, MaskBuilder::MASK_OWNER);
        
        foreach ($currentOwners as $owner)
        {
            $this->removeRight($target, $owner, MaskBuilder::MASK_OWNER);            
        }
        
        return $this->baseManager->addRight($target, $subject, MaskBuilder::MASK_OWNER);
    }

    public function deleteRights($target)
    {
        return $this->baseManager->deleteRights($target);
    }

    public function getUsersWithRight($target, $rightMask)
    {
        return $this->baseManager->getUsersWithRight($target, $rightMask);
    }

    public function hasRight($target, $subject, $rightMask)
    {
        return $this->baseManager->hasRight($target, $subject, $rightMask);
    }

    public function removeAllRights($target, $subject)
    {
        return $this->baseManager->removeAllRights($target, $subject);
    }

    public function removeRight($target, $subject, $rightMask)
    {
        return $this->baseManager->removeRight($target, $subject, $rightMask);
    }

    public function setRight($target, $subject, $rightMask)
    {
        return $this->baseManager->setRight($target, $subject, $rightMask);
    }   
}