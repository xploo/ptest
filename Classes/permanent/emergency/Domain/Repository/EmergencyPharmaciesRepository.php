<?php
namespace permanent\emergency\Domain\Repository;

/*
 * emergency pharmacies Neos-Nodetype repository
 * author: Damian Bücker
 * date: 21.03.2016
 * company: permanent. Wirtschaftsförderung GmbH & Co KG 
 *  
 */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Repository;
use permanent\emergency\Domain\Model\EmergencyPharmacies;
use TYPO3\Flow\Persistence\QueryInterface;
use TYPO3\Flow\Persistence\QueryResultInterface;

/**
 * @Flow\Scope("singleton")
 */
class EmergencyPharmaciesRepository extends Repository
{

    // add customized methods here
    /**
     * Gets emergency model object by a specific zip code
     * 
     * @param Plz $plz A plzbla
     * @return EmergencyPharmacies 
     */
    public function getLatLonFromZip($plz)
    {
        $query = $this->createQuery();
        return
                $query->matching(
                        $query->equals('plz', $plz)
                )
                ->execute();                
    }

}
