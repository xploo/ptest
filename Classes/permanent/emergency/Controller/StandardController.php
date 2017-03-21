<?php

namespace permanent\emergency\Controller;


/*
 * emergency pharmacies Neos-Nodetype controller
 * author: Damian Bücker
 * date: 21.03.2016
 * company: permanent. Wirtschaftsförderung GmbH & Co KG 
 *  
 */

use TYPO3\Flow\Annotations as Flow;
use permanent\emergency\Domain\Repository\EmergencyPharmaciesRepository;
use permanent\emergency\Domain\Model\EmergencyPharmacies;
use permanent\hosts\Domain\Model\Hosts;
use permanent\hosts\Domain\Repository\HostsRepository;

class StandardController extends \TYPO3\Flow\Mvc\Controller\ActionController {

    /**
     * @Flow\Inject
     * @var EmergencyPharmaciesRepository
     */
    protected $repository;

    /**
     * @Flow\Inject
     * @var HostsRepository
     */
    protected $hostsRepository;

    const SERVICE_URL = 'http://www.apotheken.de/thirdparty.php';

    /**
     * assigns view with host data from backend inspector
     * 
     * @return void
     */
    public function indexAction($addressData = null)
    {
	if ($this->hostsRepository->getHost() != false)
	{
	    $hostId = $this->hostsRepository->getHost()->getHostId();
	}
	else
	{
	    $hostId = 1001;
	}
	//$host = $this->hostsRepository->getHost($hostId);
	if ($this->request->getInternalArgument('__useAddress') == true)
	{
	    if ($this->hostsRepository->getHost() != false)
	    {
		$addressData[0]['name'] = $this->hostsRepository->getHost()->getPharmacyname();
		$addressData[0]['street'] = $this->hostsRepository->getHost()->getPharmacyStreet();
		$addressData[0]['zip'] = $this->hostsRepository->getHost()->getPharmacyZip();
		$addressData[0]['city'] = $this->hostsRepository->getHost()->getPharmacyCity();
	    }
	}
	else
	{
//	$addressData[0]['lat'] = $this->request->getInternalArgument('__lat');
//	$addressData[0]['lon'] = $this->request->getInternalArgument('__lon');
	    $addressData[0]['name'] = $this->request->getInternalArgument('__name');
	    $addressData[0]['street'] = $this->request->getInternalArgument('__street');
	    $addressData[0]['zip'] = $this->request->getInternalArgument('__zip');
	    $addressData[0]['city'] = $this->request->getInternalArgument('__city');
	}
	$latLonResult = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($addressData[0]['street'] . '+' . $addressData[0]['zip'] . '+' . $addressData[0]['city']).'&key=AIzaSyBvuZY8e7Z7yhm9ShpJoFdD7N3zR8jVBoA');

	$resultSet = json_decode($latLonResult);
	$addressData[0]['lat'] = $resultSet->results[0]->geometry->location->lat;
	$addressData[0]['lon'] = $resultSet->results[0]->geometry->location->lng;

	$minResult = $this->request->getInternalArgument('__minResult');
	$radius = $this->request->getInternalArgument('__radius');

