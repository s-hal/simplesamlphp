<?php
$this->data['header'] = $this->t('{login:user_pass_header}');

if (strlen($this->data['username']) > 0) {
        $this->data['autofocus'] = 'password';
} else {
        $this->data['autofocus'] = 'username';
}

/**
 * Support the htmlinject hook, which allows modules to change header, pre and post body on all pages.
 */
$this->data['htmlinject'] = array(
	'htmlContentPre' => array(),
	'htmlContentPost' => array(),
	'htmlContentHead' => array(),
);


$jquery = array();
if (array_key_exists('jquery', $this->data)) $jquery = $this->data['jquery'];

if (array_key_exists('pageid', $this->data)) {
	$hookinfo = array(
		'pre' => &$this->data['htmlinject']['htmlContentPre'], 
		'post' => &$this->data['htmlinject']['htmlContentPost'], 
		'head' => &$this->data['htmlinject']['htmlContentHead'], 
		'jquery' => &$jquery, 
		'page' => $this->data['pageid']
	);
		
	SimpleSAML_Module::callHooks('htmlinject', $hookinfo);	
}
// - o - o - o - o - o - o - o - o - o - o - o - o -

/**
 * Do not allow to frame simpleSAMLphp pages from another location.
 * This prevents clickjacking attacks in modern browsers.
 *
 * If you don't want any framing at all you can even change this to
 * 'DENY', or comment it out if you actually want to allow foreign
 * sites to put simpleSAMLphp in a frame. The latter is however
 * probably not a good security practice.
 */
header('X-Frame-Options: SAMEORIGIN');

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0" />
<script type="text/javascript" src="/<?php echo $this->data['baseurlpath']; ?>resources/script.js"></script>
<title><?php
if(array_key_exists('header', $this->data)) {
	echo $this->data['header'];
} else {
	echo 'Log in';
}
?></title>

	<link rel="stylesheet" type="text/css" href="<?php echo SimpleSAML_Module::getModuleURL('negocheck/iis.css'); ?>" />
	<link rel="icon" type="image/icon" href="<?php echo SimpleSAML_Module::getModuleURL('negocheck/favicon.ico');?>" /> 

<?php

