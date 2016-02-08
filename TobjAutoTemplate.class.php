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

	# http://phpduck.com/php-static-methods-and-properties/
	# http://r.je/static-methods-bad-practice.html

	# http://stackoverflow.com/questions/7543150/matching-double-quote-in-preg-match
	# http://webcheatsheet.com/php/regular_expressions.php
	# http://twig.sensiolabs.org/doc/templates.html#list-of-built-in-filters
	# http://coding.smashingmagazine.com/2011/10/17/getting-started-with-php-templating/
	# http://www.broculos.net/2008/03/how-to-make-simple-html-template-engine.html#.UzmomufdWhM

	# RegEx - Regular Expressions
	# http://www.autohotkey.com/docs/misc/RegEx-QuickRef.htm
	# http://weblogtoolscollection.com/regex/regex.php
	# http://forums.phpfreaks.com/topic/117560-regex-get-html-tag-attribute-value/
	# http://www.regexlib.com/?AspxAutoDetectCookieSupport=1
	# http://www.php.net/manual/fr/regexp.reference.recursive.php#95568
	# http://stackoverflow.com/questions/1721223/php-regexp-for-nested-div-tags
	# http://stackoverflow.com/questions/1422553/preg-match-for-nested-html-tags
	#

	class TobjAutoTemplate
	{
		public $listObjects 	= array();
		public $listFunctions 	= array();
		public $listValues 		= array();
		public $listKeys 		= array();
		public $listPostActions = array();
		public $HTML 			= '';
		public static $listCallbacks  = array();
		public static $listMarkupType = array('echo','form','func','hook','loop');
		public $keys 			= array();
		public $values 			= array();


		public function __construct()
		{

		}
		public function __destruct()
		{
			unset($this->listObjects);
			unset($this->listFunctions);
			unset($this->listValues);
			unset($this->listKeys);
			unset($this->listPostActions);
			#unset(self::$listCallbacks);
			unset($this->HTML);
		}
		###########################################################################################
		# http://www.phpkode.com/source/p/pyrocms/pyrocms-2.2.1/tests/mocks/core/common.php
		private function ms_escape($data)
		{
			$non_displayables = array(
			'/%0[0-8bcef]/', 			# url encoded 00-08, 11, 12, 14, 15
			'/%1[0-9a-f]/', 			# url encoded 16-31
			'/[\x00-\x08]/', 			# 00-08
			'/\x0b/', 					# 11
			'/\x0c/', 					# 12
			'/[\x0e-\x1f]/', 			# 14-31
			'/x7F/' 					# 127
			);
			foreach ($non_displayables as $regex)
			{
				$data = preg_replace($regex,'',$data);
			}
			#"\n"
			$search  = array("\0","\r","\x1a","\t");
			return trim(str_replace($search,'',$data));
		}
		###########################################################################################
		private function setAsBoolean($AsBoolString) 		{ return filter_var($AsBoolString,FILTER_VALIDATE_BOOLEAN); }
		private function resetListFunctions() 				{ $this->listFunctions 		= array(); }
		private function resetListValues() 					{ $this->listValues 		= array(); }
		private function resetListKeys() 					{ $this->listKeys 			= array(); }
		private function resetListPostActions() 			{ $this->listPostActions 	= array(); }
		private function resetKeys() 						{ $this->keys 				= array(); }
		private function resetValues() 						{ $this->values 			= array(); }
		
		public function setMarkupType($AsMarkupName) 		{ self::$listMarkupType[] = $AsMarkupName; }
		public function setKey($key,$value='')
		{
			$this->keys[]   = "{".$key."}";
			$this->values[] = $value;
		}
		public function setVar($key, $value='')
		{
			if (is_array($key))
			{
				if (isset($key[0]))
				{
					foreach ($key as $idx => $valx)
					{
						foreach ($valx as $key2 => $value2)
						{
							$this->listKeys[]   = '{'.$key2.'}';
							$this->listValues[] = $value2;
						}
					}
				}
				else
				{
					foreach ($key as $idx => $valx)
					{
						$this->listKeys[]   = '{'.$idx.'}';
						$this->listValues[] = $valx;
					}
				}
			}
			else
			{
				$this->listKeys[]   = $key;
				$this->listValues[] = $value;
			}
		}
		public function setCallback($AsMarkupName,$AObject,$AsFuncName,$AsParams=array(),$AbReplace=false)
		{
			if ((!isset(self::$listCallbacks[$AsMarkupName])) || ($AbReplace==true))
			{
				self::$listCallbacks[$AsMarkupName] = array(
					'object' 	=> $AObject,
					'function' 	=> $AsFuncName,
					'params' 	=> $AsParams);
			}
		}
		public function reset()
		{
			$this->resetListFunctions();
			$this->resetListValues();
			$this->resetListKeys();
			$this->resetListPostActions();
		}
		private function getFileContent($AsFileName)
		{
			$this->HTML = $this->ms_escape(file_get_contents($AsFileName));
		}
		private function replaceData()
		{
			$this->HTML = str_ireplace($this->listKeys,$this->listValues,$this->HTML);
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

		private function getFunctions($AsHTML=false)
		{
			if (!$AsHTML) $AsHTML = $this->HTML;
			$auxArray = array();
			#preg_match_all('/{([a-zA-Z0-9_]*?) (.*?)}(.*?|(?R)){\/1}/',$this->HTML,$output);
			preg_match_all('/{([a-zA-Z0-9_]+) (.*?)}((([^{]*?)|(?R)).*?){\/\\1}/sm',$AsHTML,$output);
			#var_dump($output);
			foreach ($output[0] as $key => &$value)
			{
				$auxArray[$key] = array(
					'outerhtml' => $value,
					'markup' 	=> $output[1][$key],
					'attributes'=> $this->getAttributes($output[2][$key]),
					'innerhtml' => $output[3][$key]
				);
				$att = &$auxArray[$key]['attributes'];
				if (!isset($att['obj'])) 		$att['obj']   = '';
				if (!isset($att['class'])) 		$att['class'] = '';
				if (!isset($att['func'])) 		$att['func']  = '';
				if (!isset($att['jsonparams'])) $att['jsonparams'] = '';
				if (!isset($att['jsonencode'])) $att['jsonencode'] = '';
				if (!isset($att['postaction'])) $att['postaction'] = false;
				$att['params'] 		= (!isset($att['params'])) 		? '' : $this->processParams($att['params']);
				$att['jsonparams'] 	= (!isset($att['jsonparams'])) 	? '' : $this->processJsonParams($att['jsonparams'],$att['jsonencode']);
				#$auxArray[$key]['markup'] 		= ($value === '') ? $output[4][$key] : $value;
			}
			
			return $auxArray;
		}
		private function getPostActions()
		{
			preg_match_all('/{% (.*?) %}/',$this->HTML,$output);
			#var_dump($output);
			$auxArray = array();
			foreach ($output[0] as $key => $value)
			{
				$auxArray[$output[1][$key]] = array();
				$auxArray[$output[1][$key]]['outerhtml'] = $value;
				$auxArray[$output[1][$key]]['markup'] 	 = $output[1][$key];
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
			preg_match_all('/(.*?)="(.*?)"/', $AsString, $matches);
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
							$AsJson = urldecode($AsJson);
						} break;
				}
				$Result = json_decode($AsJson,true);
			}
			return $Result;
		}
		private function callFunction($AsObject,$AsClass,$AsFunction,$AsParams)
		{
			$Result = false;
			if (($AsObject !== '') && ($objTbl = $this->getObject($AsObject)))
			{
				$Result = call_user_func_array(array($objTbl, $AsFunction), $AsParams);
			}
			else
			if ($AsClass !== '')
			{
				if (method_exists($AsClass, $AsFunction))
					$Result = call_user_func_array(array($AsClass, $AsFunction), $AsParams);
			}
			else
			if ($AsFunction !== '')
			{
				if (function_exists($AsFunction))
					$Result = call_user_func_array($AsFunction, $AsParams);
				else
					$Result = '';
			}

			return $Result;
		}
		private function runMarkups($AaLoops=false)
		{
			if ($AaLoops !== false)
				$this->listFunctions = $AaLoops;
			if (count($this->listFunctions) == 0)
				return false;

			$arrToSearch  = array();
			$arrToReplace = array();
			foreach($this->listFunctions as $pos => &$arrVal)
			{
				$outerhtml 	= $arrVal['outerhtml'];
				$sMarkup 	= $arrVal['markup'];
				$loop 		= $arrVal['innerhtml'];
				$arrRes 	= $arrVal['attributes'];

				#$sObject 	= $arrRes['obj'];
				#$sClass 	= $arrRes['class'];
				#$function 	= $arrRes['func'];
				$params 	= ($arrRes['params'] !== '') ? $arrRes['params'] : $arrRes['jsonparams'];
				#$json 		= $arrRes['jsonparams'];
				#$jsonencode = $arrRes['jsonencode'];
				$postaction = $arrRes['postaction'];


				$htmlReplace = '';
				$Result 	 = false;
				$Result 	 = $this->callFunction($arrRes['obj'],$arrRes['class'],$arrRes['func'],$params);

				#Execute an action when function completed? (echo)
				if (($postaction == true) && (isset($this->listPostActions[$sMarkup])))
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
					# Checks if a Markup action was overrited
					if (isset(self::$listCallbacks[$sMarkup]))
					{
						$objCall 	= self::$listCallbacks[$sMarkup]['object'];
						$funcCall 	= self::$listCallbacks[$sMarkup]['function'];
						$parCall 	= self::$listCallbacks[$sMarkup]['params'];

						# IF is NOT an object, getObject($AsObjName);
						if (!is_object($objCall))
						{
							$objCall = $this->getObject($objCall);
						}
						$funcResult = call_user_func_array(array($objCall, $funcCall), $parCall);
						
						if (isset($funcResult['echo']) && ($funcResult['echo'] == true))
						{
							$htmlReplace = $funcResult['value'];
						}
						else
						{
							$htmlReplace = $funcResult;
						}
					}
					else
					{
						switch ($sMarkup)
						{
							case 'form':
							case 'loop':
								{
									# IF "option" is given, add (selected/checked) property to the returned array
									# @option: 		value that will be checked if matches (Ex: (13=="15")? // (14=="15")? etc)
									# @statusName: 	checked / selected / other
									# @fieldName: 	id / name / etc (MUST BE IN the returned array)
									$optionValue = (isset($arrRes['option'])) ? $arrRes['option'] : '';
									$statusName  = (isset($arrRes['statusName'])) ? $arrRes['statusName'] : 'selected'; #add {checked/selected} to the LI
									$fieldName   = (isset($arrRes['fieldName'])) ? $arrRes['fieldName'] : '';
									$arrhtml = array();

									foreach($Result as $key => &$arrValue)
									{
										$this->resetValues();
										$this->resetKeys();

										$this->setKey('i', ($key+1));
										$this->setKey('random', substr(md5(mt_rand(0,999)),0,10));
										$this->setKey('true', substr(md5(mt_rand(0,999)),0,10));
										
										if (($fieldName !== '') && (isset($arrValue[$fieldName])))
										{
											$this->setKey($statusName, ($arrValue[$fieldName] === $optionValue) ? $statusName : '');
											$this->setKey('disabled', ($arrValue[$fieldName] === $optionValue) ? 'disabled' : '');
										}

										foreach($arrValue as $key2 => &$value)
										{
											$this->setKey($key2, (mb_check_encoding($value, 'UTF-8')) ? $value : utf8_encode($value));
										}
										$arrhtml[] = str_ireplace($this->keys, $this->values, $loop);
									}

									$htmlReplace = implode($arrhtml,"\n");
								} break;
							case 'hook':
								{

								} break;
							case 'func':
								{

								} break;
							case 'echo':
								{
									if (is_array($Result))
									{
										ob_start();
											var_dump($Result);
											$htmlReplace = ob_get_contents();
										ob_end_clean();
									}
									else
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

		public function renderize($FileHTML,$isFile=true,$processPHP=false,$cleanVariables=false)
		{
			if ($processPHP)
			{
				ob_start();
					include($FileHTML);
					$this->HTML = $this->ms_escape(ob_get_contents());
				ob_end_clean();
			}
			else
			{
				if ($isFile)
					$this->getFileContent($FileHTML);
				else
					$this->HTML = $this->ms_escape($FileHTML);
			}
			$this->replaceData();
			$this->getMarkups();
			$this->runMarkups();
			if ($cleanVariables)
			{
				$this->reset();
			}
			
			return $this->HTML;
		}
	}
	
?>
