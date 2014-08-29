<?php
/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin
 * @copyright Copyright &copy; 2005-2009, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id$
 */

require_once 'Zend/Translate.php';
require_once 'Zend/Locale.php';

/**
 * I18n (Internationalization class) is singleton class that tranlates string
 * to different languages according to application translation files.
 *
 * @package   openbiz.bin
 * @author    Rocky Swen <rocky@phpopenbiz.org>
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class I18n
{

    const LANGUAGE_PATH_1 = "languages";
    const LANGUAGE_PATH_2 = "LC_MESSAGES";
    const DEFAULT_LANGUAGE = DEFAULT_LANGUAGE;

    /**
     * @var I18n
     */
    private static $_singleton = null;
    private $_zTrans = array();

    /**
     * @var Zend_Locale
     */
    private $_zLocale = null;
    private $_curLang = null;
    
    protected static $_langData;
    protected static $_langCode;
    
    public static function t($text, $key=null, $module)
    {
    	// TODO: use cache, apc cache? special handling for menu?
    	
    	//echo "to translate $text, $key, $module".nl;
    	if (!I18n::loadLangData($module))	// cannot load lang data, return orig text
			return $text;
		
    	if ($key && isset(I18n::$_langData[$module][$key]))
    		return I18n::$_langData[$module][$key];
    	
    	$key = strtoupper('STRING_'.md5($text));
    	if ($key && isset(I18n::$_langData[$module][$key]))
    		return I18n::$_langData[$module][$key];
    	
    	// try to load system.ini if previous steps can't find match
    	if ($module != '_system')
    		return self::t($text, $key, '_system');
    		
    	return $text;
    }
    
    protected static function loadLangData($module)
    {
    	if (isset(I18n::$_langData[$module])) {
    		return true;
    	}
    	
    	// get language code
    	$langCode = I18n::getCurrentLangCode();
    	
    	// load language file
    	if ($module == '_system') $filename = 'system.ini';
    	else $filename = "mod.$module.ini";
    	$langFile = LANGUAGE_PATH."/$langCode/$filename";
    	//echo "check ini file $langFile".nl;
    	if (!file_exists($langFile)) return false;
    	
    	//echo "parse ini file $langFile".nl;
    	$inidata = parse_ini_file($langFile, false);
    	
    	I18n::$_langData[$module] = $inidata;
    	//print_r(I18n::$_langData);
    	return true;
    } 
    
	protected static function getCurrentLangCode ()
    {
    	if (I18n::$_langCode != null)
            return I18n::$_langCode;
        $currentLanguage = BizSystem::sessionContext()->getVar("LANG");
        // default language
        if ($currentLanguage == "")
            $currentLanguage = I18n::DEFAULT_LANGUAGE;
        // language from url
        if (isset($_GET['lang']))
            $currentLanguage = $_GET['lang'];

        // TODO: user pereference has language setting
        
        BizSystem::sessionContext()->setVar("LANG", $currentLanguage);
        I18n::$_langCode = $currentLanguage;
        
        return $currentLanguage;
    }

    private function __construct()
    {
        $this->_zLocale = new Zend_Locale();
    }

    /**
     * Get instance of I18n
     * @return I18n instant of I18n
     */
    public static function getInstance ()
    {
        if (! isset(self::$_singleton))
        {
            $c = __CLASS__;
            self::$_singleton = new $c();
        }
        return self::$_singleton;
    }

    /**
     * Translate string to another string with specific language
     * @param string $str to be translated string
     * @param string $language given language name
     * @return string translated string
     */
    public function translate2 ($str, $lang = null)
    {
        $translation = $str;
        $zTransPHPopenbiz = NULL;
        if ($lang == null)
        {
            $lang = $this->getCurrentLanguage();
        }
        else
        {
            if (! isset($this->_zTrans[$lang]))
            {
                $this->_loadLanguage($lang);
            }
        }
        if (isset($this->_zTrans[$lang]) && $str != "")
        {
            $zTransApp = $this->_zTrans[$lang]['app'];
            if (array_key_exists('phpopenbiz', $this->_zTrans[$lang]))
                $zTransPHPopenbiz = $this->_zTrans[$lang]['phpopenbiz'];
            $isTranslated = false;
            if ($zTransApp != null)
            {
                $translation = $zTransApp->translate($str);
                $isTranslated = $zTransApp->getAdapter()->isTranslated($str);
            }
            if (! $isTranslated && $zTransPHPopenbiz != null)
            {
                $translation = $zTransPHPopenbiz->translate($str);
            }
        }
        return $translation;
    }

    /**
     * Translate string to another string with current lanuguage setting
     *
     * @param string $str to be translated string
     * @return string translated string
     */
    public function translate ($str)
    {
        return $this->translate2($str);
    }

    /**
     * Check if a string is translated or not.
     *
     * @param string $str to be translated string
     * @param string $language given language name
     * @return boolean
     */
    public function isTranslated ($str, $lang = null)
    {
        if ($lang == null)
        {
            $lang = $this->getCurrentLanguage();
        }
        else
        {
            if (! isset($this->_zTrans[$lang]))
            {
                $result = $this->_loadLanguage($lang);
                if ($result == false)
                {
                    return false;
                }
            }
        }
        $zTransApp = $this->_zTrans[$lang]['app'];
        $isTranslated = $zTransApp->getAdapter()->isTranslated($str);
        return $isTranslated;
    }

    /**
     * Load language file of given language
     *
     * @param string $language given language name
     * @return boolean true if language file is loaded successfully
     */
    private function _loadLanguage($lang)
    {
        $appLanguagePath = APP_HOME . "/" . I18n::LANGUAGE_PATH_1 . "/" . $lang . "/" . I18n::LANGUAGE_PATH_2 . "/" . "lang." . $lang . ".mo";
        $openbizLanguagePath = OPENBIZ_HOME . "/" . I18n::LANGUAGE_PATH_1 . "/" . $lang . "/" . I18n::LANGUAGE_PATH_2 . "/" . "lang." . $lang . ".mo";
        $noAppMoFile = false;
        if (file_exists($appLanguagePath))
        {
            try
            {
                $zTransApp = new Zend_Translate('gettext', $appLanguagePath, $lang);
                $this->_zTrans[$lang]['app'] = $zTransApp;
            }
            catch (Exception $e)
            {
                $noAppMoFile = true;
            }
        }
        $noOpenbizMoFile = false;
        if (file_exists($openbizLanguagePath))
        {
            try
            {
                $ztransOpenbiz = new Zend_Translate('gettext', $openbizLanguagePath, $lang);
                $this->_zTrans[$lang]['phpopenbiz'] = $ztransOpenbiz;
            }
            catch (Exception $e)
            {
                $noOpenbizMoFile = true;
            }
        }
        $result = true;
        if ($noOpenbizMoFile == true && $noAppMoFile == true)
        {
            $result = false;
        }
        else
        {
            if ($noOpenbizMoFile == true)
            {
                $this->_zTrans[$lang]['phpopenbiz'] = null;
            }
            if ($noAppMoFile == true)
            {
                $this->_zTrans[$lang]['app'] = null;
            }
        }
        return $result;
    }

    /**
     * Get best region from browser
     * @return <type>
     */
    public function getBestRegionFromBrowser()
    {
        $region = $this->_zLocale->getRegion();
        if ($region === FALSE)
        {
            return null;
        }
        return $region;
    }

    /**
     * Get locale for SetlocaleWin
     * locale relies on browser setting
     * @return <type>
     */
    public function getLocaleForSetlocaleWin ()
    {
        $zEnglishLocale = new Zend_Locale("en");
        $currentLanguage = $this->getCurrentLanguage();
        $acceptedLangsByBrowser = $this->_zLocale->getBrowser();
        array_multisort($acceptedLangsByBrowser, SORT_DESC, SORT_NUMERIC);
        if ($acceptedLangsByBrowser != null)
        {
            foreach ($acceptedLangsByBrowser as $acceptedLang => $quality)
            {
                $locale = explode('_', $acceptedLang);
                if ($currentLanguage == $acceptedLang || $currentLanguage == $locale[0])
                {
                    $language = $zEnglishLocale->getLanguageTranslation($locale[0]);
                    $country = null;
                    if (isset($locale[1]))
                    {
                        $country = $this->get3from2($locale[1]);
                    }
                    $windowsCode = $language . ($country != null ? "_" . $country : "");
                    return $windowsCode;
                }
            }
        }
        $currentLanguageWinLocale = $zEnglishLocale->getLanguageTranslation($currentLanguage);
        return $currentLanguageWinLocale;
    }

    /**
     * Get current language in short format
     * @return string
     */
    public function getCurrentLanguageShort ()
    {
        $currentLanguage = $this->getCurrentLanguage();
        $parts = explode('_', $currentLanguage);
        $currentLanguageShort = $parts[0];
        return $currentLanguageShort;
    }

    /**
     * Get best available language setting from browser
     * if browser = es_AR and no es_AR.mo but es.mo, load es.mo
     * @return string language name
     */
    public function getBestAvailableLanguageFromBrowser ()
    {
        $acceptedLangsByBrowser = $this->_zLocale->getBrowser();
        array_multisort($acceptedLangsByBrowser, SORT_DESC, SORT_NUMERIC);
        $currentLanguage = I18n::DEFAULT_LANGUAGE;
        if ($acceptedLangsByBrowser != null)
        {
            foreach ($acceptedLangsByBrowser as $acceptedLang => $quality)
            {
                $isAvailable = $this->_loadLanguage($acceptedLang);
                if ($isAvailable === FALSE)
                {
                    $parts = explode('_', $acceptedLang);
                    $isAvailable = $this->_loadLanguage($parts[0]);
                }
                if ($isAvailable)
                {
                    $currentLanguage = $acceptedLang;
                    break;
                }
            }
        }
        return $currentLanguage;
    }

    /**
     * Get current language setting from session, browser, url,
     * @return string language name
     */
    public function getCurrentLanguage ()
    {
        if ($this->_curLang != null)
            return $this->_curLang;
        $sessionContext = BizSystem::sessionContext();
        $currentLanguage = BizSystem::sessionContext()->getVar("LANG");
        if ($currentLanguage == "")
        {
            $currentLanguage = I18n::DEFAULT_LANGUAGE;
            if (!$currentLanguage)
        		$currentLanguage = I18n::getBestAvailableLanguageFromBrowser();
            $sessionContext->setVar("LANG", $currentLanguage);
        }
        if (isset($_REQUEST['lang']))
        {
            $requestedLanguage = $_REQUEST['lang'];
            if (! isset($this->_zTrans[$requestedLanguage]))
            {
                $result = $this->_loadLanguage($requestedLanguage);
                if ($result == false)
                {
                    $parts = explode('_', $requestedLanguage);
                    $requestedLanguage = $parts[0];
                    $result = $this->_loadLanguage($requestedLanguage);
                }
                if ($result == true)
                {
                    $currentLanguage = $requestedLanguage;
                    $sessionContext->setVar("LANG", $requestedLanguage);
                }
            }
        }
        if (! isset($this->_zTrans[$currentLanguage]))
        {
            $this->_loadLanguage($currentLanguage);
        }
        $this->_curLang = $currentLanguage;
        return $currentLanguage;
    }

    /**
     * Get 3 length code from 2 length code
     * @param string $code2 2-length code
     * @return string 3 length code
     */
    function get3from2 ($code2)
    {
        $code2 = strtoupper($code2);
        if (isset($this->two2three[$code2]))
        {
            return $this->two2three[$code2];
        }
        return null;
    }

    /**
     *
     * @var array
     */
    public $two2three = array("AF" => "AFG" , "AL" => "ALB" , "DZ" => "DZA" , "AS" => "ASM" , "AD" => "AND" , "AO" => "AGO" , "AI" => "AIA" , "AQ" => "ATA" , "AG" => "ATG" , "AR" => "ARG" , "AM" => "ARM" , "AW" => "ABW" , "AU" => "AUS" , "AT" => "AUT" , "AZ" => "AZE" , "BS" => "BHS" , "BH" => "BHR" , "BD" => "BGD" , "BB" => "BRB" , "BY" => "BLR" , "BE" => "BEL" , "BZ" => "BLZ" , "BJ" => "BEN" , "BM" => "BMU" , "BT" => "BTN" , "BO" => "BOL" , "BA" => "BIH" , "BW" => "BWA" , "BV" => "BVT" , "BR" => "BRA" , "IO" => "IOT" , "BN" => "BRN" , "BG" => "BGR" , "BF" => "BFA" , "BI" => "BDI" , "KH" => "KHM" , "CM" => "CMR" , "CA" => "CAN" , "CV" => "CPV" , "KY" => "CYM" , "CF" => "CAF" , "TD" => "TCD" , "CL" => "CHL" , "CN" => "CHN" , "CX" => "CXR" , "CC" => "CCK" , "CO" => "COL" , "KM" => "COM" , "CG" => "COG" , "CK" => "COK" , "CR" => "CRI" , "CI" => "CIV" , "HR" => "HRV" , "CU" => "CUB" , "CY" => "CYP" , "CZ" => "CZE" , "DK" => "DNK" , "DJ" => "DJI" , "DM" => "DMA" , "DO" => "DOM" , "TL" => "TLS" , "EC" => "ECU" , "EG" => "EGY" , "SV" => "SLV" , "GQ" => "GNQ" , "ER" => "ERI" , "EE" => "EST" , "ET" => "ETH" , "FK" => "FLK" , "FO" => "FRO" , "FJ" => "FJI" , "FI" => "FIN" , "FR" => "FRA" , "FX" => "FXX" , "GF" => "GUF" , "PF" => "PYF" , "TF" => "ATF" , "GA" => "GAB" , "GM" => "GMB" , "GE" => "GEO" , "DE" => "DEU" , "GH" => "GHA" , "GI" => "GIB" , "GR" => "GRC" , "GL" => "GRL" , "GD" => "GRD" , "GP" => "GLP" , "GU" => "GUM" , "GT" => "GTM" , "GN" => "GIN" , "GW" => "GNB" , "GY" => "GUY" , "HT" => "HTI" , "HM" => "HMD" , "HN" => "HND" , "HK" => "HKG" , "HU" => "HUN" , "IS" => "ISL" , "IN" => "IND" , "ID" => "IDN" , "IR" => "IRN" , "IQ" => "IRQ" , "IE" => "IRL" , "IL" => "ISR" , "IT" => "ITA" , "JM" => "JAM" , "JP" => "JPN" , "JO" => "JOR" , "KZ" => "KAZ" , "KE" => "KEN" , "KI" => "KIR" , "KP" => "PRK" , "KR" => "KOR" , "KW" => "KWT" , "KG" => "KGZ" , "LA" => "LAO" , "LV" => "LVA" , "LB" => "LBN" , "LS" => "LSO" , "LR" => "LBR" , "LY" => "LBY" , "LI" => "LIE" , "LT" => "LTU" , "LU" => "LUX" , "MO" => "MAC" , "MK" => "MKD" , "MG" => "MDG" , "MW" => "MWI" , "MY" => "MYS" , "MV" => "MDV" , "ML" => "MLI" , "MT" => "MLT" , "MH" => "MHL" , "MQ" => "MTQ" , "MR" => "MRT" , "MU" => "MUS" , "YT" => "MYT" , "MX" => "MEX" , "FM" => "FSM" , "MD" => "MDA" , "MC" => "MCO" , "MN" => "MNG" , "MS" => "MSR" , "MA" => "MAR" , "MZ" => "MOZ" , "MM" => "MMR" , "NA" => "NAM" , "NR" => "NRU" , "NP" => "NPL" , "NL" => "NLD" , "AN" => "ANT" , "NC" => "NCL" , "NZ" => "NZL" , "NI" => "NIC" , "NE" => "NER" , "NG" => "NGA" , "NU" => "NIU" , "NF" => "NFK" , "MP" => "MNP" , "NO" => "NOR" , "OM" => "OMN" , "PK" => "PAK" , "PW" => "PLW" , "PA" => "PAN" , "PG" => "PNG" , "PY" => "PRY" , "PE" => "PER" , "PH" => "PHL" , "PN" => "PCN" , "PL" => "POL" , "PT" => "PRT" , "PR" => "PRI" , "QA" => "QAT" , "RE" => "REU" , "RO" => "ROU" , "RU" => "RUS" , "RW" => "RWA" , "KN" => "KNA" , "LC" => "LCA" , "VC" => "VCT" , "WS" => "WSM" , "SM" => "SMR" , "ST" => "STP" , "SA" => "SAU" , "SN" => "SEN" , "SC" => "SYC" , "SL" => "SLE" , "SG" => "SGP" , "SK" => "SVK" , "SI" => "SVN" , "SB" => "SLB" , "SO" => "SOM" , "ZA" => "ZAF" , "ES" => "ESP" , "LK" => "LKA" , "SH" => "SHN" , "PM" => "SPM" , "SD" => "SDN" , "SR" => "SUR" , "SJ" => "SJM" , "SZ" => "SWZ" , "SE" => "SWE" , "CH" => "CHE" , "SY" => "SYR" , "TW" => "TWN" , "TJ" => "TJK" , "TZ" => "TZA" , " 	" => "   " , "TH" => "THA" , "TG" => "TGO" , "TK" => "TKL" , "TO" => "TON" , "TT" => "TTO" , "TN" => "TUN" , "TR" => "TUR" , "TM" => "TKM" , "TC" => "TCA" , "TV" => "TUV" , "UG" => "UGA" , "UA" => "UKR" , "AE" => "ARE" , "GB" => "GBR" , "US" => "USA" , "UM" => "UMI" , "UY" => "URY" , "UZ" => "UZB" , "VU" => "VUT" , "VA" => "VAT" , "VE" => "VEN" , "VN" => "VNM" , "VG" => "VGB" , "VI" => "VIR" , "WF" => "WLF" , "EH" => "ESH" , "YE" => "YEM" , "YU" => "YUG" , "ZR" => "ZAR" , "ZM" => "ZMB" , "ZW" => "ZWE");
}
?>