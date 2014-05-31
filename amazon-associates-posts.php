<?php
/*
 *
 * Plugin Name: Amazon Associates Posts
 * Plugin URI: http://davidpeach.co.uk
 * Description: A WordPress plugin for adding custom Amazon Associates links into your posts.
 * Version: 1.0
 * Author: David Peach
 * Author URI: http://davidpeach.co.uk
 * License: GPL2
 *
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */

class AmazonAssociatesPosts {

	/*
	|-----------------------------
	|	Private Config
	|-----------------------------
	*/
	private $version;
	private $plugin_name;
	private $plugin_display_name;



	/*
	|-----------------------------
	|	Private Functions
	|-----------------------------
	*/
	private function _init() {
		add_action('admin_menu', array($this, '_create_menu_pages') );
	}

	private function _make_display_name($name) {
		return ucwords(str_replace(array('-'), array(' '), $name));
	}

	private function _make_abbr($sentance) {
		$words	=	explode(' ', $sentance);
		$abbr	=	'';
		foreach ($words as $word) {
			$abbr .= strtoupper($word[0]);
		}
		return $abbr;
	}

	private function _set_config() {
		$this->version		=	'1.0';
		$this->plugin_name	=	'amazon-associates-posts';		
		$this->plugin_display_name	=	$this->_make_display_name($this->plugin_name);
	}


	/*
	|-----------------------------
	|	Display Menu Pages
	|-----------------------------
	*/
	public function _display_menu_page() {
		echo "<div class='wrap'>";
		echo "<h2>" . $this->plugin_display_name . "</h2>";
		echo "<p>This plugin allows you to insert Amazon Assocaites image links into your post content.</p>";
		echo "<p>You have the freedom&#8202;&mdash;&#8202;on a per post basis&#8202;&mdash;&#8202;to add your chosen amazon associate image links. You may then style it however you wish.</p>";
		echo "<p>For each post you have written, you fill in the Amazon Associates fields for the 'referer link', 'product type', 'Product title' and the position you wish it to appear in the posts. The plugin will then insert it into your posts' content.</p>";
		echo "</div>";
	}


	/*
	|-----------------------------
	|	Public Functions
	|-----------------------------
	*/
	public function _create_menu_pages() {
		//add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
		add_menu_page( $this->plugin_display_name, $this->_make_abbr($this->plugin_display_name) . ' Settings', 'manage_options', $this->plugin_name, array($this, '_display_menu_page'), '' );
	}

	public function __construct() {
		$this->_set_config();
		$this->_init();
	}

}
$dpamazon = new AmazonAssociatesPosts();