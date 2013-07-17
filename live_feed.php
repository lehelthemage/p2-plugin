<?php
/*
 * Plugin Name: Live Feed
 * Author: Lehel Kovach
 * Company: Velomedia
 * Date: 7/20/2013
 * Description: wordpress plugin enabling a page to list a live stream feed of 
 * wordpress post publications
 * 
 */
 //include 'shortcodes.php';
 
 
//create a shortode for the live feed
 add_shortcode( 'live_feed', 'get_live_feed_content' );
 add_action('wp_ajax_get_new_posts', 'get_new_posts_callback');
 add_action( 'admin_footer', 'load_javascript' );
 


//gets posts and their types from wordpress
function get_all_posts() {
	global $post;
	
	
	$post_types = get_post_types(
		array( 'public' => true)
	);
	
	$output = '<div class="feed">';

	$args = array( 'posts_per_page' => 10, 'order'=> 'ASC', 'orderby' => 'title' );
	$postslist = get_posts( $args );
	foreach ($postslist as $post) :  setup_postdata($post); 
		$output = $output . 
		'<div class="feed_item">' .
			'<div class="feed_author_img">' . get_avatar( $post->ID, 32 )  . '</div>'.
			'<div class="comment_headline">' . 
				'<div class="comment_author">' . get_the_author_meta( 'user_nicename', $post->post_author) . '</div>' .
				'<div class="comment_date">' . $post->post_date  . '</div>' .
			'</div>' .
			'<div class="comment_content">' .
				$post->post_content . 
			'</div>' .
		'</div>';
				
		//get comments
		$args = array( 'post_id' => $post->ID );
		$comments = get_comments( $args );
		foreach( $comments as $comment ) {
			$output = $output . '<div class="feed_comment">' .
			//'<div class="feed_author_img">' . get_avatar( $comment->user_id, 32 )  . '</div><div class="comment_headline">' . 
			//	'<div class="comment_author">' . $comment->comment_author . '</div>' .
			//	'<div class="comment_date">' . $post->comment_date  . '</div>' .
				$comment->comment_content . '</div>';
				
		}
	endforeach;
	
	$output = $output . '</div>';
	
	return $output;
}

function load_javascript() {
	wp_deregister_script( 'jquery' ); 
    wp_register_script( 'jquery', 'http://code.jquery.com/jquery-latest.pack.js', false, '' ); 
	wp_deregister_script( 'cookies' ); 
    wp_register_script( 'cookies', 'cookies.js', false, '' ); 
	
	?>
	<script type="text/javascript">
	setCookie('lastUpdateTime', new Date(), 0);
	
	jQuery(document).ready(function($) {
		alert('hi');
		var pollingTimer = setInterval(function(){pollingTimerCallback()},3000);
	
	});


	function pollingTimerCallback()
	{
		//check for new updates by calling a wordpress function via ajax
		//where you pass in lastUpdateTime
		
		var data = {
			action: 'get_new_posts',
			last_update: getCookie('lastUpdateTime')
		};
	
		// We can also pass the url value separately from ajaxurl for front end AJAX implementations
		jQuery.post(ajax_object.ajax_url, data, function(response) {
			alert('Got this from the server: ' + response);
		});
		
	}
	</script>
	<?php
	
}



function filter_where( $where = '' ) {
	global $last_update;
	
    // posts in the last 30 days
    $where .= " AND post_date > '" . $last_update . "'";
    return $where;
}	

function get_new_posts_callback() {
	global $wpdb; // this is how you get access to the database

	global $last_update;
	$last_update = intval( $_POST['last_update'] ); 	


	add_filter( 'posts_where', 'filter_where' );
	$args = array( 'posts_per_page' => 10, 'order'=> 'ASC', 'orderby' => 'title' );
	$postslist = get_posts( $args );
	foreach ($postslist as $post) :  setup_postdata($post);
		array_push( $output_list, array( 'post_id' => $post->ID, 'post_date' => $post->post_date ) );
	endforeach;
	remove_filter( 'posts_where', 'filter_where' );
	
	$output = json_encode( $output_list );
	
	if (is_array($output)){ 
		print_r($output);
	}
	else {
		echo $output;
	}
	die;
}

function get_post_box() {
	
	//create jquery to handle pressing enter key to post ajax...
	$output = 
	'<div class="live_post_box">' . '<div>Post to the live feed!</div>' .
		'<textarea class="live_feed_textarea" id="post_box" name="post_box"></textarea>' .
	'</div><br/>';
	
	return $output;
}

function get_live_feed_content() {
	$output = get_post_box();
	$output .= get_all_posts();
	return $output;
}

add_action( 'wp_enqueue_scripts', 'prefix_add_my_stylesheet' );

/**
 * Enqueue plugin style-file
 */
function prefix_add_my_stylesheet() {
    // Respects SSL, Style.css is relative to the current file
    wp_register_style( 'prefix-style', plugins_url('css/live_feed.css', __FILE__) );
    wp_enqueue_style( 'prefix-style' );
    
}

//insert javascript on the page (via ajax?) 
//javascript will contain a timer to poll.
//we will make another php file that will check the database and handle the transactions
//then send data back as json

//the refresh function will look at date-times of posts and save last refresh date time
//that way we can just query newer info from a certain last time we refereshed

//we also need to check any edits that came in...not just new posts and comments


//need to create a text field that you can click on which becomes editable
//hitting enter will cause the data via ajax to be sent to the server (to a wordpress function to update db)





?>
