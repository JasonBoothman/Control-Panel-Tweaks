<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * RD Control Panel Tweaks extension class
 *
 * @package		Control Panel Styling
 * @author		Jason Boothman
 * @copyright	Copyright (c) 2017, Reusser Design
 * @link		https://github.com/oldmanboothman
 * @since		1.0.0
 * @filesource 	./system/user/addons/control_panel_tweaks/ext.control_panel_tweaks.php
 */
class Control_panel_tweaks_ext {

	var $settings = array();

	/**
	 * Constructor
	 *
	 */
	public function __construct($settings = '')
	{
		$this->settings = $settings;

		// required extension properties
		$this->name				= 'Control Panel Tweaks';
		$this->version			= '1.0.0';
		$this->description		= 'This add-on modifies some of the default control panel styling as well as gives you options to make various overrides such as hiding certain parts of the navigation.';
		$this->settings_exist	= 'y';

		ee()->load->library('session');
	}

	// ------------------------------------------------------

	/**
	 * Activate Extension
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		 $this->_add_hook('cp_css_end', 10);
	}

	// ------------------------------------------------------

	/**
	 * Disable Extension
	 *
	 * @return void
	 */
	public function disable_extension()
	{
		ee()->db->where('class', __CLASS__);
		ee()->db->delete('extensions');
	}

	// ------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * @param 	string	String value of current version
	 * @return 	mixed	void on update / FALSE if none
	 */
	public function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE; // up to date
		}

		// update table row with current version
		ee()->db->where('class', __CLASS__);
		ee()->db->update('extensions', array('version' => $this->version));
	}

	function settings()
	{
		// Get all user groups that have access to the control panel
		$query = ee()->db->select('group_id, group_title')->get_where('member_groups', array('can_access_cp' => 'y'));

		$groups = array();

		foreach ($query->result() as $row)
		{
				$groups[$row->group_id] = $row->group_title;
		}

		$settings = array();

		// Settings for hiding buttons
		$settings['hide_files_button'] = array('ms', $groups, '');
		$settings['hide_developer_button'] = array('ms', $groups, '');
		$settings['hide_preview_button'] = array('ms', $groups, '');

		return $settings;
	}

	// ------------------------------------------------------
    //
    /**
     * Method for cp_css_end hook
     *
     * Add custom CSS to every Control Panel page:
     *
     * @access     public
     * @param      array
     * @return     array
     */
    public function cp_css_end()
    {
		//Set Defaults
		$css = '';
		$group_id = ee()->session->userdata('group_id');

		//Bring in all our custom CSS files
		$cp_style = file_get_contents( PATH_THIRD . '/control_panel_tweaks/css/cp.css');
		$wygwam_style = file_get_contents( PATH_THIRD . '/control_panel_tweaks/css/wygwam.css');
		$hide_files_button = file_get_contents( PATH_THIRD . '/control_panel_tweaks/css/hide-files-button.css');
		$hide_developer_button = file_get_contents( PATH_THIRD . '/control_panel_tweaks/css/hide-developer-button.css');
		$hide_preview_button = file_get_contents( PATH_THIRD . '/control_panel_tweaks/css/hide-preview-button.css');
		$upper_nav = file_get_contents( PATH_THIRD . '/control_panel_tweaks/css/upper-nav.css');

		//Hide buttons
		if (isset($this->settings['hide_files_button'])) {
			foreach ($this->settings['hide_files_button'] as $row)
			{
				if ($row == $group_id) {
					$css .= $hide_files_button;
				}
			}
		}

		if (isset($this->settings['hide_developer_button'])) {
			foreach ($this->settings['hide_developer_button'] as $row)
			{
				if ($row == $group_id) {
					$css .= $hide_developer_button;
				}
			}
		}

		if (isset($this->settings['hide_preview_button'])) {
			foreach ($this->settings['hide_preview_button'] as $row)
			{
				if ($row == $group_id) {
					$css .= $hide_preview_button;
				}
			}
		}

        $css .= $cp_style . $structure_style . $assets_style . $wygwam_style . $upper_nav;

        $user_css_location = ee()->config->item('control_panel_tweaks_user_css');

        if($user_css_location) {
            $user_css = file_get_contents($user_css_location);
            $css .= $user_css;
        }

		$other_css = [];

		//If another extension shares the same hook
		if (ee()->extensions->last_call !== false) {
			$other_css[] = ee()->extensions->last_call;
		}

    	return implode('', $other_css) . $css;
    }

	// --------------------------------------------------------------------

    /**
     * Add extension hook
     *
     * @access     private
     * @param      string
     * @param      integer
     * @return     void
     */
    private function _add_hook($name, $priority = 10)
    {
        ee()->db->insert('extensions',
            array(
                'class'    => __CLASS__,
                'method'   => $name,
                'hook'     => $name,
                'settings' => '',
                'priority' => $priority,
                'version'  => $this->version,
                'enabled'  => 'y'
            )
        );
	}
}