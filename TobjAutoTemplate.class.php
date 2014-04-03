<?php
	/* *********************************************************************
	*  Author: Paulo Mota (paulomota2@gmail.com)
	*  Web...: N/A
	*  Name..: TobjAutoTemplate.class.php
	*  Desc..: Auto Template Framework
	*  Date..: 27/03/2014
	*  Date-update: 
	*
	*********************************************************************** */
	
	#error_reporting(E_ALL);
	#ini_set('display_errors', 1);

	# http://phpduck.com/php-static-methods-and-properties/
	# http://r.je/static-methods-bad-practice.html

	# http://stackoverflow.com/questions/7543150/matching-double-quote-in-preg-match
	# http://webcheatsheet.com/php/regular_expressions.php
	# http://twig.sensiolabs.org/doc/templates.html#list-of-built-in-filters
	# http://coding.smashingmagazine.com/2011/10/17/getting-started-with-php-templating/
	# http://www.broculos.net/2008/03/how-to-make-simple-html-template-engine.html#.UzmomufdWhM

	class TobjAutoTemplate
	{
		public $listObjects 	= array();
		public $listFunctions 	= array();
		public $listValues 		= array();
		public $listPostActions = array();
		public $HTML 			= '';
		public static $listCallbacks  = array();
		public static $listMarkupType = array('echo','func','hook','loop');

		public function __construct()
		{

		}
		public function __destruct()
		{
			unset($this->listObjects);
			unset($this->listFunctions);
			unset($this->listValues);
			unset($this->listPostActions);
			#unset($this::$listCallbacks);
			unset($this->HTML);
		}
		###########################################################################################
		private function ms_escape_string($data)
		{
			$non_displayables = array(
			'/%0[0-8bcef]/', 			# url encoded 00-08, 11, 12, 14, 15
			'/%1[0-9a-f]/', 			# url encoded 16-31
			'/[\x00-\x08]/', 			# 00-08
			'/\x0b/', 					# 11
			'/\x0c/', 					# 12
			'/[\x0e-\x1f]/' 			# 14-31
			);

			foreach ($non_displayables as $regex)
			{
				$data = preg_replace($regex, '', $data);
			}
			$search  = array("\0","\n","\r","\x1a","\t");
			$data 	 = str_replace($search,'',$data);

			return trim($data);
		}
		###########################################################################################
		private function setAsBoolean($AsBoolString) 		{ return filter_var($AsBoolString,FILTER_VALIDATE_BOOLEAN); }
		private function resetListFunctions() 				{ $this->listFunctions 		= array(); }
		private function resetListValues() 					{ $this->listValues 		= array(); }
		private function resetListPostActions() 			{ $this->listPostActions 	= array(); }
		public function setMarkupType($AsMarkupName) 		{ $this::$listMarkupType[] = $AsMarkupName; }
		public function setVar($AKey, $AValue) 				{ $this->listValues[$AKey] = $AValue; }
		public function setCallback($AsMarkupName,$AObject,$AsFuncName,$AsParams=array(),$AbReplace=false)
		{
			if ((!isset($this::$listCallbacks[$AsMarkupName])) || ($AbReplace==true))
			{
				$this::$listCallbacks[$AsMarkupName] = array(
					'object'=> $AObject,
					'function' => $AsFuncName,
					'params' => $AsParams);
			}
		}
		public function cleanVariables()
		{
			$this->resetListFunctions();
			$this->resetListValues();
			$this->resetListPostActions();
		}
		private function getFileContent($AsFileName)
		{
			$this->HTML = $this->ms_escape_string(file_get_contents($AsFileName));
		}
		private function runReplaceVars()
		{
			foreach ($this->listValues as $key => $value)
			{
				$this->HTML = str_ireplace($key,$value,$this->HTML);
			}
		}

		private function checkKeyInObject($AsObjName)
		{
			return isset($this->listObjects[$AsObjName]);
		}
		public function addObject($AsObjName,$AobjObject)
		{
			if (!$this->checkKeyInObject($AsObjName))
			{
				$this->listObjects[$AsObjName] = $AobjObject;
			}
		}
		public function deleteObject($AsObjName)
		{
			if ($this->checkKeyInObject($AsObjName))
			{
				unset($this->listObjects[$AsObjName]);
			}
		}
		public function getObject($AsObjName)
		{
			if ($this->checkKeyInObject($AsObjName))
			{
				return ($this->listObjects[$AsObjName]);
			}
			else
				return false;
		}

		private function getFunctions()
		{
			$auxArray = array();
			preg_match_all('/{(.*?) (.*?)}(.*?){\/(.*?)}/',$this->HTML,$output);
			#preg_match_all('/{(.*?) (.*?)}([^`]*?){\/(.*?)}/',$this->HTML,$output);
			#var_dump($output);
			foreach ($output[0] as $key => $value)
			{
				$auxArray[$key] = array();
				$auxArray[$key]['outerhtml'] 	= $value;
				$auxArray[$key]['markup'] 		= $output[1][$key];
				$auxArray[$key]['attributes'] 	= $this->getAttributes($output[2][$key]);
				$auxArray[$key]['innerhtml'] 	= $output[3][$key];
				#$auxArray[$key]['markup'] 		= ($value === '') ? $output[4][$key] : $value;
			}
			return $auxArray;
		}
		private function getPostActions()
		{
			$auxArray = array();
			preg_match_all('/{% (.*?) %}/',$this->HTML,$output);
			#var_dump($output);
			foreach ($output[0] as $key => $value)
			{
				$auxArray[$output[1][$key]] = array();
				$auxArray[$output[1][$key]]['outerhtml'] 	= $value;
				$auxArray[$output[1][$key]]['markup'] 		= $output[1][$key];
			}
			return $auxArray;
		}

		private function getMarkups()
		{
			$this->resetListFunctions();
			$this->resetListPostActions();
			$this->listFunctions 	= $this->getFunctions();
			$this->listPostActions 	= $this->getPostActions();
		}

		private function getAttributes($AsString)
		{
			preg_match_all('/(.*?)="(.*?)"/is', $AsString, $matches);
			#var_dump($matches);
			$Result = array();
			foreach ($matches[1] as $key => $value)
			{
				$Result[trim($value)] = $matches[2][$key];
			}
			return $Result;
		}
		private function processParams($AsParams)
		{
			$Result = explode(';', substr($AsParams,1,-1));

			foreach($Result as $key => &$value)
			{
				if (strpos($value,'array') !== false)
				{
					if (isset($value[6]) && ($value[6] === '(')) #check if is "array()"
					{
						$value = explode(',', substr($value,6,-1));
					}
					else
					{
						$value = array();
					}
				}
				else
				if ($value !== '')
				{
					if (isset($value[0]))
					{
						if ($value[0] !== "'")
						{
							$value = (int)$value+0;
						}
						else
							$value = substr($value,1,-1);
					}
				}
			}
			return $Result;
		}
		private function processJsonParams($AsJson,$AsEncoding)
		{
			$Result = array();
			if ($AsJson !== '')
			{
				switch ($AsEncoding)
				{
					case 'urlencode':
						{
							$AsJson = urldecode($AsJson);
						} break;
					case 'base64':
						{
							$AsJson = base64_decode($AsJson);
						} break;
					default:
						{

						} break;
				}
				$Result = json_decode($AsJson,true);
			}
			return $Result;
		}
		private function runLoops($AaLoops=false)
		{
			$this->listFunctions = ($AaLoops !== false) ? $AaLoops : $this->listFunctions;
			#ksort($AaLoops); #Ordena Array por Key

			$arrToSearch  = array();
			$arrToReplace = array();
			foreach($this->listFunctions as $pos => &$arrVal)
			{
				$outerhtml 	= $arrVal['outerhtml'];
				$sMarkup 	= $arrVal['markup'];
				$loop 		= $arrVal['innerhtml'];
				$arrRes 	= $arrVal['attributes'];

				$sObject 	= (isset($arrRes['obj'])) 		? $arrRes['obj'] 	: '';
				$sClass 	= (isset($arrRes['class'])) 	? $arrRes['class'] 	: '';
				$function 	= (isset($arrRes['func'])) 		? $arrRes['func'] 	: '';
				$params 	= (isset($arrRes['params']))	? $this->processParams($arrRes['params']) : '';
				$json 		= (isset($arrRes['jsonparams']))? $arrRes['jsonparams'] : '';
				$jsonencode = (isset($arrRes['jsonencode']))? $arrRes['jsonencode'] : '';
				$postaction = (isset($arrRes['postaction']))? $this->setAsBoolean($arrRes['postaction']) : false;
				$params 	= ($params !== '') ? $params : $this->processJsonParams($json,$jsonencode);

				$htmlReplace = '';
				$Result 	 = false;
				if (($sObject !== '') && ($objTbl = $this->getObject($sObject)))
				{
					$Result = call_user_func_array(array($objTbl, $function), $params);
				}
				else
				if ($sClass !== '')
				{
					$Result = call_user_func_array(array($sClass, $function), $params);
				}
				else
				{
					$Result = call_user_func_array($function, $params);
				}

				#Execute an action when function completed? (echo)
				if ($postaction == true)
				{
					$sPostOuterHtml = $this->listPostActions[$sMarkup]['outerhtml'];
					$this->HTML 	= str_ireplace($sPostOuterHtml, $Result, $this->HTML);
					foreach($this->listFunctions as $key => &$arrValue)
					{
						$arrValue['outerhtml'] = str_ireplace($sPostOuterHtml, $Result, $arrValue['outerhtml']);
						$arrValue['innerhtml'] = str_ireplace($sPostOuterHtml, $Result, $arrValue['innerhtml']);
					}
				}

				#If there is no Errors
				if ($Result)
				{
					if (isset($this::$listCallbacks[$sMarkup]))
					{
						$objCall 	= $this::$listCallbacks[$sMarkup]['object'];
						$funcCall 	= $this::$listCallbacks[$sMarkup]['function'];
						$parCall 	= $this::$listCallbacks[$sMarkup]['params'];

						# IF is NOT an object, getObject($AsObjName);
						if (!is_object($objCall))
						{
							$objCall = $this->getObject($objCall);
						}
						$funcResult = call_user_func_array(array($objCall, $funcCall), $parCall);
						
						if (is_array($funcResult))
						{
							if (isset($funcResult['echo']))
								$htmlReplace = $funcResult['value'];
						}
						else
							$htmlReplace =  $funcResult;
					}
					else
					{
						switch ($sMarkup)
						{
							case 'loop':
								{
									$arrhtml = array();
									foreach($Result['ROW'] as $key => $arrValue)
									{
										$auxLoop = str_ireplace('{i}', ($key+1), $loop);
										$auxLoop = str_ireplace('{random}', substr(md5(mt_rand(0,999)),0,10), $loop);
										foreach($arrValue as $key2 => $value)
										{
											$auxLoop = str_ireplace('{'.$key2.'}', $value, $auxLoop);
										}
										$arrhtml[] = $auxLoop;
									}
									$htmlReplace = implode($arrhtml);
								} break;
							case 'hook':
								{

								} break;
							case 'func':
								{

								} break;
							case 'echo':
								{
									$htmlReplace = $Result;
								} break;
							default:
								{

								} break;
						}
					}
				}
				$arrToSearch[]  = $outerhtml;
				$arrToReplace[] = $htmlReplace;
			}
			$this->HTML = str_ireplace($arrToSearch, $arrToReplace, $this->HTML);
		}

		public function tpl_page($AsData,$AisFile=true,$AbProcessFile=false)
		{
			if ($AbProcessFile)
			{
				ob_start();
					include($AsData);
					$this->HTML = $this->ms_escape_string(ob_get_contents());
				ob_end_clean();
			}
			else
			{
				if ($AisFile)
					$this->getFileContent($AsData);
				else
					$this->HTML = $AsData;
			}
			$this->runReplaceVars();
			$this->getMarkups();
			$this->runLoops();
			return $this->HTML;
		}
	}
	
?>