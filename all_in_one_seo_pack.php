<?php

/*
Plugin Name: All in One SEO Pack
Plugin URI: http://wp.uberdose.com/2007/03/24/all-in-one-seo-pack/
Description: Out-of-the-box SEO for your Wordpress blog.
Version: 0.2
Author: some guy
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
	
 	var $version = "0.2";
 	
 	var $minimum_excerpt_length = 10;

	function start() {
		ob_start();
	}

	function wp_head() {
		global $post;
		$meta_string = null;
		
		echo "<!-- all in one seo pack $this->version -->\n";
		
		$keywords = $this->get_all_keywords();

		if (is_single() || is_page()) {
			$description = stripslashes(get_the_excerpt());
			if (isset($description) && strlen($description) > $this->minimum_excerpt_length) {
				if (isset($meta_string)) {
					$meta_string .= "\n";
				}
				$meta_string .= sprintf("<meta name=\"description\" content=\"%s\"/>", $description);
			} else {
	            $description = stripslashes(get_post_meta($post->ID, "description", true));
				if (isset($description) && strlen($description) > $this->minimum_excerpt_length) {
					if (isset($meta_string)) {
						$meta_string .= "\n";
					}
					$meta_string .= sprintf("<meta name=\"description\" content=\"%s\"/>", $description);
				}
			}
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
	
}

$aiosp = new All_in_One_SEO_Pack();
add_action('wp_head', array($aiosp, 'wp_head'));

$aiosp->start();

?>
