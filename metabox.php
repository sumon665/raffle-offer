<?php
/**
 * Include and setup custom metaboxes and fields. (make sure you copy this file to outside the CMB2 directory)
 *
 * Be sure to replace all instances of 'yourprefix_' with your project's prefix.
 * http://nacin.com/2010/05/11/in-wordpress-prefix-everything/
 *
 * @category YourThemeOrPlugin
 * @package  Demo_CMB2
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     https://github.com/CMB2/CMB2
 */

/**
 * Get the bootstrap! If using the plugin from wordpress.org, REMOVE THIS!
 */

if ( file_exists( dirname( __FILE__ ) . '/cmb2/init.php' ) ) {
	require_once dirname( __FILE__ ) . '/cmb2/init.php';
} elseif ( file_exists( dirname( __FILE__ ) . '/CMB2/init.php' ) ) {
	require_once dirname( __FILE__ ) . '/CMB2/init.php';
}

/**
 * Conditionally displays a metabox when used as a callback in the 'show_on_cb' cmb2_box parameter
 *
 * @param  CMB2 $cmb CMB2 object.
 *
 * @return bool      True if metabox should show
 */
function rafoff_show_if_front_page( $cmb ) {
	// Don't show this metabox if it's not the front page template.
	if ( get_option( 'page_on_front' ) !== $cmb->object_id ) {
		return false;
	}
	return true;
}

/**
 * Conditionally displays a field when used as a callback in the 'show_on_cb' field parameter
 *
 * @param  CMB2_Field $field Field object.
 *
 * @return bool              True if metabox should show
 */
function rafoff_hide_if_no_cats( $field ) {
	// Don't show this field if not in the cats category.
	if ( ! has_tag( 'cats', $field->object_id ) ) {
		return false;
	}
	return true;
}

/**
 * Manually render a field.
 *
 * @param  array      $field_args Array of field arguments.
 * @param  CMB2_Field $field      The field object.
 */
function rafoff_render_row_cb( $field_args, $field ) {
	$classes     = $field->row_classes();
	$id          = $field->args( 'id' );
	$label       = $field->args( 'name' );
	$name        = $field->args( '_name' );
	$value       = $field->escaped_value();
	$description = $field->args( 'description' );
	?>
	<div class="custom-field-row <?php echo esc_attr( $classes ); ?>">
		<p><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label></p>
		<p><input id="<?php echo esc_attr( $id ); ?>" type="text" name="<?php echo esc_attr( $name ); ?>" value="<?php echo $value; ?>"/></p>
		<p class="description"><?php echo esc_html( $description ); ?></p>
	</div>
	<?php
}

/**
 * Manually render a field column display.
 *
 * @param  array      $field_args Array of field arguments.
 * @param  CMB2_Field $field      The field object.
 */
function rafoff_display_text_small_column( $field_args, $field ) {
	?>
	<div class="custom-column-display <?php echo esc_attr( $field->row_classes() ); ?>">
		<p><?php echo $field->escaped_value(); ?></p>
		<p class="description"><?php echo esc_html( $field->args( 'description' ) ); ?></p>
	</div>
	<?php
}


/* Product page */
add_action( 'cmb2_admin_init', 'rafoff_product_metabox' );
function rafoff_product_metabox() {
	$rafoff = new_cmb2_box( array(
		'id'           => 'rafoff_product_edit',
		'title'        => esc_html__( 'Raffle offer setting', 'cmb2' ),
		'object_types' => array( 'product' ), // Post type
		'show_names'   => true, // Show field names on the left
	) );

	$rafoff->add_field( array(
		'name'    => 'Max offer',
		'id'      => 'raf_pro_max',
		'type'    => 'text',
	) );

	$rafoff->add_field( array(
		'name'    => 'Min offer',
		'id'      => 'raf_pro_min',
		'type'    => 'text',
	) );

	$rafoff->add_field( array(
		'name'    => 'Winner Number',
		'id'      => 'raf_pro_win',
		'type'    => 'text',
	) );
			
	$rafoff->add_field( array(
		'name'    => 'User ids',
		'id'      => 'raf_pro_uid',
		'type'    => 'text',
	) );
}


/* Users profile page */
add_action( 'cmb2_admin_init', 'rafoff_user_metabox' );
function rafoff_user_metabox() {
	$rafoff = new_cmb2_box( array(
		'id'               => 'rafoff_user_edit',
		'title'            => esc_html__( 'Raffle offer setting', 'cmb2' ), // Doesn't output for user boxes
		'object_types'     => array( 'user' ), // Tells CMB2 to use user_meta vs post_meta
		'show_names'       => true,
		'new_user_section' => 'add-new-user', // where form will show on new user page. 'add-existing-user' is only other valid option.
	) );

	$rafoff->add_field( array(
		'name'    => 'Product id',
		'id'      => 'raf_user_pid',
		'type'    => 'text',
	) );

	$rafoff->add_field( array(
		'name'    => 'Discount',
		'id'      => 'raf_user_dis',
		'type'    => 'text',
	) );	

	$rafoff->add_field( array(
		'name'    => 'Cart product',
		'id'      => 'raf_user_cart',
		'type'    => 'checkbox',
	) );	
}