	$this->view->setPartialRootPath($this->request->getInternalArgument('__partialRootPath'));
	$this->view->assign('gridProperties', $this->request->getInternalArgument('__gridProperties'));
	$this->view->assign('api_key', 'AIzaSyDcvhZjF0kXn25iE_VHTH7ym6cQXa86uLw');
	$this->view->assign('weekday', date('l'));
	$this->view->assign('datum', date('d. F'));
	$this->view->assign('radius', $radius);
	$this->view->assign('addressData', $addressData);
	$this->view->assign('minResult', $minResult);
    }

    /*
     * returns xml content from url 
     */

    private function _getXmlResponse($resourceUrl, $noCache = FALSE)
    {
	$urlContent = $this->_loadUrlContent($resourceUrl, $noCache);
	$xml = simplexml_load_string($urlContent);

	return $xml;
    }

    /*  # do not use php5's simplexml_load_string because result object
      # is not unserializable after it is serialized
      $u = new XML_Unserializer(array(
      XML_UNSERIALIZER_OPTION_ENCODING_SOURCE => 'ISO-8859-1',
      XML_UNSERIALIZER_OPTION_COMPLEXTYPE => 'object',
      XML_UNSERIALIZER_OPTION_TAG_AS_CLASSNAME => FALSE,
      XML_UNSERIALIZER_OPTION_ATTRIBUTES_PARSE => TRUE,
      XML_UNSERIALIZER_OPTION_ATTRIBUTES_PREPEND => '__'
      ));

      if (pear::isError($r = $u->unserialize($urlContent, FALSE)))
      {
      if($noCache === TRUE)
      {
      trigger_error(sprintf('%s->%s(): failed to parse resource xml %s into object: %s', __CLASS__, __FUNCTION__, $resourceUrl, $r->getMessage()), E_USER_NOTICE);
      return FALSE;
      }
      else
      return $this->_getXmlResponse($resourceUrl, TRUE);
      }

      $domObject = $u->getUnserializedData();

      return $domObject;
     * */

    /*
     * get content from resource url 
     */

    private function _loadUrlContent($resourceUrl, $noCache = FALSE)
    {
	/*  $cacheFile = application::get_tmpfs_dir('apotheken_deCache') . '/' . md5($resourceUrl) . '.xml';

	  # try to read cache
	  if (!$noCache)
	  if ($this->cacheTime)
	  if (@filemtime($cacheFile) > (time() - $this->cacheTime))
	  return file_get_contents($cacheFile);
	 */
	# query apotheken.de and save to cache
	$curlHandle = curl_init();
	curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($curlHandle, CURLOPT_HEADER, FALSE);
	curl_setopt($curlHandle, CURLOPT_URL, $resourceUrl);
	curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 3);
	$response = curl_exec($curlHandle);
	//echo $response;
	curl_close($curlHandle);

	/*
	  if ($response === FALSE)
	  trigger_error(sprintf('%s->%s(): failed to load resource %s', __CLASS__, __FUNCTION__, $resourceUrl), E_USER_NOTICE);
	  elseif ($this->cacheTime)
	  file_put_contents($cacheFile, $response . chr(10) . '<!-- ' . $resourceUrl . ' -->');
	 */

	return $response;
    }

    /**
     * Get latitude, longitude and city information from specific zip code
     * 
     * @param \permanent\emergency\Domain\Model\Plz $plz
     * @return void
     */
    public function getZipContent($plz)
    {
	$test = $this->repository->getLatLonFromZip($plz);
	foreach ($test as $t)
	{
	    $latLon[0]['lat'] = $t->getLat();
	    $latLon[0]['lon'] = $t->getLon();
	    $latLon[0]['ort'] = $t->getOrt();
	    return $latLon[0];
	}
    }

    public static function formatDate($ts)
    {
	
	  
	$dayOfTimeStamp = floor($ts / 86400);
	$today = floor(time() / 86400);
	if ($dayOfTimeStamp == $today)
	    return strftime('heute %H:%M', $ts);
	elseif ($dayOfTimeStamp == $today + 1)
	    return strftime('morgen %H:%M', $ts);
	else
	    return strftime('%w %d.%m. %H:%M', $ts);
    }

    /*
     * call apotheken.de api with specific arguments
     *
     * returns ermergency paharmacies data
     */

    public function getPharmaciesAction($datetoget, $lat, $lon, $radius, $zip, $minResult)
    {
	$radius *= 1000;

	$wgsx = number_format(($lon * 60), 6, '.', '');
	$wgsy = number_format(($lat * 60), 6, '.', '');

	if ($datetoget != 0)
	{
	    $newdate = strtotime("+" . $datetoget . " day");
	    $date = date("d.m.Y", $newdate);
	}
	else
	{
	    $date = date('d.m.Y');
	}

	$result = $this->_getXmlResponse(self::SERVICE_URL . '?refid=202880&todo=search&typ=4&ndate=' . $date . '&dist=1&order=dist&radius=' . $radius . '&rekursiv=1&wgsx=' . $wgsx . '&wgsy=' . $wgsy . '');

	if (isset($result->body->pharmacy))
	{
	    // if we have only one result, extend the radius by 5km
	    //@TODO: Die 5 durch das InternalArgument tauschen, damit man das im Backend einstellen kann. 
	    if (!is_array($result->body->pharmacy) && $radius < 100000 && $result->header->amount <= $minResult)
	    {
		return $this->getPharmaciesAction($datetoget, $lat, $lon, ($radius / 1000) + 5, $zip, $minResult);
	    }
	}

	$i = 0;
	foreach ($result->body->pharmacy as $pharmacy)
	{
	    sscanf(($pharmacy->emergencies->Emergency['dateBegin']), '%u.%u.%u', $day, $month, $year);
	    sscanf(($pharmacy->emergencies->Emergency['timeBegin']), '%u:%u', $hour, $minute);
	    $pharmacy->__start = mktime($hour, $minute, 0, $month, $day, $year);

	    sscanf(($pharmacy->emergencies->Emergency['dateEnd']), '%u.%u.%u', $day, $month, $year);
	    sscanf(($pharmacy->emergencies->Emergency['timeEnd']), '%u:%u', $hour, $minute);
	    $pharmacy->__end = mktime($hour, $minute, 0, $month, $day, $year);

	    $pharmacyNodes[$i]['start'] = $this->formatDate((string) $pharmacy->__start);
	    $pharmacyNodes[$i]['end'] = $this->formatDate((string) $pharmacy->__end);
	    $pharmacyNodes[$i]['name'] = (string) $pharmacy->name;
	    $pharmacyNodes[$i]['zip'] = (string) $pharmacy->zip;
	    $pharmacyNodes[$i]['city'] = (string) $pharmacy->city;
	    $pharmacyNodes[$i]['district'] = (string) $pharmacy->district;
	    $pharmacyNodes[$i]['street'] = (string) $pharmacy->street;
	    $pharmacyNodes[$i]['phone1'] = (string) $pharmacy->phone1;
	    $pharmacyNodes[$i]['phone2'] = (string) $pharmacy->phone2;
	    $pharmacyNodes[$i]['fax1'] = (string) $pharmacy->fax1;
	    $pharmacyNodes[$i]['email'] = (string) $pharmacy->email;
	    $pharmacyNodes[$i]['homepage'] = (string) $pharmacy->homepage;
	    $pharmacyNodes[$i]['lat'] = (floatval($pharmacy->wgsy) / 60);
	    $pharmacyNodes[$i]['lon'] = (floatval($pharmacy->wgsx) / 60);
	    $pharmacyNodes[$i]['distance'] = (string) $pharmacy->distance;
	    $pharmacyNodes[$i]['radius'] = (string) intval($result->header->radius / 1000);
	    $i++;
	}

	return $pharmacyNodes;
    }

    /**
     * @param string $action
     */
    public function getAjaxDataAction($action)
    {

	$result = array();
	$pharmacies = $this->getPharmaciesAction($_POST['datetoget'], $_POST['lat'], $_POST['lon'], $_POST['radius'], $_POST['zip'], $_POST['minResult']);

	if ($pharmacies != null)
	{
	    $result['status'] = 'OK';
	    $result['data'] = $pharmacies;
	}
	else
	{
	    $result['status'] = 'Error';
	    $result['message'] = '<div class="warning">Es stehen aktuell leider keine Notdienstdaten zur Verfügung.</div>';
	}
	return json_encode($result);
    }

    /**
     * @param string $action
     */
    public function getLatLonFromZipAction($action)
    {

	$result = array();
	$latLon = $this->getZipContent($_POST['zip']);

	if ($latLon != null)
	{
	    $result['status'] = 'OK';
	    $result['data'] = $latLon;
	}
	else
	{
	    $result['status'] = 'Error';
	    $result['message'] = '<div class="warning">Es stehen aktuell leider keine Notdienstdaten zur Verfügung.</div>';
	}
	return json_encode($result);
    }

}
