<?php
/* ProductMatrix
 *
 * @category   Webshopapps
 * @package    Webshopapps_productmatrix
 * @copyright   Copyright (c) 2013 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2013, Zowta, LLC - US license
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */


class Webshopapps_Productmatrix_Model_Mysql4_Carrier_Productmatrix extends Mage_Core_Model_Mysql4_Abstract
{

	private $_request;
	private $_zipSearchString;
	private $_table;
	private $_customerGroupCode;
	private $_starIncludeAll;
	private $_minusOne;
	private $_exclusionList;
	private $_structuredItems;
	private $_hasEmptyPackages;
	private $_prioritySet;
	private $_maxPriceSet;
	private $_roundPrice;
	private $_freeShipping;
	private $_useParent;
	private $_useBundleParent=false;
	private $_useConfigurableParent=true;
	private $_appendStarRates;
	private $_stockFound = false;
	private $_outofstock = false;
	private $_debug;
	private $_options;
	private $_ignoreAdditionalItemPrice;
	private $_shortMatchPostcode = '';
	private $_longMatchPostcode = '';
	private $_twoPhaseFiltering;
	private $_uDrop;

    private $_volume;
    private $_greaterVolume = false;
    private $_usingGreaterVolLogic=false;
    private $_actualWeightMethodArr;

    protected function _construct()
    {
        $this->_init('shipping/productmatrix', 'pk');
    }

    public function getNewRate(Mage_Shipping_Model_Rate_Request $request, &$errorText)
    {
        $this->_request=$request;
        $this->initGlobals();

        $read = $this->_getReadAdapter();

        if ($this->_debug) {
			Mage::helper('wsalogger/log')->postDebug('productmatrix','Option Settings',$this->_options);
        }

        $zipcodeMaxLength = Mage::getStoreConfig('carriers/productmatrix/zipcode_max_length');
        if(empty($zipcodeMaxLength)) {
            if ( Mage_Usa_Model_Shipping_Carrier_Abstract::USA_COUNTRY_ID == $request->getDestCountryId() ||  'BR' == $request->getDestCountryId()) {
                $splitPostcode = explode('-',$request->getDestPostcode());
                $postcode = $splitPostcode[0];
            } else {
                $postcode = $request->getDestPostcode();
            }
        } else {
            $postcode = substr($request->getDestPostcode(), 0, $zipcodeMaxLength);
        }

        $filterCanada = false;

        $this->processZipcode($read, $postcode,
			$this->_twoPhaseFiltering, $this->_zipSearchString, $this->_shortMatchPostcode, $this->_longMatchPostcode, $filterCanada );

        // if POBOX search on CITY field
        $searchPOBox=false;
    	if (preg_match('/(^|(?:post(al)? *(?:office *)?|p[. ]*o\.? *))box *#? *\w+/ui', $request->getDestStreet())) {
  			$searchPOBox=true;
    		if ($this->_debug) {
  				Mage::helper('wsalogger/log')->postDebug('productmatrix','POBox check','We cannot deliver to PO boxes.');
    		}
		}

		$items = $request->getAllItems();
		if (empty($items) || ($items=='')) {

            if ($this->_debug) {
                Mage::helper('wsalogger/log')->postCritical('productmatrix','No Items','No valid items have been found');
            }

			return null;
		}

		if ($this->_debug) {
			Mage::helper('wsalogger/log')->postInfo('productmatrix','Customer Group Code',$this->_customerGroupCode);
		}

		// get the package_id's for the items in the cart

		$conditionName=$this->_request->getPMConditionName();
		$this->populateStructuredItems($items,$conditionName);
        if (!Mage::helper('wsacommon')->checkItems('Y2FycmllcnMvcHJvZHVjdG1hdHJpeC9zaGlwX29uY2U=',
        	'dXBzaWRlZG93bg==','Y2FycmllcnMvcHJvZHVjdG1hdHJpeC9zZXJpYWw=')) {
 	     		Mage::helper('wsalogger/log')->postCritical('productmatrix',base64_decode('TGljZW5zZQ=='),base64_decode('U2VyaWFsIEtleSBJbnZhbGlk'));
        		return null; }

		$first=true;
		if ($filterCanada && 'CA' == $this->_request->getDestCountryId()) {
			$zipBreakdown = strtoupper(substr($postcode,0,3));
		} else {
			$filterCanada = false;
			$zipBreakdown = array();
		}
        $finalResults = array();
		foreach ($this->_structuredItems as $structuredItem) {
			if ($structuredItem['package_id']=='none' && $this->_starIncludeAll) { continue; }
			$this->_minusOne=false;
            $data=$this->runSelectStmt($read,$structuredItem,$searchPOBox,$filterCanada, $zipBreakdown,$errorText);
            if (!empty($data)) {
                if(!$first) {
                    if ($conditionName=='highest') {
                        $this->mergeHighest($data,$finalResults);
                    } else if ($conditionName=='lowest') {
                        $this->mergeLowest($data,$finalResults);
                    } else if ($conditionName=='order') {
                        $this->mergeOrdered($data,$finalResults);
                    } else {
                        $this->mergeResults($data,$finalResults);
                    }
                } else {
					$first=false;
					$finalResults=$data;
                }
            } elseif (!$this->_starIncludeAll  || (!$this->_starIncludeAll && $this->_minusOne)) {
                if ($this->_debug) {
                    Mage::helper('wsalogger/log')->postCritical('productmatrix','No Rates Found','There are no valid rates in your CSV file');
                }
				return null;
			}
		}

        $this->_removeExcludedDeliveryMethods($finalResults);

		if (empty($finalResults)) {
            if ($this->_debug) {
                Mage::helper('wsalogger/log')->postCritical('productmatrix','No Rates Found','There are no valid rates in your CSV file');
            }

            return null;
        }

        $absoluteResults = $this->organisePriorities($finalResults);

    	if (in_array('custom_sorting',$this->_options)) {
			usort($absoluteResults, array($this,'cmp_notes'));
    	}

        $this->manipulatePricing($absoluteResults,$conditionName);

        return $absoluteResults;
    }


    protected function initGlobals()
    {
        $this->_table = Mage::getSingleton('core/resource')->getTableName('productmatrix_shipping/productmatrix');

        $this->_debug = Mage::helper('wsalogger')->isDebug('Webshopapps_Productmatrix');
        $this->_options = explode(',', Mage::getStoreConfig("carriers/productmatrix/ship_options"));
        $this->_uDrop = Mage::getConfig()->getNode('productmatrix/specialvars/udrop') == 1;
        $this->_volume = Mage::getConfig()->getNode('productmatrix/specialvars/volume') == 1;
        $this->_usingGreaterVolLogic = Mage::getStoreConfig('carriers/productmatrix/calculate_greater_volume');
        $this->_actualWeightMethodArr = array_filter(explode(",", Mage::getStoreConfig('carriers/productmatrix/always_weight')));
        $this->_starIncludeAll = Mage::getStoreConfig("carriers/productmatrix/star_include_all");
        $this->_useParent = Mage::getStoreConfig("carriers/productmatrix/parent_group");

        $this->_appendStarRates =  in_array('append_star_rates',$this->_options);
        $this->_roundPrice = in_array('round_price',$this->_options);

        $this->_hasEmptyPackages = false;
        $this->_prioritySet = false;
        $this->_ignoreAdditionalItemPrice = false;
        $this->_maxPriceSet = false;

        $this->_freeShipping = $this->_request->getFreeShipping();
        $this->_customerGroupCode = $this->getCustomerGroupCode();

        $this->_structuredItems = array();

        $this->setupUseParentGlobals();
    }

    protected function manipulatePricing(&$absoluteResults,$conditionName) {

        if ($conditionName=="highest" && !$this->_ignoreAdditionalItemPrice)
		{
			foreach ($absoluteResults as $key=>$data) {
				$absoluteResults[$key]['price'] = $data['price'] + $data['additional_price'];
				if ($data['qty']>1 && $data['multiprice']>0 && !$data['override']) {
					$absoluteResults[$key]['price'] = $absoluteResults[$key]['price'] + $data['multiprice']*($data['qty']-1);
				}
				$tempAlgorithm=explode("=",$data['algorithm'],2);
				if (strtolower($tempAlgorithm[0]) == ('ai'))
				{
					if ($data['qty']=1 && $data['multiprice']>0 && !$data['override']) {
						$absoluteResults[$key]['price'] = $absoluteResults[$key]['price'] + $data['multiprice']*($data['qty']);
					}
				}
			}
		}

		if ($this->_maxPriceSet) {
			foreach ($absoluteResults as $key=>$data) {
				if ($data['max_price']!=-1 && $data['price']>$data['max_price']) {
					$absoluteResults[$key]['price'] = $data['max_price'];
				}
			}
		}

		if ($this->_roundPrice) {
		    foreach ($absoluteResults as $key=>$data) {
				$absoluteResults[$key]['price'] = round($absoluteResults[$key]['price']);
			}
		}

    }