if(!empty($jquery)) {
	$version = '1.8';
	if (array_key_exists('version', $jquery))
		$version = $jquery['version'];
		
	if ($version == '1.8') {
		if (isset($jquery['core']) && $jquery['core'])
			echo('<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'resources/jquery-1.8.js"></script>' . "\n");
	
		if (isset($jquery['ui']) && $jquery['ui'])
			echo('<script type="text/javascript" src="/' . $this->data['baseurlpath'] . 'resources/jquery-ui-1.8.js"></script>' . "\n");
	
		if (isset($jquery['css']) && $jquery['css'])
			echo('<link rel="stylesheet" media="screen" type="text/css" href="/' . $this->data['baseurlpath'] . 
				'resources/uitheme1.8/jquery-ui.css" />' . "\n");
	}
}

if (isset($this->data['clipboard.js'])) {
	echo '<script type="text/javascript" src="/'. $this->data['baseurlpath'] .
		 'resources/clipboard.min.js"></script>'."\n";
}


if(!empty($this->data['htmlinject']['htmlContentHead'])) {
	foreach($this->data['htmlinject']['htmlContentHead'] AS $c) {
		echo $c;
	}
}

if ($this->isLanguageRTL()) {
?>
	<link rel="stylesheet" type="text/css" href="/<?php echo $this->data['baseurlpath']; ?>resources/default-rtl.css" />
<?php	
}
?>

    <meta name="robots" content="noindex, nofollow" />

<?php	
if(array_key_exists('head', $this->data)) {
	echo '<!-- head -->' . $this->data['head'] . '<!-- /head -->';
}
?>

</head>

<?php
if(!empty($this->data['htmlinject']['htmlContentPre'])) {
	foreach($this->data['htmlinject']['htmlContentPre'] AS $c) {
		echo $c;
	}
}
?>

<body class="login">

<div id="login">

	<form name="loginform" id="loginform" action="?" method="post">
		
			<img alt="logo" src="<?php echo SimpleSAML_Module::getModuleURL('negocheck/iis.png') ?>" style="float: right" />
		
		<p>
			<label><?php echo $this->t('{login:username}'); ?><br />
			<input type="text" name="username" id="username" class="input" <?php if (isset($this->data['username'])) {
						echo 'value="' . htmlspecialchars($this->data['username']) . '"';
					} ?> size="20" tabindex="10" /></label>
		</p>
		<p>
			<label><?php echo $this->t('{login:password}'); ?><br />
			<input type="password" name="password" id="password" class="input" value="" size="20" tabindex="20" /></label>
		</p>
		<!-- p><label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="90" /> Remember me</label></p -->
		<p class="submit">
			<input type="submit" name="wp-submit" id="wp-submit" value="<?php echo $this->t('{login:login_button}'); ?> &raquo;" tabindex="100" />
		</p>



<?php
if ($this->data['errorcode'] !== NULL) {
?>
	<div id="error">
		<img src="/<?php echo $this->data['baseurlpath']; ?>resources/icons/experience/gtk-dialog-error.48x48.png" style="float: right; margin: 15px " />
		<h2><?php echo $this->t('{login:error_header}'); ?></h2>
		<p style="clear: both"><b><?php echo $this->t('{errors:title_' . $this->data['errorcode'] . '}'); ?></b></p>
		<p><?php echo $this->t('{errors:descr_' . $this->data['errorcode'] . '}'); ?></p>
	</div>
<?php
}

if(!empty($this->data['links'])) {
	echo '<ul class="links" style="margin-top: 2em">';
	foreach($this->data['links'] AS $l) {
		echo '<li><a href="' . htmlspecialchars($l['href']) . '">' . htmlspecialchars($this->t($l['text'])) . '</a></li>';
	}
	echo '</ul>';
}
?>
		
<?php
foreach ($this->data['stateparams'] as $name => $value) {
	echo('<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />');
}
?>

    </form>
</div>

<?php
	$includeLanguageBar = TRUE;
	if (!empty($_POST)) 
		$includeLanguageBar = FALSE;
	if (isset($this->data['hideLanguageBar']) && $this->data['hideLanguageBar'] === TRUE) 
		$includeLanguageBar = FALSE;
	
	if ($includeLanguageBar) {

		echo '<div id="languagebar">';		

		$languages = $this->getLanguageList();
		$langnames = array(
			'no' => 'Bokmål', // Norwegian Bokmål
			'nn' => 'Nynorsk', // Norwegian Nynorsk
			'se' => 'Sámegiella', // Northern Sami
			'sam' => 'Åarjelh-saemien giele', // Southern Sami
			'da' => 'Dansk', // Danish
			'en' => 'English',
			'de' => 'Deutsch', // German
			'sv' => 'Svenska', // Swedish
			'fi' => 'Suomeksi', // Finnish
			'es' => 'Español', // Spanish
			'fr' => 'Français', // French
			'it' => 'Italiano', // Italian
			'nl' => 'Nederlands', // Dutch
			'lb' => 'Lëtzebuergesch', // Luxembourgish
			'cs' => 'Čeština', // Czech
			'sl' => 'Slovenščina', // Slovensk
			'lt' => 'Lietuvių kalba', // Lithuanian
			'hr' => 'Hrvatski', // Croatian
			'hu' => 'Magyar', // Hungarian
			'pl' => 'Język polski', // Polish
			'pt' => 'Português', // Portuguese
			'pt-br' => 'Português brasileiro', // Portuguese
			'ru' => 'русский язык', // Russian
			'et' => 'eesti keel', // Estonian
			'tr' => 'Türkçe', // Turkish
			'el' => 'ελληνικά', // Greek
			'ja' => '日本語', // Japanese
			'zh' => '简体中文', // Chinese (simplified)
			'zh-tw' => '繁體中文', // Chinese (traditional)
			'ar' => 'ﺎﻠﻋﺮﺒﻳﺓ', // Arabic
			'fa' => 'پﺍﺮﺳی', // Persian
			'ur' => 'ﺍﺭﺩﻭ', // Urdu
			'he' => 'עִבְרִית', // Hebrew
			'id' => 'Bahasa Indonesia', // Indonesian
			'sr' => 'Srpski', // Serbian
			'lv' => 'Latviešu', // Latvian
			'ro' => 'Românește', // Romanian
			'eu' => 'Euskara', // Basque
		);
		
		$textarray = array();
		foreach ($languages AS $lang => $current) {
			if ($current) {
				$textarray[] = $langnames[$lang];
			} else {
				$textarray[] = '<a href="' . htmlspecialchars(
						SimpleSAML_Utilities::addURLparameter(
							SimpleSAML_Utilities::selfURL(), array('language' => $lang)
						)
				) . '">' . $langnames[$lang] . '</a>';
			}
		}
		echo join(' | ', $textarray);
		echo '</div>';
	}
    
    sspmod_negocheck_Source_NegoCheck::preparecheck();

    $onLoad = '';
    if(array_key_exists('autofocus', $this->data)) {
    	$onLoad .= 'SimpleSAML_focus(\'' . $this->data['autofocus'] . '\');';
    }
    if (isset($this->data['onLoad'])) {
    	$onLoad .= $this->data['onLoad']; 
    }

?>

<script type="text/javascript">
    setTimeout( function() { window.onload = <?php echo $onLoad; ?> }, 500);
</script>


</body>
</html>
