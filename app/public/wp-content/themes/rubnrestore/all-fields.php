<?php
//The template to go with Gravity Forms All Fields plugin
//https://gravitywiz.com/gravity-forms-all-fields-template/
// Most email clients do not support <style> blocks. We'll define our styles here and output them inline in the markup below.
$styles = array(
	'ul'         => 'border-top: 1px solid #eee;margin:0;padding:0;',
	'li'         => 'border-bottom: 1px solid #eee; padding: 10px 10px 15px; margin: 0; list-style-type: none; overflow: hidden; white-space:normal;',
	'span'       => 'vertical-align: top; display: block; margin-left:30%;',
	'span.label' => 'float: left; vertical-align: top; font-weight: bold; width: 30%;'
);

// Make a back-up of the styles we've just defined. This allows us to make temporary changes to the styles below and then
// reset the styles for the next item.
$reset_styles = $styles;
?>

<ul class="gf-all-fields" style="<?php echo $styles['ul']; ?>">
	<?php foreach( $items as $item ):

		// Get field object for use in template.
		$field = isset( $item['field'] ) ? $item['field'] : new GF_Field();

		// Don't show pricing fields (just like GF default {all_fields}).
		if( GFCommon::is_pricing_field( $field ) ) {
			continue;
		}

		// Change the style a bit for Section fields.
		if( $field->get_input_type() == 'section' ) {
			$styles['li'] .= 'background-color:#f7f7f7; padding-bottom: 10px;';
		}

		// Add the field type as a CSS class for every field to make styling specific elements easier.
		$css_class = isset( $field ) ? 'field-type-' . $field->type : '';

		?>

		<li class="<?php echo $css_class; ?>" style="<?php echo $styles['li']; ?>">
			<span style="<?php echo $styles['span.label']; ?>"><?php echo $item['label']; ?></span>
			<span style="<?php echo $styles['span']; ?>"><?php echo $item['value']; ?></span>
		</li>

		<?php
		// Reset any temporary changes we made to our core styles.
		$styles = $reset_styles;
	endforeach; ?>
</ul>