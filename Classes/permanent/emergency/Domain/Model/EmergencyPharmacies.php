<?php
namespace permanent\emergency\Domain\Model;
/*
 * emergency pharmacies Neos-Nodetype model
 * author: Damian Bücker
 * date: 21.03.2016
 * company: permanent. Wirtschaftsförderung GmbH & Co KG 
 *  
 */



/*
 * This file is part of the permanent.emergency package.
 */

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class EmergencyPharmacies {

    /**
     * @Flow\Validate(type="NotEmpty")
     * @Flow\Validate(type="StringLength", options={ "minimum"=3, "maximum"=80 })
     * @ORM\Column(length=80)
     * @var string
     */
    protected $loc_id;

    /**
     * @var string
     */
    protected $plz;

    /**
     * @var string
     */
    protected $lon;

    /**
     * @var string
     */
    protected $lat;

    /**
     * @var string
     */
    protected $Ort;

    /**
     * @return string
     */
    function getLoc_id() {
        return $this->loc_id;
    }

    /**
     * @return string
     */
    function getPlz() {
        return $this->plz;
    }

    /**
     * @return string
     */
    function getLon() {
        return $this->lon;
    }

    /**
     * @return string
     */
    function getLat() {
        return $this->lat;
    }

    /**
     * @return string
     */
    function getOrt() {
        return $this->Ort;
    }

    /**
     * @return string
     */
    function setLoc_id($loc_id) {
        $this->loc_id = $loc_id;
    }

    /**
     * @return string
     */
    function setPlz($plz) {
        $this->plz = $plz;
    }

    /**
     * @return string
     */
    function setLon($lon) {
        $this->lon = $lon;
    }

    function setLat($lat) {
        $this->lat = $lat;
    }

    /**
     * @return string
     */
    function setOrt($Ort) {
        $this->Ort = $Ort;
    }

}
