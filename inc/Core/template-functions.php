<?php
/**
 * @param       $template
 * @param array $args
 * @return string
 */
function ggmp_get_template( $template, $args = [] ) {
	return GGMP\Core\View::render_template( $template, $args );
}

/**
 * Render template.
 */
function ggmp_render_template( $template, $args = [] ) {
	echo ggmp_get_template( $template, $args );
}
