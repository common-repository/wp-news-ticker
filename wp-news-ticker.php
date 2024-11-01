<?php
/*
Plugin Name: WP News Ticker
Description: A jQuery based news ticker widget.
Version: 0.1
Author: Hassan Derakhshandeh

		* 	Copyright (C) 2011  Hassan Derakhshandeh
		*	http://tween.ir/
		*	hassan.derakhshandeh@gmail.com

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
		Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define( WPNT_VERSION, '0.1' );

class WP_News_Ticker extends WP_Widget {

	private $textdomain;

	function WP_News_Ticker() {
		$widget_ops = array('description' => __( 'WP News Ticker brings a lightweight and easy to use news ticker to WordPress.', $this->textdomain ) );
		$this->WP_Widget( "wp-news-ticker", __( 'WP News Ticker', $this->textdomain ), $widget_ops, null );
		add_action( 'template_redirect', array( &$this, 'queue' ) );
	}

	/**
	 * Queue script and styles required
	 *
	 * @link http://www.jquerynewsticker.com/
	 * @since 0.1
	 */
	function queue() {
		wp_enqueue_script( 'wp-news-ticker', plugins_url( 'js/jquery.ticker-min.js', __FILE__ ), array( 'jquery' ), WPNT_VERSION );
		wp_enqueue_style( 'wp-news-ticker', plugins_url( 'css/ticker-style.css', __FILE__ ), array(), WPNT_VERSION );
	}

	function widget( $args, $instance ) {
		echo $args['before_widget'];
		if( $instance['title'] ) {
			$title = apply_filters( 'widget_title', $instance['title'] );
			echo $args['before_title'] . $title . $args['after_title'];
		}
		$query = new WP_Query( "cat={$instance[category]}&posts_per_page={$instance[number]}" );
		if( $query->have_posts() ) : ?>
		<div id="ticker-wrapper" class="no-js">
			<ul id="js-news" class="js-hidden">
				<?php while( $query->have_posts() ) : $query->the_post(); ?>
					<li class="news-item"><a href="<?php the_permalink() ?>"><?php the_title() ?></a></li>
				<?php endwhile; ?>
			</ul>
		</div>
		<script>
			jQuery('#js-news').ticker({
				direction: "<?php echo ( is_rtl() ) ? 'rtl' : 'ltr' ?>",
				controls: <?php echo ( $instance['controls'] == 1 ) ? 'true' : 'false' ?>,
				displayType: "<?php echo $instance['displayType'] ?>",
				titleText: "<?php echo $instance['titleText'] ?>"
			});
		</script>
		<?php endif;
		wp_reset_postdata();
		echo $args['after_widget'];
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = stripslashes( $new_instance['title'] );
		$instance['number'] = stripslashes( $new_instance['number'] );
		$instance['category'] = stripslashes( $new_instance['category'] );
		$instance['titleText'] = stripslashes( $new_instance['titleText'] );
		$instance['displayType'] = stripslashes( $new_instance['displayType'] );
		$instance['controls'] = stripslashes( $new_instance['controls'] );

		return $instance;
	}

	function form( $instance ) {
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$categories = get_categories('hide_empty=0');
		$titleText = isset( $instance['titleText'] ) ? $instance['titleText'] : __( 'Latest', $this->textdomain );
?>
		<p>
			<label><?php _e('Title') ?></label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id('title') ?>" name="<?php echo $this->get_field_name('title') ?>" value="<?php echo $instance['title'] ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show:'); ?></label>
			<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Category'); ?></label>
			<select name="<?php echo $this->get_field_name('category'); ?>" id="<?php echo $this->get_field_id('category'); ?>" class="widefat">
			<?php foreach( $categories as $category ) : ?>
				<option value="<?php echo $category->cat_ID ?>" <?php echo selected( $category->cat_ID, $instance['category'] ) ?>><?php echo $category->cat_name ?></option>
			<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label><?php _e('Title Text') ?></label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id('titleText') ?>" name="<?php echo $this->get_field_name('titleText') ?>" value="<?php echo $titleText ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('displayType'); ?>"><?php _e( 'Effect', $this->textdomain ); ?></label>
			<select name="<?php echo $this->get_field_name('displayType'); ?>" id="<?php echo $this->get_field_id('displayType'); ?>" class="widefat">
				<option value="reveal" <?php echo selected( 'reveal', $instance['displayType'] ) ?>>Reveal</option>
				<option value="fade" <?php echo selected( 'fade', $instance['displayType'] ) ?>>Fade</option>
			</select>
		</p>
		<p>
			<label><input type="checkbox" name="<?php echo $this->get_field_name('controls'); ?>" value="1" <?php echo checked( 1, $instance['controls'] ) ?> /> <?php _e( 'Show controls?', $this->textdomain ) ?></label>
		</p>
	<?php }
}

function register_wp_news_ticker_widget() {
	register_widget( 'WP_News_Ticker' );
}
add_action( 'widgets_init', 'register_wp_news_ticker_widget' );