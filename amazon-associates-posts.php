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
	private $options_name;



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

	private function _dump($to_dump) {
		echo "<pre>";
		var_dump($to_dump);
		echo "</pre>";
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
		$this->plugin_name	=	'amazon-associates-for-posts';		
		$this->plugin_display_name	=	$this->_make_display_name($this->plugin_name);
		$this->options_name	=	'dp_aap_options';
	}


	/*
	|-----------------------------
	|	Display Menu Pages
	|-----------------------------
	*/
	public function _display_menu_page() {
		echo "<div class='wrap'>";
		echo "<h2>" . $this->plugin_display_name . "</h2>";
		echo "<p>This plugin helps you to insert custom Amazon Assocaites image links into your post content.</p>";
		echo "<p>It uses a different approach from pasting in complete advert widgets.</p>";
		echo "<p>It separates out the elements of an amazon associates referer link and places it into your post content wrapped in semantic html.</p>";
		echo "<p>By entering the Product Title, Referer Link, Product Type and uploading / attaching the product image it will insert to referal image link into you post.</p>";
		echo "</div>";
	}

	public function _display_submenu_page() {

		global $wpdb;

		if (!empty($_POST[$this->options_name])) {
			foreach ($_POST[$this->options_name] as $key => $value) {
				
				 if (!empty($value['remove']) && $value['remove'] == "on" ) {
					delete_post_meta( $key, $this->options_name );
					continue;
				}

				if (!empty($value['product_link'])) {
					update_post_meta( $key, $this->options_name, $value);
				}
				// check for a delete fields flag to unattach fields from the post
			}
		}

		echo "<div class='wrap'>";

		$paged		=	(isset($_GET['pageno']))?$_GET['pageno']:1;
		$per_page	=	5;

		$args		=	array('post_type' => 'post', 'posts_per_page' => '-1');
		$all_posts	=	get_posts( $args );

		$total_pages = ceil(count($all_posts) / $per_page);
		unset($all_posts);

		// Get all posts and list them with empty amazon fields.
		$args	=	array(
			'post_type'	=>	'post',
			'posts_per_page'	=>	$per_page,
			'paged'	=> $paged
			);
		$posts	=	get_posts($args);

		echo "<div class='alignleft'>";
		echo "<div class='tablenav-pages'>";
		echo "<span class='pagination-links'>";
		foreach (range(1, (int)$total_pages) as $key => $value) {
			$inline_style = ($paged == $value)? "class='button-primary'":"class='button-secondary'";
			echo '<a ' . $inline_style  . ' href="admin.php?page=' . $this->plugin_name . '-links&pageno=' . $value . '">' . $value . '</a> ';
		}
		echo "</span>";
		echo "</div>";
		echo "</div>";
		echo "<br><br>";

		if (!empty($posts)) {
			echo "<form action='' method='post' enctype='multipart/form-data'>";
			settings_fields($this->options_name);
			?>
			<table class='wp-list-table widefat'>
				<thead>
					<tr>
						<th>Clear</th>
						<th>Post Title</th>
						<th>Product Title</th>
						<th>Referer Link</th>
						<th>Product Type</th>
						<th>Choose New Image</th>
						<th>Current Image</th>
						<th>Status</th>
					</tr>
				</thead>
			<?php
			$alternate_class	=	'';
			foreach ($posts as $post) {
				$alternate_class	=	(empty($alternate_class))? 'alternate': '';
				$post_meta	=	get_post_meta( $post->ID, $this->options_name , TRUE );
				?>
				<tr class='<?php echo $alternate_class; ?>'>
					<td><input type='checkbox' name='<?php echo $this->options_name;?>[<?php echo $post->ID; ?>][remove]'></td>
					<td class='post-title'><strong><?php echo $post->post_title; ?></strong></td>
					<td><input type='text' name='<?php echo $this->options_name; ?>[<?php echo $post->ID; ?>][product_name]' value='<?php echo (empty($post_meta['product_name'])) ? '': $post_meta['product_name']; ?>' ></td>
					<td><input type='text' name='<?php echo $this->options_name; ?>[<?php echo $post->ID; ?>][product_link]' value='<?php echo (empty($post_meta['product_link'])) ? '': $post_meta['product_link']; ?>'></td>
					<td><input type='text' name='<?php echo $this->options_name; ?>[<?php echo $post->ID; ?>][product_type]' value='<?php echo (empty($post_meta['product_type'])) ? '': $post_meta['product_type']; ?>'></td>
					<td><input type='file' style='vertical-align:top;' class='aap-image' name='<?php echo $this->options_name; ?>[<?php echo $post->ID; ?>][product_image]' id='<?php echo $this->options_name; ?>-product-image_button_<?php echo $post->ID; ?>' />
					</td>
					<td>
						<?php
						if (!empty($post_meta['product_image_id'])) {
							$img_array	=	wp_get_attachment_image_src( $post_meta['product_image_id'], 'thumbnail' );
							echo "<img src='" . $img_array[0] . "' height='100px'>";
						} else {
							echo "<img src='' id='" . $this->options_name . "-product-image_" . $post->ID . "' height='100px' width='100px'>";
						}
						?>
						<input type='hidden' name='<?php echo $this->options_name; ?>[<?php echo $post->ID; ?>][product_image_id]' id='<?php echo $this->options_name; ?>-product-image_<?php echo $post->ID; ?>_id'  value='<?php echo (empty($post_meta['product_image_id']))? '': $post_meta['product_image_id']; ?>'>
					</td>
					<td>
						<select type='text' name='<?php echo $this->options_name; ?>[<?php echo $post->ID; ?>][product_status]'><?php
						if (empty($post_meta['product_status']) || $post_meta['product_status'] == '0') {
							echo "<option value='0' selected>Disabled</option>";
							echo "<option value='1'>Enabled</option>";
						} else {
							echo "<option value='0'>Disabled</option>";
							echo "<option value='1' selected>Enabled</option>";
						}
					?></select></td>
				</tr><?php
			}
			?>
				<tfoot>
					<tr>
						<th>Clear</th>
						<th>Post Title</th>
						<th>Product Title</th>
						<th>Referer Link</th>
						<th>Product Type</th>
						<th>Choose New Image</th>
						<th>Current Image</th>
						<th>Status</th>
					</tr>
				</tfoot>
			</table>
			<br>
			<input type='submit' value='Save AAP Settings' class='button-primary alignright'>
			</form>
			</div>
			<?php include(__DIR__ . '/inc/javascript.php'); ?>
			<?php
		}
	}


	/*
	|-----------------------------
	|	Public Functions
	|-----------------------------
	*/
	public function _create_menu_pages() {
		//add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
		add_menu_page( $this->plugin_display_name, $this->_make_abbr($this->plugin_display_name) . ' Settings', 'manage_options', $this->plugin_name, array($this, '_display_menu_page'), '' );
		add_submenu_page( $this->plugin_name, 'Amazon Links Settings', 'Amazon Links', 'manage_options', $this->plugin_name . '-links', array($this, '_display_submenu_page') );
	}

	public function _register_settings_and_fields() {
		# Needed ?
		register_setting($this->options_name, $this->options_name);
	}

	public function _insert_aap_image($the_content) {
		global $post;
		if (is_single()) {
			$post_meta	=	get_post_meta( $post->ID, $this->options_name, TRUE );
			if ($post_meta && $post_meta['product_status']) {
				$img_array	=	wp_get_attachment_image_src( $post_meta['product_image_id'], 'medium' );
				$image_string	=	'<figure style="float:right;margin-left:2em;width:' . $img_array[1] . 'px;">';
				$image_string	.=	'<a href="' . $post_meta['product_link'] . '">';
				$image_string	.=	'<img style="display:block;margin:0 auto .5em;" src="' . $img_array[0] . '">';
				$image_string	.=	'<figcaption>Buy ' . $post_meta['product_name'] . '</figcaption>';
				$image_string	.=	'</a>';
				$image_string	.=	'</figure>';
				$the_content	=	$image_string . $the_content;
			}
		}
		return $the_content;
	}

	public function _test_attachment_filter($thumb) {
		global $post;
		$post_meta	=	get_post_meta( $post->ID, $this->options_name, TRUE );
		if (is_single() && ( $post_meta && $post_meta['product_status'] ) )
			return null;
		return $thumb;
	}

	public function _post_meta_boxes($post) {
		$post_meta	=	get_post_meta($post->ID, $this->options_name, TRUE);
		#$this->_dump($post_meta);
		?>
		<p>
			<label for="<?php echo $this->options_name; ?>-remove_<?php echo $post->ID; ?>">Tick to Remove</label>
			<input type='checkbox' name='<?php echo $this->options_name;?>[<?php echo $post->ID; ?>][remove]' id='<?php echo $this->options_name; ?>-remove_<?php echo $post->ID; ?>'>
		</p>
		<p>
		<?php
		if (!empty($post_meta['product_image_id'])) {
			$img_array	=	wp_get_attachment_image_src( $post_meta['product_image_id'], 'thumbnail' );
			echo "<img src='" . $img_array[0] . "' height='100px'>";
		} else {
			echo "<img src='' id='" . $this->options_name . "-product-image_" . $post->ID . "' height='100px' width='100px'>";
		}
		?>
		</p>
		<p>
			<input type='file' style='vertical-align:top;' class='aap-image' name='<?php echo $this->options_name; ?>[<?php echo $post->ID; ?>][product_image]' id='<?php echo $this->options_name; ?>-product-image_button_<?php echo $post->ID; ?>' />
			<input type='hidden' name='<?php echo $this->options_name; ?>[<?php echo $post->ID; ?>][product_image_id]' id='<?php echo $this->options_name; ?>-product-image_<?php echo $post->ID; ?>_id'  value='<?php echo (empty($post_meta['product_image_id']))? '': $post_meta['product_image_id']; ?>'>
		</p>
		<p>
			<label for="<?php echo $this->options_name; ?>-product-name-<?php echo $post->ID; ?>">Product Name</label>
			<input type='text' class='widefat' name='<?php echo $this->options_name; ?>[<?php echo $post->ID; ?>][product_name]' id='<?php echo $this->options_name; ?>-product-name-<?php echo $post->ID; ?>' value='<?php echo (empty($post_meta['product_name']))?'':$post_meta['product_name']; ?>'>
		</p>
		<p>
			<label for="<?php echo $this->options_name; ?>-product-link-<?php echo $post->ID; ?>">Referrer Link</label>
			<input type='text' class='widefat' name='<?php echo $this->options_name; ?>[<?php echo $post->ID; ?>][product_link]' id='<?php echo $this->options_name; ?>-product-link-<?php echo $post->ID; ?>' value='<?php echo (empty($post_meta['product_link']))?'':$post_meta['product_link']; ?>'>
		</p>
		<p>
			<label for="<?php echo $this->options_name; ?>-product-type-<?php echo $post->ID; ?>">Product Type</label>
			<input type='text' class='widefat' name='<?php echo $this->options_name; ?>[<?php echo $post->ID; ?>][product_type]' id='<?php echo $this->options_name; ?>-product-type-<?php echo $post->ID; ?>' value='<?php echo (empty($post_meta['product_type']))? '': $post_meta['product_type']; ?>'>
		</p>
		<p>
			<select type='text' name='<?php echo $this->options_name; ?>[<?php echo $post->ID; ?>][product_status]'><?php
				if (empty($post_meta['product_status']) || $post_meta['product_status'] == '0') {
					echo "<option value='0' selected>Disabled</option>";
					echo "<option value='1'>Enabled</option>";
				} else {
					echo "<option value='0'>Disabled</option>";
					echo "<option value='1' selected>Enabled</option>";
				}
			?></select>
		</p>
		<?php include(__DIR__ . '/inc/javascript.php'); ?>
		<?php
	}

	public function _meta_boxes() {
		// id, title, cb, page/post-type, priority, cb args
		add_meta_box($this->plugin_name . '-name', $this->plugin_display_name, array($this, '_post_meta_boxes'), 'post');
	}

	public function _save_meta_boxes($id) {
			#$this->_dump($id);
		if (isset($_POST[$this->options_name])) {
			foreach ($_POST[$this->options_name] as $key => $value) {

				if (isset($value['remove']) && $value['remove'] == 'on') {
					delete_post_meta( $id, $this->options_name);
					continue;
				}
				
				update_post_meta($id, $this->options_name, $value);
			}
		}
	}

	public function _load_wp_media_files() {
		wp_enqueue_media();
	}

	public function __construct() {
		$this->_set_config();
		add_action('admin_enqueue_scripts', array($this, '_load_wp_media_files') );
		add_action('admin_menu', array($this, '_create_menu_pages'));
		add_action('admin_init', array($this, '_register_settings_and_fields'));
		add_action('add_meta_boxes', array($this, '_meta_boxes'));
		add_action('save_post', array($this, '_save_meta_boxes'));
		add_filter('the_content', array($this, '_insert_aap_image'));
		add_filter('post_thumbnail_html', array($this, '_test_attachment_filter'));		
	}

}
$dpamazon = new AmazonAssociatesPosts();
/*
Functionality to add
Admin: paginate posts

choose size of advert
choose position of the advert.
disable / keep featured image when attaching a product

Meta boxes in post admin pages with same data

clean up the code a bit.
*/