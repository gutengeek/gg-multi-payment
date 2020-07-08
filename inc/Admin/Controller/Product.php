<?php
namespace GGMP\Admin\Controller;

use GGMP\Core\Controller;

class Product extends Controller {
	/**
	 * Register Hook Callback functions is called.
	 */
	public function register_hook_callbacks() {
		add_filter( 'display_post_states', [ $this, 'display_post_states' ], 10, 2 );
	}

	/**
	 * Display post states
	 */
	public function display_post_states( $states, $post ) {
		if ( 'product' == get_post_type( $post->ID ) ) {
			if ( $ids = get_post_meta( $post->ID, 'ggmp_ids', true ) ) {
				$ids = ggmp_sanitize_ids( $ids );

				if ( ! empty( $ids ) ) {
					$count    = count( explode( ',', $ids ) );
					$states[] = apply_filters( 'ggmp_post_states', '<span class="ggmp-state">' . sprintf( esc_html__( 'Associate (%s)', 'ggmp' ), $count ) . '</span>', $count,
						$post->ID );
				}
			}
		}

		return $states;
	}
}
