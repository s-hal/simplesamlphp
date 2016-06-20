<?php

/**
 * This class outputs the JavaScript that when executed by the browser run an AJAX requests.
 * The request will check if the browser is capable of returning a kerberos ticket.
 * 
 *
 * @author Stefan Halén, IIS.
 */
class sspmod_negocheck_Source_NegoCheck {
    public static function preparecheck() {
        if (!array_key_exists('AuthState', $_REQUEST)) {
            throw new SimpleSAML_Error_BadRequest('Missing AuthState parameter.');
        }
        $state = SimpleSAML_Auth_State::loadState($_REQUEST['AuthState'], sspmod_core_Auth_UserPassBase::STAGEID);
        if (!array_key_exists('negotiate_done' ,$state)){
            $mconfig = SimpleSAML_Configuration::getOptionalConfig('config-negocheck.php');
            $params = array('AuthState' => $_REQUEST['AuthState']);
            $url_negocheck = SimpleSAML_Module::getModuleURL('negocheck/negocheck.php');
            $url_savestate = SimpleSAML_Module::getModuleURL('negocheck/savestate.php',$params);
            echo '
                <script type="text/javascript">
                    function ajaxFunction(){
                        var ajaxRequest;  // The variable that makes Ajax possible!
                            try{
                                // Opera 8.0+, Firefox, Safari
                                ajaxRequest = new XMLHttpRequest();
                            }catch (e){
                                // Internet Explorer Browsers
                                try{
                                    ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
                                }catch (e) {
                                    try{
                                        ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
                                    }catch (e){
                                        // Something went wrong
                                        alert("Your browser broke!");
                                        return false;
                                    }
                                }
                            }

                            ajaxRequest.onreadystatechange = function(){
                                if(ajaxRequest.readyState == 4){
                                    if(ajaxRequest.responseText === "SPNEGOAvailable") {
                                        document.myForm.SPNEGOAvailable.value = "true";
                                        document.myForm.action = "'. $url_savestate .'";
                                        document.myForm.submit();
                                    } else {
                                        showContent();
                                    }
                               }
                            }
                            ajaxRequest.open("GET", "'. $url_negocheck .'", true);
                            ajaxRequest.withCredentials = false;
                            ajaxRequest.send(null);
                    }

                    function showContent(){
                        for (div in divShow) {
                            var elem = document.getElementById(div);
                            if(typeof elem !== "undefined" && elem !== null) {
                                elem.style.display = divShow[div];
                            }
                        }
                    }               

                    var divHide = '. json_encode($mconfig->getArray('divhide', NULL)) .';
                    var divShow = '. json_encode($mconfig->getArray('divshow', NULL)) .';
                    for (div in divHide) {
                        var elem = document.getElementById(div);
                        if(typeof elem !== "undefined" && elem !== null) {
                            elem.style.display = divHide[div];
                        }
                    }

                    window.onload = ajaxFunction();
                    setTimeout(showContent, ' . $mconfig->getInteger('timeout', 0) . ');
                </script>
                <form action="" method="post" name="myForm">
                    <input type="hidden" name="SPNEGOAvailable">
                </form>
            ';
        }
    }
    
    /**
     * Send a 401 to the browser. Check if the response contains a autorization header of type
     * Negotiate OID = 1.3.6.1.5.5.2. If true, send SPNEGOAvailable, if not, send SPNEGONotAvailable.
     * 
     *
     * @author Stefan Halén, IIS.
      */
    public static function negocheck() {
        $status = "SPNEGONotAvailable";
        if(empty($_SERVER['HTTP_AUTHORIZATION']) || 'Negotiate ' !=  substr($_SERVER['HTTP_AUTHORIZATION'], 0, 10)){
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Negotiate',false);
        } else {
            list($mech, $data) = split(' ', $_SERVER['HTTP_AUTHORIZATION']);
            $asn1 = new sspmod_negocheck_phpseclib_File_ASN1();
            $str = str_replace(array("\r", "\n", ' '), '', $data);
            $str = preg_match('#^[a-zA-Z\d/+]*={0,2}$#', $str) ? base64_decode($str) : false;
            if ($str != false) {
                $result = $asn1->decodeBER($str);
                if(($result[0]['content'][0]['type'] == $asn1::TYPE_OBJECT_IDENTIFIER) and ($result[0]['content'][0]['content'] == '1.3.6.1.5.5.2')){
                    $status = 'SPNEGOAvailable';
                }
            }
        }
        echo $status;
    }

    /**
     * Save the state so that it can be loaded from negoload.
     * Set $state['negotiate_done'] = TRUE to prevent looping
     *
     *
     * @author Stefan Halén, IIS.
     */
    public static function savestate() {
        $state = SimpleSAML_Auth_State::loadState($_REQUEST['AuthState'], sspmod_core_Auth_UserPassBase::STAGEID);
        $state['negotiate_done'] = TRUE;
        $id = SimpleSAML_Auth_State::saveState($state, sspmod_negotiate_Auth_Source_Negotiate::STAGEID);
        $params = array('AuthState' => $id);
        $params['test'] = "test";
        $url_negoload = SimpleSAML_Module::getModuleURL('negocheck/negoload.php',$params);

        echo (' 	
            <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
                "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
            <head>
                <meta http-equiv="content-type" content="text/html; charset=utf-8" />
                <title>POST data</title>
            </head>
            <body onload="document.getElementsByTagName(\'input\')[0].click();">
                <form method="post" action="' .  $url_negoload . '">
                    <input type="submit" style="display:none;" />
                </form>
            </body>
            </html>
        ');
    }

    /**
     * Perform authenticate with the negotiate module.
     * If fallback is set to a base username/password auth module. 
     * 
     *
     * @author Stefan Halén, IIS.
     */
    public static function negoload() {
        $state = SimpleSAML_Auth_State::loadState($_REQUEST['AuthState'], sspmod_negotiate_Auth_Source_Negotiate::STAGEID);
        $mconfig = SimpleSAML_Configuration::getOptionalConfig('config-negocheck.php');
        $source = SimpleSAML_Auth_Source::getById($mconfig->getString('authsource'));
        $source->authenticate($state);
    }
}