    protected function organisePriorities(&$finalResults) {
        $absoluteResults = array();

        if ($this->_prioritySet) {
            foreach ($finalResults as $i => $rate) {
                $priceArr[$i] = $rate['price'];
                $priority[$i] = $rate['priority'];
            }

            array_multisort($priceArr, SORT_ASC, $priority, SORT_ASC, $finalResults);
            $previousPrice=-100;
            $previousPriority="";

            foreach ($finalResults as $data) {
                if ($previousPrice==$data['price'] && $data['priority']!=$previousPriority  && is_numeric($data['priority']) && is_numeric($previousPriority)) {
                    continue;
                } else {
                    $previousPrice=$data['price'];
                    $previousPriority=$data['priority'];
                    $absoluteResults[]=$data;
                }
            }
        } else {
            $absoluteResults=$finalResults;
        }

        return $absoluteResults;
    }

    protected function _removeExcludedDeliveryMethods(&$finalResults) {
        if (empty($finalResults)) { return; }
        if (!empty($this->_exclusionList)) {
            foreach ($finalResults as $key=>$result) {
                foreach ($this->_exclusionList as $exclusionItem) {
                    if ($result['delivery_type']==$exclusionItem['delivery_type']) {
                        $finalResults[$key]="";
                        break;
                    }
                }

            }
            foreach ($finalResults as $key=>$result) {
                if (empty($finalResults[$key])) {
                    unset($finalResults[$key]);
                }
            }
        }
    }

	static function cmp_notes($a, $b)
	{
	    if ($a['notes'] == $b['notes']) {
	        return 0;
	    }
	    return ($a['notes'] < $b['notes']) ? -1 : 1;
	}

    private function getCustomerGroupCode() {

    	if ($ruleData = Mage::registry('rule_data')) {
            $gId = $ruleData->getCustomerGroupId();
            return Mage::getModel('customer/group')->load($gId)->getCode();
    	} else {
    		return Mage::getModel('customer/group')->load(
			Mage::getSingleton('customer/session')->getCustomerGroupId())->getCode();
    	}

    }

	protected function processZipcode($readAdaptor, $customerPostcode,&$twoPhaseFiltering,
		&$zipString, &$shortMatchPostcode, &$longMatchPostcode,&$filterCanada ) {

        $postcodeFilter = Mage::getStoreConfig("carriers/productmatrix/postcode_filter");

        if ($this->_debug) {
            Mage::helper('wsalogger/log')->postDebug('productmatrix','Postcode Format',$postcodeFilter);
        }

		$customerPostcode = trim($customerPostcode);
		$twoPhaseFiltering = false;
		if ($postcodeFilter == 'numeric' && is_numeric($customerPostcode)) {
			$zipString = ' AND '.$customerPostcode.' BETWEEN dest_zip AND dest_zip_to )';

		} else if ($postcodeFilter == 'uk' && strlen($customerPostcode)>4) {
			$twoPhaseFiltering = true;
			$longPostcode=substr_replace($customerPostcode,"",-3);
			$longMatchPostcode = trim($longPostcode);
			$shortMatchPostcode = preg_replace('/\d/','', $longMatchPostcode);
			$shortMatchPostcode = $readAdaptor->quoteInto(" AND STRCMP(LOWER(dest_zip),LOWER(?)) = 0)", $shortMatchPostcode);
		}  else if ($postcodeFilter == 'both') {
			if (is_numeric($customerPostcode)){
				$zipString = ' AND '.$customerPostcode.' BETWEEN dest_zip AND dest_zip_to )';
			} else {
				$twoPhaseFiltering = true;
				$longPostcode=substr_replace($customerPostcode,"",-3);
				$longMatchPostcode = trim($longPostcode);
				$shortMatchPostcode = preg_replace('/\d/','', $longMatchPostcode);
				$shortMatchPostcode = $readAdaptor->quoteInto(" AND STRCMP(LOWER(dest_zip),LOWER(?)) = 0)", $shortMatchPostcode);
			}
		} else if ($postcodeFilter == 'canada') {
			// first search complete postcode
			// then search exact match on first 3 chars
			// then search range
			$shortPart = substr($customerPostcode,0,3);
			if (strlen($shortPart) < 3 || !is_numeric($shortPart[1]) || !ctype_alpha($shortPart[2])) {
				$zipString = $readAdaptor->quoteInto(" AND ? LIKE dest_zip )", $customerPostcode);
			} else {
				$zipFromRegExp='^'.$shortPart[0].'[0-'.$shortPart[1].'].';
				$zipToRegExp='^'.$shortPart[0].'['.$shortPart[1].'-9].';
				$shortMatchPostcode = $readAdaptor->quoteInto(" AND dest_zip REGEXP ?", $zipFromRegExp).$readAdaptor->quoteInto(" AND dest_zip_to REGEXP ? )",$zipToRegExp );
				$longMatchPostcode = $customerPostcode;
				$twoPhaseFiltering = true;
				$filterCanada = true;
			}
		} else if ($postcodeFilter == 'can_numeric') {
			if (is_numeric($customerPostcode)){
				$zipString = ' AND '.$customerPostcode.' BETWEEN dest_zip AND dest_zip_to )';
			} else {
				// first search complete postcode
				// then search exact match on first 3 chars
				// then search range
				$shortPart = substr($customerPostcode,0,3);
				if (strlen($shortPart) < 3 || !is_numeric($shortPart[1]) || !ctype_alpha($shortPart[2])) {
					$zipString = $readAdaptor->quoteInto(" AND ? LIKE dest_zip )", $customerPostcode);
				} else {
					$zipFromRegExp='^'.$shortPart[0].'[0-'.$shortPart[1].'].';
					$zipToRegExp='^'.$shortPart[0].'['.$shortPart[1].'-9].';
					$shortMatchPostcode = $readAdaptor->quoteInto(" AND dest_zip REGEXP ?", $zipFromRegExp).$readAdaptor->quoteInto(" AND dest_zip_to REGEXP ? )",$zipToRegExp );
					$longMatchPostcode = $customerPostcode;
					$twoPhaseFiltering = true;
					$filterCanada = true;
				}
			}
		} else {
			 $zipString = $readAdaptor->quoteInto(" AND ? LIKE dest_zip )", $customerPostcode);
		}

		if ($this->_debug) {
        	Mage::helper('wsalogger/log')->postDebug('productmatrix','Postcode Filter',$postcodeFilter);
			Mage::helper('wsalogger/log')->postDebug('productmatrix','Postcode Range Search String',$zipString);
        	if ($twoPhaseFiltering) {
        		Mage::helper('wsalogger/log')->postDebug('productmatrix','Postcode 2 Phase Search String','short match:'.$shortMatchPostcode.
        			', long match:'.$longMatchPostcode);
        	}
    	}

	}


    private function mergeHighest($indResults,&$baseResults)
    {
        $found=false;

    	foreach ($baseResults as $key=>$result)
    	{
    	   	$found=false;
    		foreach ($indResults as $indKey=>$data) {
    			if ($result['delivery_type']==$data['delivery_type']) {
    				if (!$baseResults[$key]['override'] && ($data['price']>$baseResults[$key]['price'] || $data['override'])) { // if higher get higher
						$baseResults[$key]['price']=$data['price'];
						$baseResults[$key]['additional_price']+=$baseResults[$key]['multiprice']*$baseResults[$key]['qty'];
						$baseResults[$key]['multiprice']=$data['multiprice'];
						$baseResults[$key]['qty']=$data['qty'];
					} else {
						$baseResults[$key]['additional_price']+=$data['multiprice']*$data['qty'];
					}
					if ($baseResults[$key]['max_price']<$data['max_price']) {
						$baseResults[$key]['max_price']=$data['max_price'];
					}
					$indResults[$indKey]['found']=true;
					$found=true;
					break;
				}
    		}
    		if (!$found && !$this->_starIncludeAll && !$baseResults[$key]['showall']) {
    		    // no match so remove
    			if ($this->_debug) {
					Mage::helper('wsalogger/log')->postInfo('productmatrix','Delivery Type - No Match Found - Removing',$baseResults[$key]['delivery_type']);
				}
    			$baseResults[$key]="";
    		}
    	}
    	// get show all items
    	foreach ($indResults as $key=>$result)
    	{
    		if ( !$found && $result['showall']) {
    			$baseResults[]=$result;
    			$indResults[$key]['found']=true;
    		}
    	}

    	if ($this->_starIncludeAll) {
    	   	// check for missing
	    	foreach ($indResults as $data) {
	    		if (empty($data['found'])) {
	    			$baseResults[]=$data;
	    		}
	    	}
    	} else {
	     	// unset here so we dont upset the apple cart
	    	foreach ($baseResults as $key=>$result) {
	    		if (empty($baseResults[$key])) {
	    			unset($baseResults[$key]);
	    		}
	    	}
    	}
    }

