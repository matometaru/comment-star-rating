<div class="section">
	<h2><?php echo esc_attr( CSR_Config::NAME ); ?> &raquo; <?php _e( 'Settings' ); ?></h2>
	<form id="<?php esc_attr_e( CSR_Config::DOMAIN ); ?>" method="post" action="">
		<?php wp_nonce_field( 'csr-nonce-key', 'csr-key' ); ?>
		<h3><?php esc_html_e( '有効にする投稿タイプを選択してください', 'csr-form' ); ?></h3>
		<?php foreach ( $post_types as $post_type ) : ?>
			<p>
				<label>
					<input
						type="checkbox"
						name="<?php echo esc_attr_e( CSR_Config::DOMAIN, 'csr-form' ); ?>[<?php esc_attr_e( $post_type ); ?>]"
						value="1"
						<?php if ( CSR_Option::find()->is_enabled_post_type( $post_type ) ) echo 'checked'; ?>
					/>
					<?php esc_attr_e( $post_type ); esc_html_e( 'ページ上で有効にします', 'csr-form' ); ?>
				</label>
			</p>
		<?php endforeach; ?>
		<h3><?php esc_html_e( 'コメントの入力から外したい要素を選択', 'csr-form' ); ?></h3>
		<p>
			<label>
				<input
					type="checkbox"
					name="<?php esc_attr_e( CSR_Config::DOMAIN, 'csr-form' ); ?>[url]"
					value="1"
					<?php if ( $csr_options->is_disabled_form_url() ) {
						echo 'checked';
					} ?>
				/>
				<?php esc_html_e( 'URLを外す', 'csr-form' ); ?>
			</label>
		</p>
		<p>
			<label>
				<input
					type="checkbox"
					name="<?php echo esc_attr( CSR_Config::DOMAIN ); ?>[email]"
					value="1"
					<?php if ( $csr_options->is_disabled_form_email() ) {
						echo 'checked';
					} ?>
				/>
				<?php esc_html_e( 'メールアドレスを外す', 'csr-form' ); ?>
			</label>
		</p>
		<p class="submit">
			<input class="button-primary" type="submit" name='save' value='<?php _e( 'Save Changes' ); ?>'/>
		</p>
	</form>
</div>
<div>
	<h2>プラグイン作成者に非常食をプレゼント</h2>
	<a class="button-primary" href="http://amzn.asia/atweOEp">プレゼントする</a>
</div>