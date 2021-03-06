<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: SitesManager.php 1833 2010-02-10 08:26:40Z vipsoft $
 * 
 * @category Piwik_Plugins
 * @package Piwik_SitesManager
 */

/**
 *
 * @package Piwik_SitesManager
 */
class Piwik_SitesManager extends Piwik_Plugin
{	
	public function getInformation()
	{
		$info = array(
			'name' => 'SitesManager',
			'description' => Piwik_Translate('SitesManager_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
		);
		return $info;
	}
	
	function getListHooksRegistered()
	{
		return array(
			'template_css_import' => 'css',
			'AdminMenu.add' => 'addMenu',
			'Common.fetchWebsiteAttributes' => 'recordWebsiteHostsInCache',
		);
	}
	
	function css()
	{
		echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"themes/default/styles.css\" />\n";
	}
	
	function recordWebsiteHostsInCache($notification)
	{
		$idsite = $notification->getNotificationInfo();
		// add the 'hosts' entry in the website array
		$array =& $notification->getNotificationObject();
		$urls = Piwik_SitesManager_API::getInstance()->getSiteUrlsFromId($idsite);
		$hosts = array();
		foreach($urls as $url)
		{
			$url = parse_url($url);
			if(isset($url['host'])) 
			{
				$hosts[] = $url['host'];
			}
		}
		$array['hosts'] = $hosts;
	}
	
	function addMenu()
	{
		Piwik_AddAdminMenu('SitesManager_MenuSites', array('module' => 'SitesManager', 'action' => 'index'));		
	}
}