    /**
     * Merge results together, ignore any not in base result set
     * @param $indResults
     * @param $baseResults - passed by reference
     */
    private function mergeResults($indResults,&$baseResults)
    {
    	if ($this->_debug) {
    		Mage::helper('wsalogger/log')->postDebug('productmatrix','merge result to add',$indResults);
    		Mage::helper('wsalogger/log')->postDebug('productmatrix','merge base results',$baseResults);
    	}
    	foreach ($baseResults as $key=>$result)
    	{
    		$found=false;
    		foreach ($indResults as $indKey=>$data) {
    			if ($result['delivery_type']==$data['delivery_type']) {
					$baseResults[$key]['found']=true;
					$indResults[$indKey]['found']=true;
    				if ($baseResults[$key]['max_price']<$data['max_price']) {
						$baseResults[$key]['max_price']=$data['max_price'];
					}
                    if (preg_match('/c=/',$result['algorithm'])){
                            if(preg_match('/alt/',$result['algorithm'])){
                                $baseResults[$key]['method_name'] = $data['method_name'];
                        }
                    }

                    if(preg_match('/alt/',$result['algorithm'])){
                        $baseResults[$key]['notes'] = $data['notes'];
                    }

    				if ($baseResults[$key]['override']) {
    					$found=true;
    					break;
    				} else if ($data['override']) {
    					$baseResults[$key]['price']=$data['price'];
    					$baseResults[$key]['override']=true;
    					$baseResults[$key]['package_id']=$baseResults[$key]['package_id'].",".$data['package_id'];
						$found=true;
						break;
    				} else {
						// add to existing
						$baseResults[$key]['price']+=$data['price'];
						$baseResults[$key]['package_id']=$baseResults[$key]['package_id'].",".$data['package_id'];
						$found=true;
						break;
    				}
				}
    		}
    		if (!$found && !$baseResults[$key]['showall']) {  // no match
    			if ( !$this->_starIncludeAll) {
    				if ($this->_debug) {
						Mage::helper('wsalogger/log')->postInfo('productmatrix','Delivery Type - No Match Found - Removing',$baseResults[$key]['delivery_type']);
					}
    				$baseResults[$key]="";
    			} else {
    				if ($result['package_id']!="" && count($this->_structuredItems)>1 && $indResults[0]['package_id']!="" && !$baseResults[$key]['showall'] ) {
    					if ($this->_debug) {
							Mage::helper('wsalogger/log')->postInfo('productmatrix','Delivery Type - No Match Found - Removing',$baseResults[$key]['delivery_type']);
						}
    					$baseResults[$key]="";
    				}
    			}
    		}
    	}
    	if ($this->_starIncludeAll) {
    	   	// check for missing
	    	foreach ($indResults as $key=>$data) {
	    		if (empty($data['found']) && $data['package_id']=="") {
	    			$indResults[$key]['found']=true;
	    			$baseResults[]=$data;
	    		}
	    	}

	    	// this was changed to be ==package_id from != - reason unclear
	    	if ($this->_appendStarRates) {
		    	foreach ($baseResults as $key=>$result) {
		    		if ($result!="" && !$baseResults[$key]['showall'] && $this->_hasEmptyPackages && $result['package_id']=="") {
		    			if ($this->_debug) {
							Mage::helper('wsalogger/log')->postInfo('productmatrix','Delivery Type - No Match Found - Removing',$baseResults[$key]['delivery_type']);
						}
	    				$baseResults[$key]="";
		    		}
		    	}
	    	} else {
		    	foreach ($baseResults as $key=>$result) {
		    		if ($result!=""  && !$baseResults[$key]['showall'] && (!isset($result['found']) || !$result['found'])) {
		    			if ($this->_debug) {
							Mage::helper('wsalogger/log')->postInfo('productmatrix','Delivery Type - No Match Found - Removing',$baseResults[$key]['delivery_type']);
						}
	    				$baseResults[$key]="";
		    		}
		    	}
	    	}
    	}

    	foreach ($indResults as $result)
    	{
    		if ($result['showall'] && (!array_key_exists('found',$result) || !$result['found'])) {
    			$baseResults[]=$result;
    		}
    	}
    	// unset here so we dont upset the apple cart
    	foreach ($baseResults as $key=>$result) {
    		if (empty($baseResults[$key])) {
    			unset($baseResults[$key]);
    		}
    	}
    }

    private function mergeOrdered($indResults,&$baseResults) {

   		//this also supports sorting

   		if (!array_key_exists('order',$indResults[0]) || !array_key_exists('order',$baseResults[0])) {
    		return;
    	}
    	// just take the first
    	$order=$indResults[0]['order'];
    	if ($order<$baseResults[0]['order']) {
    		reset($baseResults);
    		$baseResults=$indResults;
    	}


    }

    private function mergeLowest($indResults,&$baseResults)
    {

    	foreach ($baseResults as $key=>$result)
    	{
    	   	$found=false;
    		foreach ($indResults as $indKey=>$data) {
    			if ($result['delivery_type']==$data['delivery_type']) {
					// if lower get lower
					if ($data['price']<$baseResults[$key]['price']) {
						$baseResults[$key]['price']=$data['price'];
					}
					$indResults[$indKey]['found']=true;
					$found=true;
					break;
				}
    		}
    		if (!$found && !$this->_starIncludeAll) {
    			// no match so remove
    			$baseResults[$key]="";
    		}
    	}

    	if ($this->_starIncludeAll) {
    	   	// check for missing
	    	foreach ($indResults as $data) {
	    		if (empty($data['found'])) {
	    			$baseResults[]=$data;
	    		}
	    	}
    	} else {
	    	// unset here so we dont upset the apple cart
	    	foreach ($baseResults as $key=>$result) {
	    		if (empty($baseResults[$key])) {
	    			unset($baseResults[$key]);
	    		}
	    	}
    	}
    }
	private function runSelectStmt($read,$structuredItem,$searchPOBox,$filterCanada,$zipBreakdown,&$errorText)
	{
		$conditionName=$this->_request->getPMConditionName();
        $select = null;
        $weightOnlySelect = null;

        if ($this->_twoPhaseFiltering) {
			$switchSearches=14;
		} else {
			$switchSearches=10;
		}

		for ($j=0;$j<$switchSearches;$j++) {
			$select = $this->getSwitchSelect($read,$j,$searchPOBox);
            $addOr = false;

			if ($structuredItem['package_id']=='include_all' || $structuredItem['package_id']=='none') {
				$select->where('package_id=?','');
			} else {
				$select->where('package_id=?', $structuredItem['package_id']);
			}

            $totalVolweight = $structuredItem['volume'];

            if ($this->_usingGreaterVolLogic && $this->_volume) {
                if( $structuredItem['volume'] > $structuredItem['weight'] ) {
                    $this->_greaterVolume = true;
                    $select->where('weight_from_value<?', $totalVolweight);
                    $select->where('weight_to_value>=?', $totalVolweight);
                    $select->where('volume_from_value<?', $structuredItem['volume']);
                    $select->where('volume_to_value>=?', $structuredItem['volume']);

                    if (count($this->_actualWeightMethodArr)) {
                        foreach ($this->_actualWeightMethodArr as $method) {
                            $select->where('delivery_type<>?', $method);
                        }
                        $addOr = true;
                    }
                } else {
                    $select->where('weight_from_value<?', $structuredItem['weight']);
                    $select->where('weight_to_value>=?', $structuredItem['weight']);
                    $select->where('volume_from_value<?', $structuredItem['volume']);
                    $select->where('volume_to_value>=?', $structuredItem['volume']);
                }
            } else {
                $select->where('weight_from_value<?', $structuredItem['weight']);
                $select->where('weight_to_value>=?', $structuredItem['weight']);

                if($this->_volume) {
                    $select->where('volume_from_value<?', $structuredItem['volume']);
                    $select->where('volume_to_value>=?', $structuredItem['volume']);
                }
            }

			$select->where('price_from_value<?', $structuredItem['price']);
			$select->where('price_to_value>=?', $structuredItem['price']);
			$select->where('item_from_value<?', $structuredItem['qty']);
			$select->where('item_to_value>=?', $structuredItem['qty']);

			$groupArr[0]="STRCMP(LOWER(customer_group),LOWER('".$this->_customerGroupCode."')) =0";
			$groupArr[1]="customer_group=''";
			$select->where(join(' OR ', $groupArr));

			$select->where('website_id=?', $this->_request->getWebsiteId());

			$select->order('notes ASC');
			$select->order('price ASC');
			$select->order('algorithm ASC');

            if($addOr) {
                $weightOnlySelect = $this->getSwitchSelect($read,$j,$searchPOBox);

                $this->createVolumeSelect($weightOnlySelect, $structuredItem);
            }

            /*
            pdo has an issue. we cannot use bind
            */
            $outerRow = array();
            $weightOnlyRow = array();

			try {
				$outerRow = $read->fetchAll($select);
                if($addOr) {
                    $weightOnlyRow = $read->fetchAll($weightOnlySelect);
                }
			} catch (Exception $e) {
				Mage::helper('wsalogger/log')->postWarning('productmatrix','SQL Exception', $e->getMessage());
			}

            if($addOr) {
                $outerRow = array_merge($outerRow,$weightOnlyRow);
            }

			// should really do this in sql
			if (!empty($outerRow) && $filterCanada)  {
				if ($this->_debug) {
					Mage::helper('wsalogger/log')->postDebug('productmatrix','SQL Select Canada',$select->getPart('where'));
					Mage::helper('wsalogger/log')->postDebug('productmatrix','SQL Result Canada',$outerRow);
				}
				$row = array();
				foreach ($outerRow as $data) {
					$zipFromArry = $data['dest_zip'];
					$zipToArry = $data['dest_zip_to'];
					if (strlen($zipFromArry)!=3 || strlen($zipToArry)!=3) {
						$row[] = $data;
						continue;
					}
					if ($zipFromArry[1]==$zipBreakdown[1] && strcmp($zipBreakdown[2],$zipFromArry[2])<0) {
						// matches on first
						continue;
					}
					if ($zipToArry[1]==$zipBreakdown[1]) {
						if (strcmp($zipToArry[2],$zipBreakdown[2])<0) {
							continue;
						}
					}
					$row[] = $data;
				}
			} else {
				$row = $outerRow;
			}

			if (!empty($row)) {
				if ($this->_debug) {
					Mage::helper('wsalogger/log')->postDebug('productmatrix','SQL Select',$select->getPart('where'));
					Mage::helper('wsalogger/log')->postDebug('productmatrix','SQL Result',$row);
				}
				$newdata=array();
				$priorityData=array();
				foreach ($row as $data) {
					if ($data['price']==-1) {
                        $this->_exclusionList[] = $this->_addExclusion($data,$structuredItem,$errorText);
						continue;
					}

                    $this->_initData($data);
                    $priorityTypes = array();
                    if (!$this->_populateAlgorithm($data, $structuredItem, $conditionName, $priorityTypes)) {
                        continue;
                    }

					if ($conditionName=='per_product') {
						// for each unique product in basket for this package id (e.g. product A&B of package id Z)
						$data['price']=$data['price']*$structuredItem['unique'];
					} else if ($conditionName=='per_item' || $conditionName=='per_item_bare') {
						// foreach item in basket for this package_id (e.g. 3*product A of package id Z)
						if(!$data['set_cart']){
							$data['price']=$data['price']*$structuredItem['qty'];
						}
					}else if ($conditionName=='per_product_bare') {
						$data['price']=$data['price']*$structuredItem['unique'];
					}
					else if ($structuredItem['package_id']!='include_all' && $conditionName=='per_item_surcharge'  ) {
						$data['price']=$data['price']*$structuredItem['qty'];
					}

					if (count($priorityTypes)>0) {
						$data['priority']=0;
						$priorityNum=1;
						$this->_prioritySet=true;
						foreach ($priorityTypes as $priorityType) {
							$copyData = $data;
							$copyData['priority']=$priorityNum;
							$priorityNum++;
							$copyData['delivery_type']=$priorityType;
							$copyData['method_name']=$priorityType;
							$priorityData[]=$copyData;
						}

					}

					$newdata[]=$data;
				}
				if (count($priorityData)>0) {
					$newdata = array_merge($newdata, $priorityData);
				}
				if (!empty($newdata)) {
    				return $newdata;
				} else {
					return null;
				}
			}
		}
        return null;
	}

