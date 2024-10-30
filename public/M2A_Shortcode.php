<?php

class M2A_Shortcode extends M2A_Abstruct{

	public function register_shortcode($args){
		$this->_handle_args($args);
		return $this->render_output();
	}

	public function render_output(){
		$options = $this->options();

		if($options['allow_visitor']=='user' && !is_user_logged_in())
			return;
		if($options['allow_visitor']=='visitor' && is_user_logged_in())
			return;
		ob_start();
		if($options['layout']=='messagebox'){
			require plugin_dir_path(__FILE__).'partials/message-box.php';
		}
		elseif($options['layout']=='popup'){
			add_thickbox();
			require plugin_dir_path(__FILE__).'partials/popup.php';
		}
		return ob_get_clean();
	}

	private function _handle_args($args){
		$this->reset_options();
		$label           = $this->options('labels');
		$default_options = [
			'title'        => $label['title'],
			'button_label' => $label['button_label'],
			'layout'       => $this->options('layout'),
			'style'        => $this->options('style'),
		];
		$args            = shortcode_atts($default_options, $args);
		//Confirming Style typos
		if( !in_array($args['style'], $this->get_all_available_options('layout')))
			$args['style'] = $this->options('layout');

		$this->override_option('layout', $args['layout']);
		$this->override_option('style', $args['style']);
		$this->override_option('labels', [
			'title'           => $args['title'],
			'button_label'    => $args['button_label'],
			'success_message' => $label['success_message'],
			'popup_button'=>$label['popup_button'],
		]);
	}

	public function all_massage2author(){
		$user_id = get_current_user_id();
		if($user_id){
			$args = array(
				'post_type' => 'm2a_messages',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'author' => $user_id
			);
			$loop = new WP_Query( $args );
			$cl7 = '<table>
				<thead>
					<tr>
						<th>Subject</th>
						<th>Message</th>
						<th>Post</th>
					</tr>
				</thead>
				<tbody>';
				while ( $loop->have_posts() ) : $loop->the_post(); 
				if ( get_post_meta( $loop->post->ID, 'message', true ) ){
					$cl = get_post_meta( $loop->post->ID, 'message', true );
				}else{
					$cl = get_the_content();
				}
				$cl7.= '<tr>
					<td>'.get_the_title().'</td>
					<td>'.$cl.'</td>
					<td><a href="'.get_the_permalink( wp_get_post_parent_id( $loop->post->ID ) ).'">'.get_the_title( wp_get_post_parent_id( $loop->post->ID ) ).'</a></td>
				</tr>';  
				endwhile;
			$cl7.= '</tbody>
			</table>';
			wp_reset_postdata(); 
			return $cl7;
		}
	}
}