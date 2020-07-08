<?php
	$active_tab = ! empty( $_GET['ggmp_tab'] ) ? ggmp_clean( $_GET['ggmp_tab'] ) : 'form_field_options';
	wp_nonce_field( 'ggmp_save_form_meta', 'ggmp_meta_nonce' );
	?>
    <input id="ggmp_active_tab" type="hidden" name="ggmp_active_tab">
    <div class="js-ggmp-metabox-wrap ggmp-metabox-panel-wrap">
        <ul class="ggmp-form-data-tabs ggmp-metabox-tabs">
			<?php foreach ( $form_data_tabs as $index => $form_data_tab ) : ?>
				<?php
				// Determine if current tab is active.
				$is_active = $active_tab === $form_data_tab['id'] ? true : false;
				?>
                <li class="<?php echo "{$form_data_tab['id']}_tab" . ( $is_active ? ' active' : '' ) . ( $this->has_sub_tab( $form_data_tab ) ? ' has-sub-fields' : '' ); ?>"
                    data-tab="<?php echo $form_data_tab['id']; ?>">
                    <a href="#<?php echo $form_data_tab['id']; ?>"
                       data-tab-id="<?php echo $form_data_tab['id']; ?>">
						<?php if ( ! empty( $form_data_tab['icon-html'] ) ) : ?>
							<?php echo $form_data_tab['icon-html']; ?>
						<?php else : ?>
                            <span class="ggmp-icon ggmp-icon-default"></span>
						<?php endif; ?>
                        <span class="ggmp-label"><?php echo $form_data_tab['label']; ?></span>
                    </a>
					<?php if ( $this->has_sub_tab( $form_data_tab ) ) : ?>
                        <ul class="ggmp-metabox-sub-tabs ggmp-hidden">
							<?php foreach ( $form_data_tab['sub-fields'] as $sub_tab ) : ?>
                                <li class="<?php echo "{$sub_tab['id']}_tab"; ?>">
                                    <a href="#<?php echo $sub_tab['id']; ?>"
                                       data-tab-id="<?php echo $sub_tab['id']; ?>">
										<?php if ( ! empty( $sub_tab['icon-html'] ) ) : ?>
											<?php echo $sub_tab['icon-html']; ?>
										<?php else : ?>
                                            <span class="ggmp-icon ggmp-icon-default"></span>
										<?php endif; ?>
                                        <span class="ggmp-label"><?php echo $sub_tab['label']; ?></span>
                                    </a>
                                </li>
							<?php endforeach; ?>
                        </ul>
					<?php endif; ?>
                </li>
			<?php endforeach; ?>
        </ul>

		<?php foreach ( $this->settings as $setting ) : ?>
			<?php do_action( "ggmp_before_{$setting['id']}_settings" ); ?>
			<?php
			// Determine if current panel is active.
			$is_active = $active_tab === $setting['id'] ? true : false;
			?>
            <div id="<?php echo $setting['id']; ?>"
                 class="panel ggmp_options_panel<?php echo( $is_active ? ' active' : '' ); ?> ggmp-panel">
				<?php if ( ! empty( $setting['fields'] ) ) : ?>
					<?php foreach ( $setting['fields'] as $field ) : ?>
						<?php $this->render_field( $field ); ?>
					<?php endforeach; ?>
				<?php endif; ?>
            </div>
			<?php do_action( "ggmp_after_{$setting['id']}_settings" ); ?>

			<?php if ( $this->has_sub_tab( $setting ) ) : ?>
				<?php if ( ! empty( $setting['sub-fields'] ) ) : ?>
					<?php foreach ( $setting['sub-fields'] as $index => $sub_fields ) : ?>
                        <div id="<?php echo $sub_fields['id']; ?>" class="panel ggmp_options_panel ggmp-hidden">
							<?php if ( ! empty( $sub_fields['fields'] ) ) : ?>
								<?php foreach ( $sub_fields['fields'] as $sub_field ) : ?>
									<?php $this->render_field( $sub_field ); ?>
								<?php endforeach; ?>
							<?php endif; ?>
                        </div>
					<?php endforeach; ?>
				<?php endif; ?>
			<?php endif; ?>
		<?php endforeach; ?>
    </div>
	<?php
?>
