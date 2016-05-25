<?php
/**
 * @file admin.php
 *
 * elFinder plugin elfinder.php
 * 
 * @author Takashi Uchiyama 
 * @version 1.01
 * @see https://github.com/bbfriend/elfinde_xh
 * 
 *  This file is part of the elfinder editor plugin for CMSimple.
 *
 *
 *  License <http://www.gnu.org/licenses/>.
 */

if (!XH_ADM ) {
    return;
}
/*
 * Prevent direct access.
 */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

// Read elFinder's Json file
$json = file_get_contents($pth['folder']['plugins']. $plugin .'/elfinder/package.json');
$array = json_decode( $json , true ) ;

/**
 * The plugin version.
 */
define('ELFINDER_XH_VERSION', '1.01_bild'.$array["version"] );



if (!class_exists('Elfinder')) require dirname(__FILE__)."/elfinder.php";
if(isset($elfinder_xh))
{
	initvar('admin');
	initvar('post');
	
	$plugin = basename(dirname(__FILE__),"/");
	
	$o .= print_plugin_admin('on');
	
	if($admin != 'plugin_main')
	{
		$o .= plugin_admin_common($action,$admin,$plugin);
	}

	if ($action == ''
		OR $action == 'plugin_text')
	{

		/* Plugin information. */
		$o .= '<h1>' . Elfinder::name() . '</h1>'
				. tag('img src="'.$pth['folder']['plugins']. $plugin .'/help/images/elFinder_xh.png" style="float: left; margin: 0 20px 20px 0"') 
				. Elfinder::description()

				. '<p>'
                            . 'Plugin Version: ' . ELFINDER_XH_VERSION . '<br />'
                . '</p>'
                . '<p>'
                            . 'Released: ' . Elfinder::release_date() . '<br />'
                            . 'Author: ' . Elfinder::author() . '<br />'
                            . 'GitHub Repository/Releases: ' . Elfinder::github() . '<br />'
                            . Elfinder::legal() . '<br />'
				.'</p>'
				.'<div style="clear: both;"></div>'
                .'<h2>elFinder Ver: ' . $array["version"] . '</h2>'
				. '<p>'
                            . 'author: ' . $array["author"] . '<br />'
                            . 'Home: <a href="' . $array["homepage"] . '" target="_blank">' . $array["homepage"] . '</a><br />'
                            . 'Report bugs: <a href="' . $array["bugs"] . '" target="_blank">' . $array["bugs"] . '</a><br />'
                            . 'license: <a href="http://en.wikipedia.org/wiki/BSD_licenses" target="_blank">' . $array["license"] . '</a><br />'
				.'</p>';

		$o .= Elfinder::systemChecks();
	}

}

/**
 * Load the filebrowser if a filetype is requested.
 */
if($adm)
{
	if(isset($cf['filebrowser']['external'])
		AND $cf['filebrowser']['external'] != 'elfinder_xh'){
		return;
	}

	/* Detect type. */
	if ($images || $function == 'images'){
		$type = 'images';
		$startpath = $cf['folders']["images"];
	}elseif ($downloads || $function == 'downloads'){
		$type = 'downloads';
		$startpath = $cf['folders']["downloads"];
	}elseif ($media){
		$type = 'media';
		$startpath = $cf['folders']["media"];
	}elseif ($userfiles){
		$type = 'userfiles';
		$startpath = '';
	}else{
		$type = '';
		$startpath = '';
	}

	/**
	 * All configuraiton is done usign session.
	 * Start session if not started yet.
	 * SESSION is used in the editor (ex TinyMCE)
	 */
	if (!isset($_SESSION))
	{
		session_start();
	}
	
	$_SESSION['elfinder']['folders'] = $cf['folders'];
	$_SESSION['elfinder']['folders']['plugins'] = $pth['folder']['plugins'];
	$_SESSION['elfinder']['sn'] = $sn;
	$_SESSION['elfinder']['url'] = $_SERVER['HTTP_HOST'] . $sn . 'userfiles/';
	$_SESSION['elfinder']['type'] = $type;
	$_SESSION['elfinder']['startpath'] = $startpath;
	$_SESSION['elfinder']['root'] = realpath($pth['folder']['userfiles']) . '/';
	$_SESSION['elfinder']['password'] = $cf['security']['password'];

	// language of elfinder
	if( $sl == "ja") 	 $_SESSION['elfinder']['lang'] = 'jp';
	elseif( $sl == "pt") $_SESSION['elfinder']['lang'] = 'pt_BR';
	elseif( $sl == "zh") $_SESSION['elfinder']['lang'] = 'zh_CN';
	elseif( $sl == "tw") $_SESSION['elfinder']['lang'] = 'zh_TW';
	else				 $_SESSION['elfinder']['lang'] = $sl;

	$_SESSION['elfinder']['images_maxsize'] = $cf['images']['maxsize'];
	$_SESSION['elfinder']['downloads_maxsize'] = $cf['downloads']['maxsize'];
	$_SESSION['elfinder']['locale'] = $tx['locale']['all'];
	$_SESSION['elfinder']['dateformat'] = $tx['lastupdate']['dateformat'];

	$_SESSION['elfinder']['theme'] = $plugin_cf[$plugin]['interface_theme'];

/******************************************
 * 
 * admin Memu --> file / image / downloads / media  ...
 * 
********************************************/
	if (isset($type)
		AND $type != '') //admin Memu > file ...
	{
		$f = $type;
		if (file_exists($pth['folder']['plugins'] . 'jquery/jquery.inc.php'))
		{
			include_once($pth['folder']['plugins'] . 'jquery/jquery.inc.php'); 
		}
		
		/* Use JQuery plugin if possible. */
		if(!function_exists('include_jQuery'))
		{
			/* Include JQuery and JQuery UI. */
			Elfinder::include_jquery();
		}
		else
		{
			/* Include JQuery. */
			include_jQuery();
			
			/* Include JQuery UI. */
			include_jQueryUI();
		}
	
		/* Include elfinder. */
		Elfinder::include_elfinder(); // load init.js etc

		/* Conflict with bootstrap.js: resize,crop,rotate  dialog */
		if($plugin_cf[$plugin]['trouble-Shoot_xh-TemplateIsBootstrap']){
			$hjs .= '
				<script>
					$(document).ready(function () {
						var btn = $.fn.button.noConflict() // reverts $.fn.button to jqueryui btn
						$.fn.btn = btn // assigns bootstrap button functionality to $.fn.btn
					});
				</script>';
		}
		/* Init elfinder. */
		/* Client elFinder initialization (REQUIRED) 
		 * https://github.com/Studio-42/elFinder/wiki/Client-configuration-options-2.1
		*/
		$hjs .= '<script type="text/javascript">
			$().ready(function() {
				$(".elfinder").elfinder({
					url         : "' . $pth['folder']['plugins'] . $plugin . '/connectors/connector.minimal_xh.php",
					lang        : "' . $_SESSION['elfinder']['lang'] . '",
					dateFormat :"' . $tx['lastupdate']['dateformat'] . '",
					width     :"' . $plugin_cf[$plugin]['interface_width'] . '",
					height     :"' . $plugin_cf[$plugin]['interface_height'] . '",
					ui      :  elfinder_ui,
					uiOptions : {
						toolbar     : elfinder_toolbar
					},
					contextmenu : elfinder_contextmenu,
				});
			});

			</script>';
		
		$o .= '<h1>' . $tx['editmenu'][$type] . '</h1>';
		$o .= '<div class="elfinder"></div>';
		
		/* Reset variables. */
		$images = $downloads = $userfiles = $media = FALSE;
	}
}
?>