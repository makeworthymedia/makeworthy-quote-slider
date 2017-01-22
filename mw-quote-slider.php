<?php
/**
 * @package MW_Quote_Slider
 * @version 1.1
 */
/*
Plugin Name: Quote Slider
Plugin URI: https://www.makeworthymedia.com/
Description: Creates widget that displays rotating quotes. Requires Advanced Custom Fields be installed. Uses Slick slider by Ken Wheeler http://kenwheeler.github.io/slick
Version: 1.1
Author: Makeworthy Media
Author URI: https://www.makeworthymedia.com/
License: GPL2
*/

/*  Copyright 2016 Jennette Fulda  (email : contact@makeworthymedia.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');

// Add custom script to footer
add_action( 'wp_enqueue_scripts', 'mwqs_quote_enqueued_assets' );
function mwqs_quote_enqueued_assets() {
	wp_enqueue_script('slick-js', plugin_dir_url( __FILE__ ) . 'slick/slick.min.js', array(), '1.5.9', true );
	wp_enqueue_style( 'slick', plugin_dir_url( __FILE__ ) . 'slick/slick.css', '', '1.5.9' );
	wp_enqueue_style( 'slick-theme', plugin_dir_url( __FILE__ ) . 'slick/slick-theme.css', '', '1.5.9' );
}

//* Add stuff to <head>
//add_action ('wp_head', 'mwqs_quoteadd_to_head');
function mwqs_quoteadd_to_head() {
}

// Register Quote custom post type
function mwqs_quote_post_type() {

	$labels = array(
		'name'                  => 'Quotes',
		'singular_name'         => 'Quote',
		'menu_name'             => 'Quotes',
		'name_admin_bar'        => 'Quote',
		'archives'              => 'Quote Archives',
		'parent_item_colon'     => 'Parent Quote:',
		'all_items'             => 'All Quotes',
		'add_new_item'          => 'Add New Quote',
		'add_new'               => 'Add New',
		'new_item'              => 'New Quote',
		'edit_item'             => 'Edit Quote',
		'update_item'           => 'Update Quote',
		'view_item'             => 'View Quote',
		'search_items'          => 'Search Quotes',
		'not_found'             => 'Not found',
		'not_found_in_trash'    => 'Not found in Trash',
		'featured_image'        => 'Featured Image',
		'set_featured_image'    => 'Set featured image',
		'remove_featured_image' => 'Remove featured image',
		'use_featured_image'    => 'Use as featured image',
		'insert_into_item'      => 'Insert into quote',
		'uploaded_to_this_item' => 'Uploaded to this quote',
		'items_list'            => 'Items list',
		'items_list_navigation' => 'Items list navigation',
		'filter_items_list'     => 'Filter items list',
	);
	$args = array(
		'label'                 => 'Quote',
		'description'           => 'Quotes',
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail', ),
		'taxonomies'            => array( 'category', 'post_tag' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 25,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,		
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
	);
	register_post_type( 'quote', $args );

}
add_action( 'init', 'mwqs_quote_post_type', 0 );



// Creating the widget 
class mw_quote_widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			// Base ID of your widget
			'mw_quote_widget', 

			// Widget name will appear in UI
			__('Quote Slider', 'mw_quote_widget_domain'), 

			// Widget description
			array( 'description' => __( 'Widget displays a quote slider', 'mw_quote_widget_domain' ), ) 
		);
	}

	// Creating widget front-end
	// This is where the action happens
	public function widget( $args, $instance ) {
		// Initialize slider. Would prefer to do this in <head> but I don't know how within a widget.
		
		$adaptiveHeight = ( isset( $instance['adaptiveHeight'] ) ) ? $instance['adaptiveHeight'] : 'false';
		$autoplay = ( isset( $instance['autoplay'] ) ) ? $instance['autoplay'] : 'false';
		$autoplaySpeed = $instance['autoplaySpeed'] ? $instance['autoplaySpeed'] : '3000';
		$arrows = ( isset( $instance['arrows'] ) ) ? $instance['arrows'] : 'true';
		$infinite = ( isset( $instance['infinite'] ) ) ? $instance['infinite'] : 'true';
		$orderby = $instance['orderby'] ? $instance['orderby'] : 'rand';
		$order = $instance['order'] ? $instance['order'] : 'DESC';
		
	?>
<script>
jQuery( document ).ready(function( $ ) {
	$('#mw-quote-wrapper-<?php echo $this->id; ?>').slick({
		speed: 500,
		cssEase: 'linear',
		fade: true,
		adaptiveHeight: <?php echo $adaptiveHeight; ?>,
		autoplay: <?php echo $autoplay; ?>,
		autoplaySpeed: <?php echo $autoplaySpeed; ?>,
		arrows: <?php echo $arrows; ?>,
		infinite: <?php echo $infinite; ?>,
	});
});
</script>
<?php 
		
		$title = apply_filters( 'widget_title', $instance['title'] );
		
		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if ( ! empty( $title ) )
		echo $args['before_title'] . $title . $args['after_title'];

		$category = empty($instance['category']) ? '' : $instance['category'];
	
		// This is where you run the code and display the output
		$queryArgs = array(
			'post_type' => 'quote',
			'cat' => $category,
			'orderby' => $orderby,
			'order' => $order,
		);
		$the_query = new WP_Query($queryArgs);
		if ($the_query->have_posts()) :
			echo '<div class="mw-quote-wrapper" id="mw-quote-wrapper-'. $this->id . '">';
			while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
				<div class="mw-quote">
					<span class="mw-quote-content"><?php echo the_content(); ?></span>
					<?php if($author = get_field('mw_quote_author')) : ?><span class="mw-quote-author"><?php echo $author; ?></span><?php endif; ?>
				</div>
			<?php endwhile;
			echo '</div>';
		endif;
		wp_reset_postdata();

		echo $args['after_widget'];
	}
		
	// Widget Backend 
	public function form( $instance ) {

		// PART 1: Extract the data from the instance variable
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = $instance['title'];
		$category = $instance['category'];
		$adaptiveHeight = ( isset( $instance['adaptiveHeight'] ) ) ? $instance['adaptiveHeight'] : 'false';
		$autoplay = ( isset( $instance['autoplay'] ) ) ? $instance['autoplay'] : 'false';
		$autoplaySpeed = $instance['autoplaySpeed'] ? $instance['autoplaySpeed'] : '3000';
		$arrows = ( isset( $instance['arrows'] ) ) ? $instance['arrows'] : 'true';
		$infinite = ( isset( $instance['infinite'] ) ) ? $instance['infinite'] : 'true';
		$orderby = $instance['orderby'] ? $instance['orderby'] : 'rand';
		$order = $instance['order'] ? $instance['order'] : 'DESC';

		// PART 2-3: Display the fields
     ?>
	<!-- Widget Title field START -->
	<p>
		<label for="<?php echo $this->get_field_id('title'); ?>">Title: 
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"  type="text"
			name="<?php echo $this->get_field_name('title'); ?>" 
			value="<?php echo esc_attr($title); ?>" />
		</label>
	</p>

     <!-- Widget Category field START -->
	<p>
		<label for="<?php echo $this->get_field_id('category'); ?>">Category: 
		<select class='widefat' id="<?php echo $this->get_field_id('category'); ?>"
		name="<?php echo $this->get_field_name('category'); ?>" type="text">
			<option value="">--All Categories--</option>
			<?php echo mwqs_create_tax_dropdown('category', $category); ?>
		</select>                
		</label>
	</p>
	
	<!-- Widget Adaptive Height field START -->
	<p>
		<label for="<?php echo $this->get_field_id('adaptiveHeight'); ?>">Adaptive Height: 
		<select class='widefat' id="<?php echo $this->get_field_id('adaptiveHeight'); ?>"
		name="<?php echo $this->get_field_name('adaptiveHeight'); ?>" type="text">
			<option value='false'<?php echo ($adaptiveHeight=='false')?'selected':''; ?>>Off</option>
			<option value='true'<?php echo ($adaptiveHeight=='true')?'selected':''; ?>>On</option> 
		</select>                
	</p>

	<!-- Widget autoplay field START -->
	<p>
		<label for="<?php echo $this->get_field_id('autoplay'); ?>">Autoplay: 
		<select class='widefat' id="<?php echo $this->get_field_id('autoplay'); ?>"
		name="<?php echo $this->get_field_name('autoplay'); ?>" type="text">
			<option value='false'<?php echo ($autoplay=='false')?'selected':''; ?>>Off</option>
			<option value='true'<?php echo ($autoplay=='true')?'selected':''; ?>>On</option> 
		</select>                
	</p>
	
	<!-- Widget autoplaySpeed field START -->
	<p>
		<label for="<?php echo $this->get_field_id('autoplaySpeed'); ?>">Autoplay Speed in milliseconds (default: 3000): 
		<input class="widefat" id="<?php echo $this->get_field_id('autoplaySpeed'); ?>"  type="text"
			name="<?php echo $this->get_field_name('autoplaySpeed'); ?>" 
			value="<?php echo esc_attr($autoplaySpeed); ?>" />
		</label>
	</p>

	<!-- Widget Arrows field START -->
	<p>
		<label for="<?php echo $this->get_field_id('arrows'); ?>">Arrows: 
		<select class='widefat' id="<?php echo $this->get_field_id('arrows'); ?>"
		name="<?php echo $this->get_field_name('arrows'); ?>" type="text">
			<option value='false'<?php echo ($arrows=='false')?'selected':''; ?>>Off</option>
			<option value='true'<?php echo ($arrows=='true')?'selected':''; ?>>On</option> 
		</select>                
	</p>
	
	<!-- Widget Infinite field START -->
	<p>
		<label for="<?php echo $this->get_field_id('infinite'); ?>">Infinite: 
		<select class='widefat' id="<?php echo $this->get_field_id('infinite'); ?>"
		name="<?php echo $this->get_field_name('infinite'); ?>" type="text">
			<option value='false'<?php echo ($infinite=='false')?'selected':''; ?>>Off</option>
			<option value='true'<?php echo ($infinite=='true')?'selected':''; ?>>On</option> 
		</select>                
	</p>

	<!-- Widget Orderby field START -->
	<p>
		<label for="<?php echo $this->get_field_id('orderby'); ?>">Order By: 
		<select class='widefat' id="<?php echo $this->get_field_id('orderby'); ?>"
		name="<?php echo $this->get_field_name('orderby'); ?>" type="text">
			<option value='rand'<?php echo ($orderby=='rand')?'selected':''; ?>>Random</option> 
			<option value='date'<?php echo ($orderby=='date')?'selected':''; ?>>Date</option>
			<option value='title'<?php echo ($orderby=='title')?'selected':''; ?>>Title</option> 
		</select>                
	</p>
	
	<!-- Widget Order field START -->
	<p>
		<label for="<?php echo $this->get_field_id('order'); ?>">Order: 
		<select class='widefat' id="<?php echo $this->get_field_id('order'); ?>"
		name="<?php echo $this->get_field_name('order'); ?>" type="text">
			<option value='DESC'<?php echo ($order=='DESC')?'selected':''; ?>>Descending (3, 2, 1; c, b, a)</option>
			<option value='ASC'<?php echo ($order=='ASC')?'selected':''; ?>>Ascending (1, 2, 3; a, b, c)</option> 
		</select>                
	</p>
	<?php 
		
	}
	
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {		
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['category'] = ( ! empty( $new_instance['category'] ) ) ? strip_tags( $new_instance['category'] ) : '';
		$instance['adaptiveHeight'] = ( ! empty( $new_instance['adaptiveHeight'] ) ) ? strip_tags( $new_instance['adaptiveHeight'] ) : '';
		$instance['autoplay'] = ( ! empty( $new_instance['autoplay'] ) ) ? strip_tags( $new_instance['autoplay'] ) : '';
		$instance['autoplaySpeed'] = ( ! empty( $new_instance['autoplaySpeed'] ) ) ? strip_tags( $new_instance['autoplaySpeed'] ) : '';
		$instance['arrows'] = ( ! empty( $new_instance['arrows'] ) ) ? strip_tags( $new_instance['arrows'] ) : '';
		$instance['infinite'] = ( ! empty( $new_instance['infinite'] ) ) ? strip_tags( $new_instance['infinite'] ) : '';
		$instance['orderby'] = ( ! empty( $new_instance['orderby'] ) ) ? strip_tags( $new_instance['orderby'] ) : '';
		$instance['order'] = ( ! empty( $new_instance['order'] ) ) ? strip_tags( $new_instance['order'] ) : '';
		return $instance;
		
	}
} // Class mw_quote_widget ends here

// Register and load the widget
function wpb_load_widget() {
	register_widget( 'mw_quote_widget' );
}
add_action( 'widgets_init', 'wpb_load_widget' );

// Add fields via Advanced Custom Fields
if(function_exists("register_field_group"))
{
	register_field_group(array (
		'id' => 'acf_quote-information',
		'title' => 'Quote Information',
		'fields' => array (
			array (
				'key' => 'field_56e363c384639',
				'label' => 'Author',
				'name' => 'mw_quote_author',
				'type' => 'text',
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
			),
			array (
				'key' => 'field_56e363d48463a',
				'label' => 'Source',
				'name' => 'mw_quote_source',
				'type' => 'text',
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'quote',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'default',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 0,
	));
}

// Creates a dropdown menu with proper item selected when fed the taxonomy name and value to be selected
function mwqs_create_tax_dropdown($taxonomy, $value) {
	// Create the "taxonomy" dropdown
	$terms = get_terms( $taxonomy, array(
		'hide_empty' => 0,
		'orderby' => 'name',
	) );
	
	$html = '';
	
	if (count($terms) > 0) {
		foreach ($terms as $term) {
			$html .= sprintf(
				'<option value="%s" class="" %s >%s</option>',
				$term->term_id,
				selected( $term->term_id, $value ),
				$term->name
			);
		}
	}
	
	return $html;
}
