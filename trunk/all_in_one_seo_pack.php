<?php

/*
Plugin Name: All in One SEO Pack
Plugin URI: http://wp.uberdose.com/2007/03/24/all-in-one-seo-pack/
Description: Out-of-the-box SEO for your Wordpress blog.
Version: 0.5.4
Author: uberdose
Author URI: http://wp.uberdose.com/
*/

/* Copyright (C) 2007 Dirk Zimmermann (dirk AT uberdose DOT com)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA */
 
class All_in_One_SEO_Pack {
	
 	var $version = "0.5.4";
 	
 	var $minimum_excerpt_length = 1;

	function start() {
		ob_start();
	}

	function wp_head() {
		global $post;
		$meta_string = null;
		
		echo "<!-- all in one seo pack $this->version -->\n";
		
		$keywords = $this->get_all_keywords();

		if (is_single() || is_page()) {
			$description = trim(stripslashes(get_the_excerpt()));
			if ($description == "Share This") {
				// comes from share this plugin, ignore
				unset($description);
			}
			if (!isset($description) || empty($description)) {
	            $description = trim(stripslashes(get_post_meta($post->ID, "description", true)));
			}
		} else if (is_home()) {
			$description = trim(stripslashes(get_option('aiosp_home_description')));
		}
		
		if (isset($description) && strlen($description) > $this->minimum_excerpt_length) {
			if (isset($meta_string)) {
				$meta_string .= "\n";
			}
			$meta_string .= sprintf("<meta name=\"description\" content=\"%s\"/>", $description);
		}

		if (isset ($keywords) && !empty($keywords)) {
			if (isset($meta_string)) {
				$meta_string .= "\n";
			}
			$meta_string .= sprintf("<meta name=\"keywords\" content=\"%s\"/>\n", $keywords);
		}

		if(!is_home() && !is_single() && !is_page()) {
			if (isset($meta_string)) {
				$meta_string .= "\n";
			}
			$meta_string = '<meta name="robots" content="noindex,follow" />';
		}
		
		// title
		$header = ob_get_contents();
		ob_end_clean();
		$title = wp_title('', false);
		global $s;
		if (is_search() && isset($s) && !empty($s)) {
			$title = attribute_escape(stripslashes($s));
		}		
		if (isset($title) && !empty($title)) {
			$title .= ' | ' . get_bloginfo('name');
			$title = trim($title);
			$header = preg_replace("/<title>.*<\/title>/", "<title>$title</title>", $header);
		}
		gzip_compression();
		print($header);

		if ($meta_string != null) {
			echo $meta_string;
		}
	}
	
	function get_all_keywords() {
		global $posts;

	    if (is_array($posts)) {
	        foreach ($posts as $post) {
	            if ($post) {
	                $categories = get_the_category($post->ID);
	                foreach ($categories as $category) {
	                    if (isset($keywords) && !empty($keywords)) {
	                        $keywords .= ',';
	                    }
	                	$keywords .= $category->cat_name;
	                }
	                $keywords_a = $keywords_i = null;
	                $description_a = $description_i = null;
	                $id = $post->ID;
		            $keywords_i = stripslashes(get_post_meta($post->ID, "keywords", true));
	                if (isset($keywords_i) && !empty($keywords_i)) {
	                    if (isset($keywords) && !empty($keywords)) {
	                        $keywords .= ',';
	                    }
	                    $keywords .= $keywords_i;
	                }
	            }
	        }
	    }
	    
	    return $this->get_unique_keywords($keywords);
	}

	function get_unique_keywords($keywords) {
		$keywords_ar = array_unique(explode(',', $keywords));
		return implode(',', $keywords_ar);
	}
	
	function the_content ($content) {
		return $content . " blub";
	}
	
	function post_meta_tags($id) {
	    $awmp_edit = $_POST["aiosp_edit"];
	    if (isset($awmp_edit) && !empty($awmp_edit)) {
		    $keywords = $_POST["aiosp_keywords"];

		    delete_post_meta($id, 'keywords');

		    if (isset($keywords) && !empty($keywords)) {
			    add_post_meta($id, 'keywords', $keywords);
		    }
	    }
	}

	function add_meta_tags_textinput() {
	    global $post;
	    $keywords = stripslashes(get_post_meta($post->ID, 'keywords', true));
	    $description = stripslashes(get_post_meta($post->ID, 'description', true));
		?>
		<input value="aiosp_edit" type="hidden" name="aiosp_edit" />
		<table style="margin-bottom:40px; margin-top:30px;">
		<tr><th style="text-align:left;" colspan="2">The recommended way to add META keywords is through categories, but you can also add some more here.
		<br/>See <a href="http://wp.uberdose.com/2007/03/24/all-in-one-seo-pack/#comment-1577">this discussion</a>.</th></tr>
		<th scope="row" style="text-align:right;"><?php _e('Keywords (comma separated):') ?></th>
		<td><input value="<?php echo $keywords ?>" type="text" name="aiosp_keywords" size="50"/></td>
		</tr>
		</table>
		<?php
	}

	function admin_menu() {
		add_submenu_page('options-general.php', __('All in One SEO'), __('All in One SEO'), 5, __FILE__, array($this, 'plugin_menu'));
	}
	
	function plugin_menu() {
		$message = null;
		$message_updated = __("Home description updated.");
		
		// update options
		if ($_POST['action'] && $_POST['action'] == 'aiosp_update') {
			$message = $message_updated;
			update_option('aiosp_home_description', $_POST['aiosp_home_description']);
			wp_cache_flush();
		}

?>
<?php if ($message) : ?>
<div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
<?php endif; ?>
<div id="dropmessage" class="updated" style="display:none;"></div>
<div class="wrap">
<h2><?php _e('All in One SEO Plugin Options'); ?></h2>
<p><?php _e('For feedback, help etc. please click <a title="Homepage for All in One SEO Plugin" href="http://wp.uberdose.com/2007/03/24/all-in-one-seo-pack/#respond">here</a>.') ?></p>
<p><?php _e('Your home description here:') ?></p>
<form name="dofollow" action="" method="post">
<table>
<tr><th scope="row" style="text-align:right; vertical-align:top;"><?php _e('Home Description:')?></td>
<td>
<textarea cols="80" rows="10" name="aiosp_home_description"><?php echo stripcslashes(get_option('aiosp_home_description')); ?></textarea></td></tr>
</table>
<p class="submit">
<input type="hidden" name="action" value="aiosp_update" /> 
<input type="hidden" name="page_options" value="aiosp_home_description" /> 
<input type="submit" name="Submit" value="<?php _e('Update Options')?> &raquo;" /> 
</p>
</form>
</div>
<?php
	
	} // plugin_menu

}

add_option("aiosp_home_description", null, __('All in One SEO Plugin Home Description'), 'yes');

$aiosp = new All_in_One_SEO_Pack();
add_action('wp_head', array($aiosp, 'wp_head'));

add_action('simple_edit_form', array($aiosp, 'add_meta_tags_textinput'));
add_action('edit_form_advanced', array($aiosp, 'add_meta_tags_textinput'));
add_action('edit_page_form', array($aiosp, 'add_meta_tags_textinput'));

add_action('edit_post', array($aiosp, 'post_meta_tags'));
add_action('publish_post', array($aiosp, 'post_meta_tags'));
add_action('save_post', array($aiosp, 'post_meta_tags'));
add_action('edit_page_form', array($aiosp, 'post_meta_tags'));

add_action('admin_menu', array($aiosp, 'admin_menu'));

// used for some debugging
//add_action('the_content', array($aiosp, 'the_content'));

$aiosp->start();

?>