    /**
     * Creates a new select statement for shipping methods that must always use the actual weight
     * @param $select
     * @param $structuredItem
     */
    private function createVolumeSelect(&$select, $structuredItem)
    {
        if ($structuredItem['package_id']=='include_all' || $structuredItem['package_id']=='none') {
            $select->where('package_id=?','');
        } else {
            $select->where('package_id=?', $structuredItem['package_id']);
        }

        $select->where('weight_from_value<?', $structuredItem['weight']);
        $select->where('weight_to_value>=?', $structuredItem['weight']);
        $select->where('delivery_type IN(?)', $this->_actualWeightMethodArr);
        $select->where('price_from_value<?', $structuredItem['price']);
        $select->where('price_to_value>=?', $structuredItem['price']);
        $select->where('item_from_value<?', $structuredItem['qty']);
        $select->where('item_to_value>=?', $structuredItem['qty']);

        $groupArr[0]="STRCMP(LOWER(customer_group),LOWER('".$this->_customerGroupCode."')) =0";
        $groupArr[1]="customer_group=''";
        $select->where(join(' OR ', $groupArr));

        $select->where('website_id=?', $this->_request->getWebsiteId());

        $select->order('notes ASC');
        $select->order('price ASC');
        $select->order('algorithm ASC');
    }

    protected function _initData(&$data)
    {
        $data['priority']=0;
        $data['multiprice']="";
        $data['additional_price']=0;
        $data['qty']=0;
        $data['max_price']=-1;
        $data['override']=false;
        $data['showall']=false;
        $data['set_cart']=false;
        $data['method_name']=$data['delivery_type'];
    }

    protected function _addExclusion(&$data, $structuredItem, &$errorText ) {

        $errorText = '';
        if ($data['algorithm']!="") {
            $algorithm_array=explode("&",$data['algorithm']);  // Multi-formula extension
            foreach ($algorithm_array as $algorithm_single) {
                $algorithm=explode("=",$algorithm_single,2);
                if (!empty($algorithm) && count($algorithm)==2) {
                    $algKey = strtolower($algorithm[0]);
                    $algValue = $algorithm[1];

                    switch ($algKey) {
                        case "error":
                            $errorText = $algValue;
                            break;

                    }
                }
            }
        }

        $exclusionItem=array ( 'package_id'     => $structuredItem['package_id'],
							   'delivery_type'  => $data['delivery_type']);
        $this->_minusOne=true;

		return $exclusionItem;
    }

    protected function _populateAlgorithm(&$data, $structuredItem, $conditionName, &$priorityTypes)
    {
        $decrease = -1;
        if ($data['algorithm']!="") {
            $algorithm_array=explode("&",$data['algorithm']);  // Multi-formula extension
            reset($algorithm_array);
            $skipData=false;
            foreach ($algorithm_array as $algorithm_single) {
                $algorithm=explode("=",$algorithm_single,2);
                if (!empty($algorithm) && count($algorithm)==2) {
                    $algKey = strtolower($algorithm[0]);
                    $algValue = $algorithm[1];

                    switch ($algKey) {
                        case "w":
                            // weight based
                            $weightIncrease=explode("@",$algValue);
                            if (!empty($weightIncrease) && count($weightIncrease)==2 ) {
                                if($data['weight_from_value'] == -1 ){$data['weight_from_value'] = 0;}
                                if($data['volume_from_value'] == -1 ){$data['volume_from_value'] = 0;}

                                if ($this->_greaterVolume && $this->_volume) {
                                    $weightDifference =	$structuredItem['volume']-$data['volume_from_value'];
                                } else {
                                    $weightDifference =	$structuredItem['weight']-$data['weight_from_value'];
                                }

                                $quotient = $weightDifference / $weightIncrease[0];
                                $data['price'] = $data['price'] + $weightIncrease[1]*$quotient;
                            }
                            break;
                        case "wa":
                            $weightIncrease=explode("@",$algValue);
                            if (!empty($weightIncrease) && count($weightIncrease)==2 ) {
                                $weight= $structuredItem['weight'];
                                $quotient=$weight / $weightIncrease[0];
                                $data['price']=$data['price']+$weightIncrease[1]*$quotient;
                            }
                            break;
                        case "wc":
                            // weight based
                            $weightIncrease=explode("@",$algValue);
                            if (!empty($weightIncrease) && count($weightIncrease)==2 ) {
                                if($data['weight_from_value'] == -1 ){$data['weight_from_value'] = 0;}
                                $weightDifference=	$structuredItem['weight']-$data['weight_from_value'];
                                $quotient=ceil($weightDifference / $weightIncrease[0]);
                                $data['price']=$data['price']+$weightIncrease[1]*$quotient;
                            }
                            break;
                        case "wca":
                            // weight based
                            $weightIncrease=explode("@",$algValue);
                            if (!empty($weightIncrease) && count($weightIncrease)==2 ) {
                                $weight= $structuredItem['weight'];
                                $quotient=ceil($weight / $weightIncrease[0]);
                                $data['price']=$data['price']+$weightIncrease[1]*$quotient;
                            }
                            break;
                        case "vwc":
                            // volume and weight based
                            if ($this->_greaterVolume) {
                                if ($structuredItem['volume'] > $structuredItem['weight']){
                                    $weightIncrease=explode("@",$algorithm[1]);
                                    if (!empty($weightIncrease) && count($weightIncrease)==2 ) {
                                        $weightDifference=	$structuredItem['volume']-$data['volume_from_value'];
                                        $quotient=ceil($weightDifference / $weightIncrease[0]);
                                        $data['price']=$data['price']+$weightIncrease[1]*$quotient;}
                                } else {
                                    $weightIncrease=explode("@",$algorithm[1]);
                                    if (!empty($weightIncrease) && count($weightIncrease)==2 ) {
                                        $weightDifference =	$structuredItem['weight']-$data['weight_from_value'];
                                        $quotient=ceil($weightDifference / $weightIncrease[0]);
                                        $data['price']=$data['price']+$weightIncrease[1]*$quotient;}
                                }
                            } else {
                                $weightIncrease=explode("@",$algorithm[1]);
                                if (!empty($weightIncrease) && count($weightIncrease)==2 ) {
                                    $weightDifference=	$structuredItem['weight']-$data['weight_from_value'];
                                    $quotient=ceil($weightDifference / $weightIncrease[0]);
                                    $data['price']=$data['price']+$weightIncrease[1]*$quotient;}
                            }
                            break;
                        case "p":
                            $this->_prioritySet=true;
                            $data['priority']=$algValue;
                            break;
                        case "alt":
                            $priorityTypes=explode(",",$algValue);
                            break;
                        case "m":
                            $this->_maxPriceSet = true;
                            $data['max_price']=$algValue;
                            break;
                        case "i":
                            if (strtolower($algValue)=='ignore') {
                                $this->_ignoreAdditionalItemPrice=true;
                            } else {
                                if ($conditionName=='per_package') {
                                    $data['price']+=$algValue*($structuredItem['qty']-$data['item_from_value']);
                                } else {
                                    $data['multiprice']=$algValue;
                                    $data['qty']=$structuredItem['qty'];
                                }
                            }
                            break;
                        case "io":
                            if ($conditionName=='per_package') {
                                $data['price']+=$algValue*($structuredItem['qty']-$data['item_from_value']);
                            } else {
                                $data['multiprice']=$algValue;
                                $data['qty']=1;
                            }
                            break;
                        case "im":
                            $itemIncrease=explode("@",$algValue);
                            if (!empty($itemIncrease) && count($itemIncrease)==2 ) {
                                $qty=$structuredItem['qty'];
                                $quotient=ceil($qty / $itemIncrease[0]);
                                if ($conditionName=='per_package') {
                                    $data['price']=$data['price']+$itemIncrease[1]*$quotient;
                                } else {
                                    $data['multiprice']=$data['price']+$itemIncrease[1]*$quotient;
                                    $data['qty']=$structuredItem['qty'];
                                }
                            }
                            break;
                        case "ai":
                            if ($conditionName=='per_package') {
                                $data['price']+=$algValue*($structuredItem['qty']);
                            } else {
                                $data['multiprice']=$algValue;
                                $data['qty']=$structuredItem['qty'];
                            }
                            break;
                        case "a":
                            if ($this->_request->getUpsDestType()=='RES') {
                                if ($algValue!='residential') {
                                    $skipData = true;
                                    break;
                                }
                            } else {
                                if ($algValue!='commercial') {
                                    $skipData = true;
                                    break;
                                }
                            }
                            break;
                        case "o":
                            $data['order']=$algValue;
                            break;
                        case "%":
                            $perSplit=explode("+",$algValue);
                            if (!empty($perSplit) && count($perSplit)==2) {
                                $percentage = $perSplit[0];
                                $flatAdd = $perSplit[1];
                            } else {
                                $percentage = $algValue;
                                $flatAdd = 0;
                            }
                            $percPrice=($structuredItem['price']*$percentage/100)+$flatAdd;
                            if ($percPrice>$data['price']) {
                                $data['price']=$percPrice;
                            }
                            break;
                        case "r":
                            $decrease=$algValue;
                            break;
                        case "c":
                            $data['method_name']=$algValue;
                            break;
                        case "setcart":
                            $data['set_cart']=$algValue;
                            break;
                        case "instock":
                            if (strtolower($algValue)=="true") {
                                if (!$this->_stockFound) {
                                    if ($this->checkOutOfStock($this->_request)) {
                                        $skipData = true;
                                        break;
                                    }
                                } else {
                                    if ($this->_outofstock) {
                                        $skipData = true;
                                        break;
                                    }
                                }
                            } elseif  (strtolower($algValue)=="false"){
                                if (!$this->_stockFound) {
                                    if (!$this->checkOutOfStock($this->_request)) {
                                        $skipData = true;
                                        break;
                                    }
                                } else {
                                    if (!$this->_outofstock) {
                                        $skipData = true;
                                        break;
                                    }
                                }
                            }
                            break;
                    }
                }
                switch ($algorithm_single) {
                    case "OVERRIDE":
                        $data['override']=true;
                        break;
                    case "OVERRIDE_SINGLE":
                        if (count($this->_structuredItems)<3) {
                            $data['override']=true;
                        }
                        break;
                    case "SHOWALL":
                        $data['showall']=true;
                        break;
                }

            }
            if ($skipData) { return false; }
            if ($decrease>-1) {
                $data['price'] = $data['price']-$decrease;
                if ($data['price']<0) { $data['price']=0; }
            }
        }
        return true;
    }

