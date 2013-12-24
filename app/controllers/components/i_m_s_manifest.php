<?php
class IMSManifestComponent extends Object {
	
	var $scoURL = 'http://moodleserver/proxy/sqlexplorer/sco';
	
	var $__imsmanifest = "";
	var $__resources = "";
	var $__jsonp = false;
	var $__fileName = "default_filename.zip";
	var $__zip;
	
	function initialize(&$controller, $settings = array()) {
		// saving the controller reference for later use
		$this->controller =& $controller;
	}
	
	function generateIMSManifest($shortName, $title, $questions, $jsonp = false){
		App::import('Vendor', 'createZip', array('file' => 'createZip.php'));
		define('SCORM', APP . 'libs' . DS . 'scorm' . DS);
		$this->__fileName = "scorm_".strtolower(str_replace(" ", "_", "$shortName.zip"));
		$this->__zip = new createZip;
		$this->__jsonp = $jsonp;
		
		$this->_header($shortName, $title);

		$i=1;
		foreach($questions as $question){
			$identifierref = "sco_target";
			if($this->__jsonp){
				$identifierref = "RES-" . $question['id'];
				$this->__resources .= '<resource identifier="RES-'. $question['id'] .'" adlcp:scormtype="sco" href="q' . $question['id'] . '.html" type="webcontent"><file href="q' . $question['id'] . '.html"/></resource>';
				$this->__zip -> addFile($this->requestAction('/sqlexplorer/sco', array('return', 'bare'=>0, 'url'=>array('id'=>$question['id'], 'jsonp'=>true))), 'q' . $question['id'] . '.html');  	
			}
			$this->__imsmanifest .= '<item identifier="ITEM-' . $i. '" identifierref="' . $identifierref . '" isvisible="true"';
			if(isset($question['id'])){
				$this->__imsmanifest .= ' parameters="id=' . $question['id'] . '"';	
			}
			$this->__imsmanifest .= ">\n";
			$questionTitle = "Question $i";
			if(isset($question['title'])){
				$questionTitle = $question['title'];
			}
			$this->__imsmanifest .= "<title>$questionTitle</title>\n";
			$this->__imsmanifest .= "<adlcp:masteryscore>1</adlcp:masteryscore>\n";
			if(isset($question['datafromlms'])){
				$this->__imsmanifest .= '<adlcp:datafromlms>'. $question['datafromlms'] . "</adlcp:datafromlms>\n";
			}
			$this->__imsmanifest .= "</item>\n";
			$i++;
		}
	
		$this->_footer($questions);
		$this->_generateZip($shortName);
	}
	
	function _header($shortName, $title){
		$this->__imsmanifest ='<?xml version="1.0" encoding="UTF-8"?>';
		$this->__imsmanifest .=
<<<IMSMANIFEST

<manifest identifier="$shortName"
		  version="1.2"
		  xmlns="http://www.imsproject.org/xsd/imscp_rootv1p1p2"
		  xmlns:adlcp="http://www.adlnet.org/xsd/adlcp_rootv1p2"
		  xmlns:imsmd="http://www.imsglobal.org/xsd/imsmd_rootv1p2p1"
		  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
		  xsi:schemaLocation="http://www.imsproject.org/xsd/imscp_rootv1p1p2 imscp_rootv1p1p2.xsd http://www.imsglobal.org/xsd/imsmd_rootv1p2p1 imsmd_rootv1p2p1.xsd http://www.adlnet.org/xsd/adlcp_rootv1p2 adlcp_rootv1p2.xsd">
  <organizations default="sqlexplorer">
    <organization identifier="sqlexplorer" structure="hierarchical">
      <title>$title</title>
IMSMANIFEST;
	}

	function _footer(){
		$this->__imsmanifest .=
<<<IMSMANIFEST
   </organization>
  </organizations>
  <resources>
IMSMANIFEST;
		if($this->__jsonp){
			$this->__imsmanifest .= $this->__resources;
		} else{
    		$this->__imsmanifest .= '<resource identifier="sco_target" adlcp:scormtype="sco" href="' . $this->scoURL . '" type="text/html" />';
		}
$this->__imsmanifest .= <<<IMSMANIFEST
  </resources>
</manifest>
IMSMANIFEST;
	}
	
	function _generateZip($shortName){
		$fileContents = file_get_contents(SCORM . 'adlcp_rootv1p2.xsd');  
		$this->__zip -> addFile($fileContents, 'adlcp_rootv1p2.xsd');  
		$fileContents = file_get_contents(SCORM . 'ims_xml.xsd');  
		$this->__zip -> addFile($fileContents, 'ims_xml.xsd');  
		$fileContents = file_get_contents(SCORM . 'imscp_rootv1p1p2.xsd');  
		$this->__zip -> addFile($fileContents, 'imscp_rootv1p1p2.xsd');  
		$fileContents = file_get_contents(SCORM . 'imsmd_rootv1p2p1.xsd');  
		$this->__zip -> addFile($fileContents, 'imsmd_rootv1p2p1.xsd');  
		$this->__zip -> addFile($this->__imsmanifest, 'imsmanifest.xml');  
		
		$fd = fopen ($this->__fileName, "wb");
		$out = fwrite ($fd, $this->__zip -> getZippedfile());
		fclose ($fd);

		$this->__zip -> forceDownload($this->__fileName);
		@unlink($this->__fileName); 
	}
	
}