	private function checkOutOfStock($request) {
		$items = $request->getAllItems();
    	foreach($items as $item) {
    		if ($item->getBackorders() != Mage_CatalogInventory_Model_Stock::BACKORDERS_NO) {
    			$this->_outofstock=true;
    		}
    	}
    	$this->_stockFound=true;
    	return $this->_outofstock;
    }

	private function getSwitchSelect($read,$j,$searchPOBox) {

		if ($searchPOBox) {
			$destCity='POBOX';
		} else {
			$destCity=$this->_request->getDestCity();
		}

		//$select = $read->select()->from($table);
		$select = $read->select()->from(array('productmatrix'=>$this->_table),
						array(	'pk'=>'pk',
								'price'=>'price',
								'delivery_type'=>'delivery_type',
								'package_id'=>'package_id',
								'weight_from_value'=>'weight_from_value',
                                'volume_from_value'=>'volume_from_value',
								'dest_zip'=>'dest_zip',
								'dest_zip_to'=>'dest_zip_to',
								'item_from_value'=>'item_from_value',
								'algorithm'=>'algorithm',
								'notes'=>'notes',
								'cost'=>'cost'));
		//UNIRGY-BEGIN
		if($this->_uDrop){
			$select->where("udropship_vendor IS NULL OR udropship_vendor in (0,?)", $this->_request->getVendorId());
			if($this->_debug) {
                Mage::helper('wsalogger/log')->postDebug('productmatrix','Detected Vendor Id',$this->_request->getVendorId());
			}
		}
		//UNIRGY-END

		if ($this->_twoPhaseFiltering) {
			switch($j) {
				case 0:
					$select->where(
						$read->quoteInto(" (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND dest_region_id=? ", $this->_request->getDestRegionId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  ", $destCity).
    						$read->quoteInto(" AND STRCMP(LOWER(dest_zip),LOWER(?)) = 0)", $this->_longMatchPostcode)
						);
					break;
				case 1:
					$select->where(
						$read->quoteInto(" (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND dest_region_id=? ", $this->_request->getDestRegionId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  ", $destCity).
							$this->_shortMatchPostcode
					);
					break;
				case 2:
					$select->where(
						$read->quoteInto(" (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND dest_region_id=?  AND dest_city=''", $this->_request->getDestRegionId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_zip),LOWER(?)) = 0 )", $this->_longMatchPostcode)
						);
					break;
				case 3:
					$select->where(
						$read->quoteInto(" (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND dest_region_id=?  AND dest_city=''", $this->_request->getDestRegionId()).
    						$this->_shortMatchPostcode
					);
					break;
				case 4:
					$select->where(
						$read->quoteInto(" (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND dest_region_id=? ", $this->_request->getDestRegionId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_zip='')", $destCity)
						);
					break;
				case 5:
					$select->where(
					   $read->quoteInto("  (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_region_id='0'", $destCity).
							$read->quoteInto(" AND STRCMP(LOWER(dest_zip),LOWER(?)) = 0 )", $this->_longMatchPostcode)
					   );
					break;
				case 6:
					$select->where(
					   $read->quoteInto("  (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_region_id='0'", $destCity).
							$this->_shortMatchPostcode
					   );
					break;
				case 7:
					$select->where(
					   $read->quoteInto("  (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_region_id='0' AND dest_zip='') ", $destCity)
					   );
					break;
				case 8:
					$select->where(
						$read->quoteInto("  (dest_country_id=? AND dest_region_id='0' AND dest_city='' ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_zip),LOWER(?)) = 0 )", $this->_longMatchPostcode)
							);
					break;
				case 9:
					$select->where(
						$read->quoteInto("  (dest_country_id=? AND dest_region_id='0' AND dest_city='' ", $this->_request->getDestCountryId()).
							$this->_shortMatchPostcode
						);
					break;
				case 10:
					$select->where(
					   $read->quoteInto("  (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND dest_region_id=? AND dest_city='' AND dest_zip='') ", $this->_request->getDestRegionId())
					   );
					break;

				case 11:
					$select->where(
					   $read->quoteInto("  (dest_country_id=? AND dest_region_id='0' AND dest_city='' AND dest_zip='') ", $this->_request->getDestCountryId())
					);
					break;

				case 12:
					$select->where(
					   $read->quoteInto("  (dest_country_id='0' AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_region_id='0' AND dest_zip='') ", $destCity)
					   );
					break;

				case 13:
					$select->where(
							"  (dest_country_id='0' AND dest_region_id='0' AND dest_city='' AND dest_zip='')"
				);
					break;
			}

		} else {
			switch($j) {
				case 0:
					$select->where(
						$read->quoteInto(" (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND dest_region_id=? ", $this->_request->getDestRegionId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  ", $destCity).
							$this->_zipSearchString
						);
					break;
				case 1:
					$select->where(
						$read->quoteInto(" (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND dest_region_id=?  AND dest_city=''", $this->_request->getDestRegionId()).
							$this->_zipSearchString
						);
					break;
				case 2:
					$select->where(
						$read->quoteInto(" (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND dest_region_id=? ", $this->_request->getDestRegionId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_zip='')", $destCity)
						);
					break;
				case 3:
					$select->where(
					   $read->quoteInto("  (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_region_id='0'", $destCity).
							$this->_zipSearchString
					   );
					break;
				case 4:
					$select->where(
					   $read->quoteInto("  (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_region_id='0' AND dest_zip='') ", $destCity)
					   );
					break;
				case 5:
					$select->where(
						$read->quoteInto("  (dest_country_id=? AND dest_region_id='0' AND dest_city='' ", $this->_request->getDestCountryId()).
							$this->_zipSearchString
						);
					break;
				case 6:
					$select->where(
					   $read->quoteInto("  (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND dest_region_id=? AND dest_city='' AND dest_zip='') ", $this->_request->getDestRegionId())
					   );
					break;

				case 7:
					$select->where(
					   $read->quoteInto("  (dest_country_id=? AND dest_region_id='0' AND dest_city='' AND dest_zip='') ", $this->_request->getDestCountryId())
					);
					break;

				case 8:
					$select->where(
					   $read->quoteInto("  (dest_country_id='0' AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_region_id='0' AND dest_zip='') ", $destCity)
					   );
					break;

				case 9:
					$select->where(
						"  (dest_country_id='0' AND dest_region_id='0' AND dest_city='' AND dest_zip='')"
					);
					break;
			}
		}
		return $select;
	}

    private function setupUseParentGlobals()
    {
        switch ($this->_useParent) {
            case "none":
                $this->_useBundleParent = false;
                $this->_useConfigurableParent = false;
                break;
            case "both":
                $this->_useBundleParent = true;
                $this->_useConfigurableParent = true;
                break;
            case "bundle":
                $this->_useBundleParent = true;
                $this->_useConfigurableParent = false;
                break;
            case "configurable":
                $this->_useBundleParent = false;
                $this->_useConfigurableParent = true;
                break;
            default:
                $this->_useBundleParent = false;
                $this->_useConfigurableParent = false;
                break;
        }
    }

	private function populateStructuredItems($items, $conditionName)
	{
		$specialPrice = 0;
		$specialWeight = 0;

		if ($this->_debug) {
			Mage::helper('wsalogger/log')->postDebug('productmatrix','Settings','Use Parent:'.$this->_useParent);
		}

		$filterPrice = in_array('filter_subtotal',$this->_options);
		$useDiscountValue = in_array('use_discounted',$this->_options);
		$splitCustom = in_array('split_custom',$this->_options);
		$useBase = in_array('use_base',$this->_options);
		$useTax = in_array('use_tax',$this->_options);
		$runningCartPrice = 0;
		$includeVirtual = !in_array('remove_virtual',$this->_options);
        $volumeMultipler = Mage::getStoreConfig("carriers/productmatrix/volume_multiplier");
        $totalVolumeWeight = 0;

        $version = Mage::helper('wsacommon')->getVersion();
		$freeship=$this->_request->getIgnoreFreeItems();

		if  ($version == 1.6 || $version == 1.7 || $version == 1.8) {
			// freemethodweight not supported. Do what we did before
			$freeship=true;
		}

		foreach($items as $item) {

			$weight=0;
			$qty=0;
			$price=0;
			$temp='';
            $product=null;

			if($item->getProduct()->isVirtual()){
				if (!Mage::helper('wsacommon/shipping')->getVirtualItemTotals($item, $weight,$qty,$price,$this->_useBundleParent,
					$freeship,$temp,$useDiscountValue, false, $useBase, $useTax, $includeVirtual)) {
						continue;
				}
			}
			else {
				if (!Mage::helper('wsacommon/shipping')->getItemTotals($item, $weight,$qty,$price,$this->_useBundleParent,
					$freeship,$temp,$useDiscountValue, false, $useBase, $useTax)) {
						continue;
				}
			}

			$runningCartPrice += $price;

			if ($item->getParentItem()!=null &&
				$this->_useBundleParent &&
                $item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE ) {
					// must be a bundle/configurable
                    $product = $item->getParentItem()->getProduct();
			} else if ( $item->getParentItem()!=null &&  $this->_useConfigurableParent &&
                        $item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE ) {
                    $product = $item->getParentItem()->getProduct();
            } else if ( $item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE && !$this->_useConfigurableParent ) {
				if ($item->getHasChildren()) {
                	foreach ($item->getChildren() as $child) {
                        $product = $child->getProduct();
                        break;
                	}
				}
			} else {
                $product = $item->getProduct();
			}

			// if find a surcharge check to see if it is on the item or the order
			// if on the order then add to the surcharge_order_price if > than previous
			// if on the item then multiple by qty and add to the surcharge_price

			if (!is_object($product)) {
				$this->_structuredItems[] = "";
				Mage::helper('wsalogger/log')->postCritical('productmatrix','Fatal Error','Item/Product is Malformed');
				return;
			}

            $shipQty=$product->getData('shipping_qty');
            $freightQtyThreshold=$product->getData('freight_qty_threshold');

			if ($shipQty=="" || !is_numeric($shipQty)) {
				$shipQty=1;
			}

            $height = $product->getData('ship_height');
            $width = $product->getData('ship_width');
            $depth = $product->getData('ship_depth');

            if ($height == "" || !is_numeric($height)||
                 $width == "" || !is_numeric($width) ||
                 $depth == "" || !is_numeric($depth) ||
                !$this->_volume
            ) {
                $volumeWeight = 0;
            } else {
                $volumeWeight = $height * $width * $depth * $volumeMultipler;
            }

            $totalVolumeWeight += $volumeWeight*$qty;

			if (in_array('group_text',$this->_options)) {
				$packageId = $product->getData('package_id');
			} else {
				$packageId = $product->getAttributeText('package_id');
			}

            if (empty($packageId) && is_numeric($freightQtyThreshold) && $qty>=$freightQtyThreshold) {
                $packageId = 'SHIP_FREIGHT_AUTO';
            }

			if (empty($packageId)) { $packageId='none'; $this->_hasEmptyPackages=true; }

			if ($splitCustom) {
				$options = Mage::helper('catalog/product_configuration')->getCustomOptions($item);
				if (!empty($options)  && is_array($options[0])) {
					$packageId .= '-OPTIONS';
				}
			}

			$found = false;


			if ($conditionName=='per_item_bare' || $conditionName=='per_item_surcharge' ||  $conditionName=='per_product_bare') {
				$prodArray=array( 'package_id'  => $packageId,
						  'qty' 				=> $qty,
						  'weight'				=> $weight/$qty,
                          'volume'		        => $volumeWeight,
						  'price'				=> $price/$qty,
						  'unique'		 		=> 1);
				$this->_structuredItems[]=$prodArray;

			} else {

				foreach($this->_structuredItems as $key=>$structuredItem) {
					if ($structuredItem['package_id']==$packageId) {
						// have already got this package id
						$this->_structuredItems[$key]['qty']=$this->_structuredItems[$key]['qty']+$qty*$shipQty;
						$this->_structuredItems[$key]['weight']=$this->_structuredItems[$key]['weight']+ $weight;
                        $this->_structuredItems[$key]['volume']=$this->_structuredItems[$key]['volume']+ $volumeWeight*$qty;
						$this->_structuredItems[$key]['price']=$this->_structuredItems[$key]['price']+ $price;
						$this->_structuredItems[$key]['unique']+=1;
						$found=true;
						break;
					}
				}

				if (!$found){
					$prodArray=array( 'package_id'  => $packageId,
							  'qty' 				=> $qty*$shipQty,
							  'weight'				=> $weight,
                              'volume'				=> $volumeWeight*$qty,
							  'price'				=> $price,
					          'unique'				=> 1);
					$this->_structuredItems[]=$prodArray;

				}
			}
			// also add to include_all package Id
			if ($this->_starIncludeAll ) {
				if ($packageId=="SPECIAL_FREE") {
					$specialPrice += $price;
					$specialWeight += $weight;
				} else {
					$found=false;
					if ($useDiscountValue) {
						$groupPrice = $runningCartPrice;
					} else {
						$groupPrice = $price;
					}
					foreach($this->_structuredItems as $key=>$structuredItem) {
						if ($structuredItem['package_id']=='include_all') {
							$this->_structuredItems[$key]['qty']=$this->_structuredItems[$key]['qty']+$qty*$shipQty;
							$this->_structuredItems[$key]['weight']=$this->_structuredItems[$key]['weight']+ $weight;
                            $this->_structuredItems[$key]['volume']=$this->_structuredItems[$key]['volume']+ $volumeWeight*$qty;
							if (!$useDiscountValue) {
								$this->_structuredItems[$key]['price']=$this->_structuredItems[$key]['price']+ $price;
							}
							$this->_structuredItems[$key]['unique']+=1;
							$found=true;
							break;
						}
					}
					if (!$found) {
						$prodArray=array( 'package_id'  => 'include_all',
						  'qty' 				=> $qty*$shipQty,
						  'weight'				=> $weight,
                          'volume'		        => $volumeWeight*$qty,
						  'price'				=> $groupPrice,
						  'unique'				=> 1);
						$this->_structuredItems[]=$prodArray;
					}
				}
			}
		}

		if ($filterPrice) {
		    if(Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Dropship','carriers/dropship/active')) {
		        if(Mage::getStoreConfig('carriers/dropship/use_cart_price')) {
		            $runningCartPrice = $this->_request->getPackageValue();
		        }
		    }
		    foreach($this->_structuredItems as $key=>$structuredItem) {
		        $this->_structuredItems[$key]['price'] = $runningCartPrice - $specialPrice;
		        $this->_structuredItems[$key]['weight'] = $this->_request->getPackageWeight() - $specialWeight;
                $this->_structuredItems[$key]['volume'] = $totalVolumeWeight;
		    }
		}
		if ($this->_debug) {
			Mage::helper('wsalogger/log')->postDebug('productmatrix','Structured Items',$this->_structuredItems);
		}
	}


    /**
     * CSV Import routine
     * @param Varien_Object $object
     * @throws Exception
     */
    public function uploadAndImport(Varien_Object $object)
    {
        $csvFile = $_FILES["groups"]["tmp_name"]["productmatrix"]["fields"]["import"]["value"];
        $csvName = $_FILES["groups"]["name"]["productmatrix"]["fields"]["import"]["value"];
        $dataStored = false;
		$session = Mage::getSingleton('adminhtml/session');
		$this->_uDrop = Mage::getConfig()->getNode('productmatrix/specialvars/udrop') == 1;
		$this->_uDrop ? $offset = 1 : $offset = 0;
        $this->_volume = Mage::getConfig()->getNode('productmatrix/specialvars/volume') == 1;
        $this->_volume ? $volumeOffset = 2 : $volumeOffset = 0;

        if (!empty($csvFile)) {

            $websiteId = $object->getScopeId();

            $csv = trim(file_get_contents($csvFile));

			Mage::helper('wsacommon/shipping')->saveCSV($csv, $csvName, $websiteId, 'productmatrix');

            $table = Mage::getSingleton('core/resource')->getTableName('productmatrix_shipping/productmatrix');

            if (!empty($csv)) {
                $exceptions = array();
                $csvLines = explode("\n", $csv);
                $csvLine = array_shift($csvLines);
                $csvLine = $this->_getCsvValues($csvLine);

                $this->validateCsvSize(count($csvLine),$exceptions);

                $countryCodes = array();
                $regionCodes = array();
                foreach ($csvLines as $csvLine) {
                    $csvLine = $this->_getCsvValues($csvLine);
                    if (count($csvLine) > 0 && count($csvLine) < 15) {
                        $exceptions[0] = Mage::helper('shipping')->__('Invalid Product Matrix File Format %s',$csvLine);
                    } else {
                        $splitCountries = explode(",", trim($csvLine[0]));
                    	$splitRegions = explode(",", trim($csvLine[1]));
                        foreach ($splitCountries as $country) {
                        	$countryCodes[] = trim($country);
                    	}
                    	foreach ($splitRegions as $region) {
                        	$regionCodes[] = $region;
                    	}
                   	}
                }

             	if (empty($exceptions)) {
                    $connection = $this->_getWriteAdapter();

                     $condition = array(
                        $connection->quoteInto('website_id = ?', $websiteId),
                    );
                    $connection->delete($table, $condition);
                }

                if (!empty($exceptions)) {
                    throw new Exception( "\n" . implode("\n", $exceptions) );
                }


                if (empty($exceptions)) {
                	$data = array();
                    $countryCodesToIds = array();
                    $regionCodesToIds = array();
                    $countryCodesIso2 = array();
                    $counter=0;

                    $countryCollection = Mage::getResourceModel('directory/country_collection')->addCountryCodeFilter($countryCodes)->load();
                    foreach ($countryCollection->getItems() as $country) {
                        $countryCodesToIds[$country->getData('iso3_code')] = $country->getData('country_id');
                        $countryCodesToIds[$country->getData('iso2_code')] = $country->getData('country_id');
                        $countryCodesIso2[] = $country->getData('iso2_code');
                    }

                    $regionCollection = Mage::getResourceModel('directory/region_collection')
                        ->addRegionCodeFilter($regionCodes)
                        ->addCountryFilter($countryCodesIso2)
                        ->load();

                    foreach ($regionCollection->getItems() as $region) {
                        $regionCodesToIds[$countryCodesToIds[$region->getData('country_id')]][$region->getData('code')] = $region->getData('region_id');
                    }

                    foreach ($csvLines as $k=>$csvLine) {
                        $csvLine = $this->_getCsvValues($csvLine);
                        $splitCountries = explode(",", trim($csvLine[0]));
                        $splitRegions = explode(",", trim($csvLine[1]));
                        $splitPostcodes = explode(",",strtoupper(trim($csvLine[3])));
                        $splitCities = explode(",",strtoupper(trim($csvLine[2])));
                        $customerGroups = explode(",",trim($csvLine[12+$offset+$volumeOffset]));
                        $this->_uDrop ? $splitVendor = explode(",",trim($csvLine[6])) : $splitVendor = array(1);

						if ($csvLine[4] == '*' || $csvLine[4] == '') {
							$zip_to = '';
						} else {
							$zip_to = strtoupper(trim($csvLine[4]));
						}


						if ($csvLine[5] == '*' || $csvLine[5] == '') {
							$package_id = '';
						} else {
							$package_id = $csvLine[5];
						}

						//Add offset from here on. The vendor id should be on column 6 if set
                    	if ( $csvLine[6+$offset] == '*' || $csvLine[6+$offset] == '') {
							$weight_from = -1;
						} else if (!$this->_isPositiveDecimalNumber($csvLine[6+$offset])) {
							$exceptions[] = Mage::helper('shipping')->__('Invalid weight From "%s" in the Row #%s', $csvLine[6+$offset], ($k+1));
                    	} else {
							$weight_from = (float)$csvLine[6+$offset];
						}

						if ( $csvLine[7+$offset] == '*' || $csvLine[7+$offset] == '') {
							$weight_to = 10000000;
						} else if (!$this->_isPositiveDecimalNumber($csvLine[7+$offset])) {
							$exceptions[] = Mage::helper('shipping')->__('Invalid weight To "%s" in the Row #%s', $csvLine[7+$offset], ($k+1));
						}
						else {
							$weight_to = (float)$csvLine[7+$offset];
						}

						if ( $csvLine[8+$offset] == '*' || $csvLine[8+$offset] == '') {
							$price_from = -1;
						} else if (!$this->_isPositiveDecimalNumber($csvLine[8+$offset]) ) {
							$exceptions[] = Mage::helper('shipping')->__('Invalid price From "%s" in the Row #%s',  $csvLine[8+$offset], ($k+1));
						} else {
							$price_from = (float)$csvLine[8+$offset];
						}

						if ( $csvLine[9+$offset] == '*' || $csvLine[9+$offset] == '') {
							$price_to = 10000000;
						} else if (!$this->_isPositiveDecimalNumber($csvLine[9+$offset])) {
							$exceptions[] = Mage::helper('shipping')->__('Invalid price To "%s" in the Row #%s', $csvLine[9+$offset], ($k+1));
						} else {
							$price_to = (float)$csvLine[9+$offset];
						}

						if ( $csvLine[10+$offset] == '*' || $csvLine[10+$offset] == '') {
							$item_from = 0;
						} else if (!$this->_isPositiveDecimalNumber($csvLine[10+$offset]) ) {
							$exceptions[] = Mage::helper('shipping')->__('Invalid item From "%s" in the Row #%s',  $csvLine[10+$offset], ($k+1));
						} else {
							$item_from = (float)$csvLine[10+$offset];
						}

						if ( $csvLine[11+$offset] == '*' || $csvLine[11+$offset] == '') {
							$item_to = 10000000;
						} else if (!$this->_isPositiveDecimalNumber($csvLine[11+$offset])) {
							$exceptions[] = Mage::helper('shipping')->__('Invalid item To "%s" in the Row #%s', $csvLine[11+$offset], ($k+1));
						} else {
							$item_to = (float)$csvLine[11+$offset];
						}

                        if($this->_volume) {
                            if ( $csvLine[12] == '*' || $csvLine[12] == '') {
                                $volume_from = -1;
                            } else if (!$this->_isPositiveDecimalNumber($csvLine[12])) {
                                $exceptions[] = Mage::helper('shipping')->__('Invalid volume From "%s" in the Row #%s', $csvLine[12], ($k+1));
                            } else {
                                $volume_from = (float)$csvLine[12];
                            }

                            if ( $csvLine[13] == '*' || $csvLine[13] == '') {
                                $volume_to = 10000000;
                            } else if (!$this->_isPositiveDecimalNumber($csvLine[13])) {
                                $exceptions[] = Mage::helper('shipping')->__('Invalid volume To "%s" in the Row #%s', $csvLine[13], ($k+1));
                            }
                            else {
                                $volume_to = (float)$csvLine[13];
                            }
                        }

                        foreach ($customerGroups as $customer_group) {

                            if ($customer_group == '*') {
                                $customer_group = '';
                            } else {
                                $customer_group = trim($customer_group);
                            }

                            foreach ($splitVendor as $vendor_id) {

                                if ($vendor_id == '*') {
                                    $vendor_id = '';
                                } else {
                                    if (!$this->_isPositiveDecimalNumber($vendor_id)) {
                                        $exceptions[] = Mage::helper('shipping')->__('Invalid Vendor ID "%s" in the Row #%s', $csvLine[6], ($k + 1));
                                        break;
                                    } else {
                                        $vendor_id = trim($vendor_id);
                                    }
                                }

                                foreach ($splitCountries as $country) {

                                    $country = trim($country);

                                    if (empty($countryCodesToIds) || !array_key_exists($country, $countryCodesToIds)) {
                                        $countryId = '0';
                                        if ($country != '*' && $country != '') {
                                            $exceptions[] = Mage::helper('shipping')->__('Invalid Country "%s" in the Row #%s', $country, ($k + 1));
                                            break;
                                        }
                                    } else {
                                        $countryId = $countryCodesToIds[$country];
                                    }

                                    foreach ($splitRegions as $region) {

                                        if (!isset($countryCodesToIds[$country])
                                            || !isset($regionCodesToIds[$countryCodesToIds[$country]])
                                            || !array_key_exists($region, $regionCodesToIds[$countryCodesToIds[$country]])
                                        ) {
                                            $regionId = '0';
                                            if ($region != '*' && $region != '') {
                                                $exceptions[] = Mage::helper('shipping')->__('Invalid Region/State "%s" in the Row #%s', $region, ($k + 1));
                                                break;
                                            }
                                        } else {
                                            $regionId = $regionCodesToIds[$countryCodesToIds[$country]][$region];
                                        }

                                        foreach ($splitPostcodes as $postcode) {
                                            if ($postcode == '*' || $postcode == '') {
                                                $zip = '';
                                                $new_zip_to = '';
                                            } else {
                                                $zip_str = explode("-", $postcode);
                                                if (count($zip_str) != 2) {
                                                    $zip = trim($postcode);
                                                    if (ctype_digit($postcode) && trim($zip_to) == '') {
                                                        $new_zip_to = trim($postcode);
                                                    } else $new_zip_to = $zip_to;
                                                } else {
                                                    $zip = trim($zip_str[0]);
                                                    $new_zip_to = trim($zip_str[1]);
                                                }
                                            }
                                            foreach ($splitCities as $city) {
                                                $city = trim($city);

                                                if($city == '*' || $city == '') {
                                                    $city = '';
                                                }

                                                if ($this->_uDrop && !count($exceptions)) {
                                                    $data[] = array('website_id' => $websiteId, 'dest_country_id' => $countryId, 'dest_region_id' => $regionId,
                                                        'dest_city' => $city, 'dest_zip' => $zip, 'dest_zip_to' => $new_zip_to,
                                                        'package_id' => $package_id, 'udropship_vendor' => $vendor_id,
                                                        'weight_from_value' => $weight_from, 'weight_to_value' => $weight_to,
                                                        'price_from_value' => $price_from, 'price_to_value' => $price_to,
                                                        'item_from_value' => $item_from, 'item_to_value' => $item_to,
                                                        'customer_group' => $customer_group,
                                                        'price' => $csvLine[13 + $offset], 'algorithm' => $csvLine[14 + $offset],
                                                        'delivery_type' => $csvLine[15 + $offset], 'notes' => $csvLine[16 + $offset]);
                                                } else if ($this->_volume && !count($exceptions)) {
                                                    $data[] = array('website_id' => $websiteId, 'dest_country_id' => $countryId, 'dest_region_id' => $regionId,
                                                        'dest_city' => $city, 'dest_zip' => $zip, 'dest_zip_to' => $new_zip_to,
                                                        'package_id' => $package_id,
                                                        'weight_from_value' => $weight_from, 'weight_to_value' => $weight_to,
                                                        'price_from_value' => $price_from, 'price_to_value' => $price_to,
                                                        'item_from_value' => $item_from, 'item_to_value' => $item_to,
                                                        'volume_from_value'=>$volume_from,'volume_to_value'=>$volume_to,
                                                        'customer_group' => $customer_group,
                                                        'price' => $csvLine[15 + $offset], 'algorithm' => $csvLine[16 + $offset],
                                                        'delivery_type' => $csvLine[17 + $offset], 'notes' => $csvLine[18 + $offset]);
                                                } else if (count($csvLine) == 15 && !count($exceptions)) {
                                                    $data[] = array('website_id' => $websiteId, 'dest_country_id' => $countryId, 'dest_region_id' => $regionId,
                                                        'dest_city' => $city, 'dest_zip' => $zip, 'dest_zip_to' => $new_zip_to,
                                                        'package_id' => $package_id,
                                                        'weight_from_value' => $weight_from, 'weight_to_value' => $weight_to,
                                                        'price_from_value' => $price_from, 'price_to_value' => $price_to,
                                                        'item_from_value' => $item_from, 'item_to_value' => $item_to,
                                                        'customer_group' => $customer_group,
                                                        'price' => $csvLine[13], 'delivery_type' => $csvLine[14]);
                                                } else if (!count($exceptions)) {
                                                    $data[] = array('website_id' => $websiteId, 'dest_country_id' => $countryId, 'dest_region_id' => $regionId,
                                                        'dest_city' => $city, 'dest_zip' => $zip, 'dest_zip_to' => $new_zip_to,
                                                        'package_id' => $package_id,
                                                        'weight_from_value' => $weight_from, 'weight_to_value' => $weight_to,
                                                        'price_from_value' => $price_from, 'price_to_value' => $price_to,
                                                        'item_from_value' => $item_from, 'item_to_value' => $item_to,
                                                        'customer_group' => $customer_group,
                                                        'price' => $csvLine[13], 'algorithm' => $csvLine[14], 'delivery_type' => $csvLine[15], 'notes' => $csvLine[16]);
                                                }

                                                $dataDetails[] = array('country' => $country, 'region' => $region);
                                                $counter++;
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $dataStored = false;
                   		if (!empty($exceptions)) {
			            	break;
			            }
	                    if ($counter>3000) {
		                    foreach($data as $k=>$dataLine) {
		                        try {
		                            $connection->insert($table, $dataLine);
		                        } catch (Exception $e) {
		                            $exceptions[] = Mage::helper('shipping')->__('Duplicate Row #%s (Country "%s", Region/State "%s", Zip "%s")', ($k+1), $dataLine['dest_country_id'], $dataLine['dest_region_id'], $dataLine['dest_zip']);
		                            $exceptions[] = $e->getMessage();
		                       }
		                    }
		                    if (!empty($exceptions)) {
			                	break;
			                }
	                		Mage::helper('wsacommon/shipping')->updateStatus($session,count($data));
			               	unset($data);
			                unset($dataDetails);
			                $counter=0;
			                $dataStored = true;
		                }
                    }
                }
                if (empty($exceptions) && !$dataStored) {
                	foreach($data as $k=>$dataLine) {
			           try {
			           	$connection->insert($table, $dataLine);
			           } catch (Exception $e) {
			           	$exceptions[] = Mage::helper('shipping')->__('Duplicate Row #%s (Country "%s", Region/State "%s", Zip "%s", Delivery Type "%s")', ($k+1), $dataDetails[$k]['country'],
			           					$dataDetails[$k]['region'], $dataLine['dest_zip'], $dataLine['delivery_type']);

                        Mage::helper('wsalogger/log')->postWarning('productmatrix','Duplicate Row', $e->getMessage());
			            break;
			           }
	                }
	                Mage::helper('wsacommon/shipping')->updateStatus($session,count($data));

                }
            	if (!empty($exceptions)) {
            		throw new Exception( "\n" . implode("\n", $exceptions) );
                }
            }
        }
    }

    private function _getCsvValues($string, $separator=",")
    {
        $elements = explode($separator, trim($string));
        for ($i = 0; $i < count($elements); $i++) {
            $nquotes = substr_count($elements[$i], '"');
            if ($nquotes %2 == 1) {
                for ($j = $i+1; $j < count($elements); $j++) {
                    if (substr_count($elements[$j], '"') > 0) {
                        // Put the quoted string's pieces back together again
                        array_splice($elements, $i, $j-$i+1, implode($separator, array_slice($elements, $i, $j-$i+1)));
                        break;
                    }
                }
            }
            if ($nquotes > 0) {
                // Remove first and last quotes, then merge pairs of quotes
                $qstr =& $elements[$i];
                $qstr = substr_replace($qstr, '', strpos($qstr, '"'), 1);
                $qstr = substr_replace($qstr, '', strrpos($qstr, '"'), 1);
                $qstr = str_replace('""', '"', $qstr);
            }
            $elements[$i] = trim($elements[$i]);
        }
        return $elements;
    }

    private function _isPositiveDecimalNumber($n)
    {
        return preg_match ("/^[0-9]+(\.[0-9]*)?$/", $n);
    }

    public function getShippingCostsforSKU($sku)
    {
       $collection = Mage::getResourceModel('productmatrix_shipping/carrier_productmatrix_collection');
       return $collection->getSkuCosts($sku, $collection);
    }

    private function validateCsvSize($csvCount,&$exceptions){
        if (($csvCount < 15 || ($csvCount > 17) && !$this->_uDrop && !$this->_volume)) {
            $exceptions[0] = Mage::helper('shipping')->__('Invalid ProductMatrix File Format');
        }

        //TODO - implement this
        if($this->_uDrop && $this->_volume){
            $exceptions[0] = Mage::helper('shipping')->__('ProductMatrix with uDrop and Volume support in conjunction are not supported');
        }

        if($this->_uDrop){
            if($csvCount < 18){
                $exceptions[0] = Mage::helper('shipping')->__('Invalid uDrop ProductMatrix File Format');
            }
        }

        if($this->_volume){
            if($csvCount < 19){
                $exceptions[0] = Mage::helper('shipping')->__('Invalid ProductMatrix Volume Support File Format');
            }
        }
    }




}
