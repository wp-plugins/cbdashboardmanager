<?php

/**
 * Start Class for all custom widgets
 */

class cbdashboardmanager_customwidgets {

    public $recentcomments, $recentpost;

    function __construct() {


        $this->pluginbasename   = plugin_basename(__FILE__);
        $checkwidgets           = get_option('widgetsadded') ? get_option('widgetsadded') : array();
        $customcheckwidgets     = get_option('customwidgetsadded') ? get_option('customwidgetsadded') : array();
        $custom_post_type       = "dashboardposttype";

        if (in_array($custom_post_type, $customcheckwidgets)) {

             add_action('init', array($this, 'cbdashboardmanager_post_type'));
        }//adding custom post type
        
        add_action('add_meta_boxes', array($this,'cbdashboardmanager_add_custom_meta_box'), 1);          //adding metabox with custom post type
        add_action('save_post', array($this,'cbdashboardmanager_save_widget_metadata'),1,2);               //updated meta-box value while saving post
        add_action('wp_dashboard_setup', array($this, 'cbdashboardmanager_dashboard_widget'), 100);        //function runs when dashboard appears
        
    }


   /**
     * Method to add meta box 
     * @global 
     * @param <type> no 
     */

    function cbdashboardmanager_add_custom_meta_box(){

         add_meta_box('cb_widget_meta_box', 'Widget Options', array($this , 'cbdashboardmanager_custom_dashboard_widget_metabox'), 'dashboardwidgets', 'normal', 'high');

    }  //end of cb_dashboard_meta_box


    /**
     * Method to show Custom fields for Dashboard widget type post
     * @global object $post
     * @param <type> $post
     */
    
    function cbdashboardmanager_custom_dashboard_widget_metabox($post){

        global $post;

        $cb_widget_position = get_post_meta($post->ID, 'cb_widget_normal_side', true);
        $cb_widget_topdown  = get_post_meta($post->ID, 'cb_widget_top_down', true);
       
        wp_nonce_field('cb_submit_option', 'cb_option_check');
        
       ?>
       <div id = "content" style = "width:420px;height: 150px;">
           <p><?php _e('Set Options For DashBoard Widgets','cbdashboardmanager') ?></p>

            <div id="leftcolumn" style="width:200px; height: 100px; display:inline-block;  float:left;">
              <?php
                 echo __('Horizontal Position:','cbdashboardmanager').'<br><br>';
                 echo  '<select name="cb_widget_normal_side">
                            <option '.(($cb_widget_position == 'normal')? 'selected="selected"':'').' value="normal" > Left</option>
                            <option '.(($cb_widget_position == 'side')? 'selected="selected"':'').'   value="side" >   Right   </option>
                      </select>';

              ?>

            </div>

            <div id="rightcolumn" style="width:200px; height:100px; display:inline-block;  float:right;">
                <?php
                echo __('Vertical Position:','cbdashboardmanager').'<br><br>';
                echo    ' <select name="cb_widget_top_down" value="' . esc_attr($cb_widget_topdown) . '">
                                <option '.(($cb_widget_topdown == 'high')? 'selected="selected"':'').' value="high" > Top    </option>
                                <option '.(($cb_widget_topdown == 'core')? 'selected="selected"':'').' value="core" > Down </option>
                           </select> ';
                ?>
            </div>
         </div>

    <?php
     
    } // end of method custom_dashboard_widget_metabox

    /**
     *Function to save meta data 
     * @param object $post
     * @param <type> $post_id,$post
     * @return <type>
     */

    function cbdashboardmanager_save_widget_metadata($post_id, $post){

        // Verify if this is an auto save routine.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        //Check permissions
        if (!current_user_can('publish_posts')) { // Check for capabilities, not role
            wp_die('Insufficient Privileges: Sorry, you do not have the capabilities access to this page. Please go back.');
        }
        // Verify this came from the our screen and with proper authorization
        if (!isset($_POST['cb_option_check']) || !wp_verify_nonce($_POST['cb_option_check'], 'cb_submit_option')) {
            return;
        }
        //update meta data
        if (isset($_POST['cb_widget_normal_side']) && isset($_POST['cb_widget_top_down'])) {
            // Save meta data
            update_post_meta($post_id, 'cb_widget_normal_side', esc_attr($_POST['cb_widget_normal_side']));
            update_post_meta($post_id, 'cb_widget_top_down', esc_attr($_POST['cb_widget_top_down']));

        }
      
    }//end of function save_widget_metadata

    /**
     * Add all extra dashboard widget when dashboard appears
     * 
     * since v1.x 
     * 
     * @global <type> $wp_meta_boxes 
     * @global object $post
     * @param object $post 
     */
    //

    function cbdashboardmanager_dashboard_widget($post) {

        global $wp_meta_boxes,$post;
        $checkwidgets = get_option('widgetsadded') ?  get_option('widgetsadded') :array();
         foreach ($checkwidgets as $widgettoadd ){
                                 //echo $widgettoadd;
                             }
        $last_modified  = "lastmodified";
        $recent_post    = "recentpost";
        $recent_comment = "recentcomments";
        
        $args = array('post_type' => 'dashboardwidgets', 'posts_per_page' => -1);
        $loop = new WP_Query($args);

        while ($loop->have_posts()) : $loop->the_post();                    //get all post of custom type named dashboardwidgets

            $widget_id      = array('title'     => get_the_title(),'id' =>$post->ID);        //get the title as string
            $widget_content = array('content'   => get_the_content());      //get the content as string

            $normal_side    = get_post_meta($post->ID, "cb_widget_normal_side", true);
            $top_down       = get_post_meta($post->ID, "cb_widget_top_down", true);
            
            if($normal_side==''){
                $normal_side='normal';      //default value
            }
            if($top_down==''){
                $top_down='high';           //default value
            }

            $customcheckwidgets = get_option('customwidgetsadded')?get_option('customwidgetsadded') : array();
            $custom_post_type   = "dashboardposttype";

            if (in_array($custom_post_type, $customcheckwidgets)) {

                 add_meta_box($widget_id['id'], $widget_id['title'], array($this, 'dashboard_notice'), 'dashboard', $normal_side, $top_down, $widget_content);
            }
           // array_push($custom_widgets_array,$widget_id['id']);

        endwhile;
        wp_reset_query();

        //end the work of adding dynamic widget from custom post type

        if ( !$title_options_comment = get_option('comment_dashboard_widget_options') )  //for adding post type name with title
            
                $title_options_comment = array();
                $title_post_comment    = isset($title_options_comment['comment']) ? $title_options_comment['comment'] : '';
                $obj                   = get_post_type_object($title_post_comment);

        //add recent comment widget
         $checkwidgets = is_array($checkwidgets)? $checkwidgets : array();

         if (in_array($recent_comment, $checkwidgets)) {
             $cbdashboardmanagertitle = is_object($obj) ? 'of post type ' .$obj->labels->singular_name : '';
             wp_add_dashboard_widget(

                    md5('recent_comments'),
                    ' Recent Comments Reloaded '.$cbdashboardmanagertitle.'',
                    array($this, 'comment_dashboard_widget'),
                    array($this, 'comment_dashboard_widget_handler')
            );


         }

        if ( !$title_options_post = get_option('post_dashboard_widget_options') )   //for adding post type name with title

                $title_options_post = array();
                $title_options_post = isset($title_options_post['post'])? $title_options_post['post'] : '';
                $obj=get_post_type_object($title_options_post);

        //add recent post widget

       if (in_array($recent_post, $checkwidgets)) {
           $cbdashboardmanagertitle = is_object($obj) ? 'of post type ' .$obj->labels->singular_name : '';
             wp_add_dashboard_widget(

                     md5('recent_posts'),
                    'Recent Posts '.$cbdashboardmanagertitle.'',
                     array($this, 'custom_dashboard_widget_post'),
                     array($this, 'custom_dashboard_widget_post_handle')
            );

       }
         
        if ( !$title_mod_post = get_option('post_dashboard_widget_modified') )
            //for adding post type name with title

                $title_mod_post = array();
                $title_mod_post = isset($title_mod_post['post'])? $title_mod_post['post'] : '';
                $obj=get_post_type_object($title_mod_post);

        //adding last modified post widget
        if (in_array($last_modified, $checkwidgets)) {
            $cbdashboardmanagertitle = is_object($obj) ? 'of post type ' .$obj->labels->singular_name : '';

            wp_add_dashboard_widget(

                   md5('modified_posts'),
                  'Last Modified Posts '.$cbdashboardmanagertitle.'',
                   array($this, 'dashboard_widget_post'),
                   array($this, 'dashboard_widget_post_handler')
            );

        }
    
    }//end of function dashboard_widget


    function dashboard_notice($post, $widget_content) { //to show content in dashboardwidgets type post widget

        echo $widget_content['args']['content'];
    }

 /**
  * register custom post type
  */

    function cbdashboardmanager_post_type() {

        $label = array(
            'name'          => __('Dashboard Widgets'),
            'singular_name' => __('DashBoard')
        );

        $args = array(
            'labels'            => $label,
            'public'            => true,
            'publicly_queryable' => true,
            'show_ui'           => true,
            'show_in_menu'      => true,
            'query_var'          => true,
            'rewrite'           => array('slug' => 'dashboard'),
            'has_archive'       => true,
            'hierarchical'      => false,
            'menu_position'     => null
        );
        
             register_post_type('dashboardwidgets', $args);

    } //end of cb_post_type
/**
 * recent comment widget callback control
 */

   
    function comment_dashboard_widget_handler() {

         $post_types_comments = get_post_types('', 'names');
        
        if (!$widget_options_comment_number = get_option('dashboard_widget_options_comment_number'))

            $widget_options_comment_number = array();

        if (!isset($widget_options_comment_number['dashboard_recent_comments_custom']))

            $widget_options_comment_number['dashboard_recent_comments_custom'] = array();

        if ( !$widget_options_comment = get_option( 'comment_dashboard_widget_options' ) )

            $widget_options_comment = array();

    
        if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['comment_dashboard_options']) ) {
       
            $widget_options_comment['comment'] = wp_kses($_POST['comment_dashboard_options'],array() );
            update_option( 'comment_dashboard_widget_options', $widget_options_comment );
            $number = absint( $_POST['widget-recent-comments_custom']['items'] );
            $widget_options_comment_number['dashboard_recent_comments_custom']['items'] = $number;
            update_option( 'dashboard_widget_options_comment_number', $widget_options_comment_number );
           
        }
        $number = isset( $widget_options_comment_number['dashboard_recent_comments_custom']['items'] ) ? (int) $widget_options_comment_number['dashboard_recent_comments_custom']['items'] : '';

	    echo '<p><label for="comments-number">' . __('Number of comments to show:') . '</label>';
	    echo '<input id="comments-number" name="widget-recent-comments_custom[items]" type="text" value="' . $number . '" size="3" /></p>';
    
       if(!isset($widget_options_comment['comment']))

            $widget_options_comment['comment'] = '';   //you can set the default
        ?>
        
        <p><strong>Recent Comments For Post/Page :</strong></p>

        <div class='team_class_wrap'>
            
            <select name='comment_dashboard_options' id='comment'>

                <?php foreach ($post_types_comments as $post_types_comment) {

                             $obj = get_post_type_object($post_types_comment); ?>
                             <option  value="<?php echo $post_types_comment; ?>" >  <?php echo $obj->labels->singular_name; ?> </option>

                 <?php } ?>

            </select>
        </div>
    <?php

    }//end of function comment_dashboard_widget_handler
    /**
     * function for modified post callback
     */

    function dashboard_widget_post() {

       $widgets_modified_post_no = get_option( 'dashboard_widget_modified_post_number' );

       $total_post_count = isset( $widgets_modified_post_no['dashboard_modified_post_custom'] ) && isset( $widgets_modified_post_no['dashboard_modified_post_custom']['items'] )
		? absint( $widgets_modified_post_no['dashboard_modified_post_custom']['items'] ) : 5;
     

      if ( !$widget_options_modified = get_option( 'post_dashboard_widget_modified' ) )

            $widget_options_modified = array();
      
        $select_post = isset($widget_options_modified['post'])? $widget_options_modified['post'] : '';
        $obj         = get_post_type_object($select_post);
        if(is_object($obj)){

            echo "
                <p><strong>Recent Modified Posts of Post Type : {$obj->labels->singular_name}</strong></p>

            ";
        }
        else{
            echo "  <p><strong>Recent Modified Posts </strong></p>   ";
        }

        $args = array(
            'posts_per_page'   => $total_post_count,
            'offset'           => 0,
            'category'         => '',
            'orderby'          => 'modified',
                'order'            => 'DESC',
            'include'          => '',
            'exclude'          => '',
            'meta_key'         => '',
            'meta_value'       => '',
            'post_type'        => $select_post,
            'post_mime_type'   => '',
            'post_parent'      => '',
            'post_status'      => 'publish',
            'suppress_filters' => true );
       ?>
        <ul>
        <?php

        $startc   = 0;
        $my_query = null;
        $my_query = new WP_Query($args);

       if ($my_query->have_posts()) {

            while ($my_query->have_posts()) : $my_query->the_post();

            if( get_the_modified_date() != get_the_date() ){
            $startc++;
        ?>
                <li>
                     <a href="<?php the_permalink(); ?>"target="_blank"><?php the_title(); ?></a> | <?php  echo the_modified_date();?>  | <?php if(current_user_can( 'edit_posts' )){edit_post_link('edit', '<span>', '</span>');} ?>
                </li>
        <?php
            }
           endwhile;

         }

         wp_reset_query();

         if($startc < $total_post_count){

             echo "You have only "  .$startc. " posts modified." ;
         }
        ?>

        </ul>

    <?php

     }//end fo function dashboard_widget_post


   /**
    * function for recent post widget callback
    */

   function custom_dashboard_widget_post() {
        //get saved data

       $widgets_custom_post_no = get_option( 'dashboard_widget_options_post_number' );
       $total_post = isset( $widgets_custom_post_no['dashboard_recent_post_custom'] ) && isset( $widgets_custom_post_no['dashboard_recent_post_custom']['items'] )
		? absint( $widgets_custom_post_no['dashboard_recent_post_custom']['items'] ) : 5;
       // echo $total_post;

        if ( !$widget_options = get_option( 'post_dashboard_widget_options' ) )
            $widget_options = array();
            $saved_post = isset($widget_options['post'])? $widget_options['post'] : '';
            $obj        = get_post_type_object($saved_post);
       if(is_object($obj)){
           echo "
                <p><strong>Recent Posts of Post Type : {$obj->labels->singular_name}</strong></p>

                ";
       }else{
           echo "
                <p><strong>Recent Posts </strong></p>
                ";
       }

        $args = array(
            'posts_per_page'   => $total_post,
            'offset'           => 0,
            'category'         => '',
            'orderby'          => 'post_date',
            'order'            => 'DESC',
            'include'          => '',
            'exclude'          => '',
            'meta_key'         => '',
            'meta_value'       => '',
            'post_type'        => $saved_post,
            'post_mime_type'   => '',
            'post_parent'      => '',
            'post_status'      => 'publish',
            'suppress_filters' => true );
       ?>
        <ul>
        <?php

        $startc=0;
        $my_query = null;
        $my_query = new WP_Query($args);
        
       if ($my_query->have_posts()) {

            while ($my_query->have_posts()) : $my_query->the_post();
            $startc++;
        ?>
                <li>
                   <a href="<?php the_permalink(); ?>"target="_blank"><?php the_title(); ?></a> | <?php echo  get_the_date();  ?>| <?php if(current_user_can( 'edit_posts' )){edit_post_link('edit', '<span>', '</span>');}?>

                </li>
        <?php
            endwhile;
         }
         wp_reset_query();
         if($startc < $total_post){
            echo "You have only "  .$startc. " posts." ;
         }
         
        ?>

        </ul>

    <?php

    }//end of function custom_dashboard_widget_post


    /**
     *last modified post callback control
     *
     */
    function dashboard_widget_post_handler(){

        if ( !$widget_modified_post_number = get_option( 'dashboard_widget_modified_post_number' ) )

            $widget_modified_post_number = array();

	if ( !isset($widget_modified_post_number['dashboard_modified_post_custom']) )

        $widget_modified_post_number['dashboard_modified_post_custom'] = array();

               $post_types_for_modification = get_post_types('', 'names');//get all post by name 

        if ( !$widget_options_for_modification  = get_option( 'post_dashboard_widget_modified' ) )

                $widget_options_for_modification = array();

   
        if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['post_dashboard_modified']) ) {
        
                $widget_options_for_modification['post'] = wp_kses($_POST['post_dashboard_modified'],array() );
                update_option( 'post_dashboard_widget_modified', $widget_options_for_modification );
                $number_mod = absint( $_POST['widget-modified-post_custom']['items'] );

                $widget_modified_post_number['dashboard_modified_post_custom']['items'] = $number_mod;
                update_option( 'dashboard_widget_modified_post_number', $widget_modified_post_number );
        }
        
        $number_mod = isset( $widget_modified_post_number['dashboard_modified_post_custom']['items'] ) ? (int) $widget_modified_post_number['dashboard_modified_post_custom']['items'] : '';

	    echo '<p><label for="comments-number">' . __('Number of posts to show:') . '</label>';
	    echo '<input id="comments-number" name="widget-modified-post_custom[items]" type="text" value="' . $number_mod . '" size="3" /></p>';

        //set defaults
     if(!isset($widget_options_for_modification['post']))

         $widget_options_for_modification['post'] = ''; //you can set the default
        ?>

        <p><strong><?php _e('Last Modified Posts','cbdashboardmanager')?></strong></p>
        <div class='team_class_wrap'>
            <label><?php _e('Post / Page','cbdashboardmanager')?></label>
            <select name='post_dashboard_modified' id='post'>

                <?php foreach ($post_types_for_modification as $post_type) {

                     $obj = get_post_type_object( $post_type );?>

                     <option value = "<?php echo $post_type; ?>"> <?php echo $obj->labels->singular_name; ?></option>
                 <?php } ?>

            </select>
        </div>
    <?php



}//end of function dashboard_widget_post_handler

    function custom_dashboard_widget_post_handle() {
        //get saved data
        if ( !$widget_options_post_number = get_option( 'dashboard_widget_options_post_number' ) )

            $widget_options_post_number = array();

	if ( !isset($widget_options_post_number['dashboard_recent_post_custom']) )

         $widget_options_post_number['dashboard_recent_post_custom'] = array();

         $post_types = get_post_types('', 'names');

        foreach ($post_types as $post_type){

         }

    if ( !$widget_options = get_option( 'post_dashboard_widget_options' ) )

          $widget_options = array();

    //process update
    if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['post_dashboard_options']) ) {
                //minor validation
                $widget_options['post'] = wp_kses($_POST['post_dashboard_options'],array() );
                //save update
                update_option( 'post_dashboard_widget_options', $widget_options );
                $number_post = absint( $_POST['widget-recent-post_custom']['items'] );
                $widget_options_post_number['dashboard_recent_post_custom']['items'] = $number_post;
                update_option( 'dashboard_widget_options_post_number', $widget_options_post_number );

        }

        $number_post = isset( $widget_options_post_number['dashboard_recent_post_custom']['items'] ) ? (int) $widget_options_post_number['dashboard_recent_post_custom']['items'] : '';

	echo '<p><label for="comments-number">' . __('Number of posts to show:') . '</label>';
	echo '<input id="comments-number" name="widget-recent-post_custom[items]" type="text" value="' . $number_post . '" size="3" /></p>';

    //set defaults
    if(!isset($widget_options['post']))

        $widget_options['post'] = '';            //you can set the default
        ?>

        <p><strong><?php _e('Recent Posts','cbdashboardmanager')?></strong></p>

        <div class ='team_class_wrap'>
            
            <label><?php _e('Post / Page','cbdashboardmanager')?></label>

            <select name = 'post_dashboard_options' id = 'post'>

                <?php foreach ($post_types as $post_type) {

                    $obj  = get_post_type_object( $post_type );?>

                    <option value = "<?php echo $post_type; ?>"> <?php echo $obj->labels->singular_name; ?></option>

                 <?php } ?>
            
            </select>
        </div>
    <?php

    }//end of function custom_dashboard_widget_post_handle

/**
 *
 * @global <type> $wpdb recent comment widget callback
 */
     function comment_dashboard_widget() {
                 
         if ( !$widget_options_comment = get_option('comment_dashboard_widget_options') )

                $widget_options_comment = array();
                //getting selected post name and then post type object to get post name in good format
                $saved_post_comment= isset($widget_options_comment['comment'])? $widget_options_comment['comment'] : '';
                $obj = get_post_type_object($saved_post_comment);
                $post_type_name = 'For Post /Page :{$obj->labels->singular_name}';
                if(is_object($obj)){
                    echo " <p><strong>".__('Recent Comments For Post /Page :','cbdashboardmanager')." {$obj->labels->singular_name}</strong></p> ";
                }
                else{
                    echo " <p><strong>".__('Custom Recent Comments','cbdashboardmanager')." </strong></p> ";
                }
       ?>
    <?php
    global $wpdb;

	// Select all comment types and filter out spam later for better query performance.
	$comments = array();
	$start = 0;

	$widgets_custom = get_option( 'dashboard_widget_options_comment_number' );

        // total _item is the number of comment to show
	$total_items = isset( $widgets_custom['dashboard_recent_comments_custom'] ) && isset( $widgets_custom['dashboard_recent_comments_custom']['items'] )
		? absint( $widgets_custom['dashboard_recent_comments_custom']['items'] ) : 5;

	$comments_query = array( 'number' => $total_items * 5, 'offset' => 0 ,'post_type' => $saved_post_comment);

	if ( ! current_user_can( 'edit_posts' ) )
		$comments_query['status'] = 'approve';

	while ( count( $comments ) < $total_items && $possible = get_comments( $comments_query ) ) {
		foreach ( $possible as $comment ) {
			if ( ! current_user_can( 'read_post', $comment->comment_post_ID ) )
				continue;
			$comments[] = $comment;
			if ( count( $comments ) == $total_items )
				break 2;
		}
		$comments_query['offset'] += $comments_query['number'];
		$comments_query['number'] = $total_items * 10;
	}

        //calling comment show function
	if ( $comments ) {

		echo '<div id="the-comment-list" data-wp-lists="list:comment">';
		foreach ( $comments as $comment )
			_wp_dashboard_recent_comments_row( $comment );
		echo '</div>';
                
		if ( current_user_can('edit_posts') )
			_get_list_table('WP_Comments_List_Table')->views();

		wp_comment_reply( -1, false, 'dashboard', false );
		wp_comment_trashnotice();
               

	} else {

		echo '<p>' . __( 'No comments yet.' ) . '</p>';
	}

                echo '<p> &nbsp &nbsp </p>';//this is for fixing a css error


   }//end of function recent comment callback

   /**
    * for showing recent cooment with pic and edit option
    * @param <type> $comment
    * @param <type> $show_date
    */

 function _wp_dashboard_recent_comments_row( &$comment, $show_date = true ) {

    $GLOBALS['comment'] = & $comment;
	$comment_post_url   = get_edit_post_link( $comment->comment_post_ID );
	$comment_post_title = strip_tags(get_the_title( $comment->comment_post_ID ));
	$comment_post_link  = "<a href='$comment_post_url'>$comment_post_title</a>";
	$comment_link       = '<a class="comment-link" href="' . esc_url(get_comment_link()) . '">#</a>';

	$actions_string = '';

if ( current_user_can( 'edit_comment', $comment->comment_ID ) ) {
		// preorder it: Approve | Reply | Edit | Spam | Trash
		$actions = array(
			'approve' => '',
            'unapprove' => '',
			'reply'   => '',
			'edit'    => '',
			'spam'    => '',
			'trash'   => '',
            'delete'  => ''
		);

		$del_nonce     = esc_html( '_wpnonce =' . wp_create_nonce( "delete-comment_$comment->comment_ID" ) );
		$approve_nonce = esc_html( '_wpnonce =' . wp_create_nonce( "approve-comment_$comment->comment_ID" ) );

		$approve_url   = esc_url( "comment.php?action=approvecomment&p=$comment->comment_post_ID&c=$comment->comment_ID&$approve_nonce" );
		$unapprove_url = esc_url( "comment.php?action=unapprovecomment&p=$comment->comment_post_ID&c=$comment->comment_ID&$approve_nonce" );
		$spam_url      = esc_url( "comment.php?action=spamcomment&p=$comment->comment_post_ID&c=$comment->comment_ID&$del_nonce" );
		$trash_url     = esc_url( "comment.php?action=trashcomment&p=$comment->comment_post_ID&c=$comment->comment_ID&$del_nonce" );
		$delete_url    = esc_url( "comment.php?action=deletecomment&p=$comment->comment_post_ID&c=$comment->comment_ID&$del_nonce" );

		$actions['approve']   = "<a href ='$approve_url' data-wp-lists='dim:the-comment-list:comment-$comment->comment_ID:unapproved:e7e7d3:e7e7d3:new=approved' class='vim-a' title='" . esc_attr__( 'Approve this comment' ) . "'>" . __( 'Approve' ) . '</a>';
		$actions['unapprove'] = "<a href ='$unapprove_url' data-wp-lists='dim:the-comment-list:comment-$comment->comment_ID:unapproved:e7e7d3:e7e7d3:new=unapproved' class='vim-u' title='" . esc_attr__( 'Unapprove this comment' ) . "'>" . __( 'Unapprove' ) . '</a>';
		$actions['edit']      = "<a href ='comment.php?action=editcomment&amp;c={$comment->comment_ID}' title='" . esc_attr__('Edit comment') . "'>". __('Edit') . '</a>';
		$actions['reply']     = '<a onclick ="commentReply.open(\''.$comment->comment_ID.'\',\''.$comment->comment_post_ID.'\');return false;" class="vim-r hide-if-no-js" title="'.esc_attr__('Reply to this comment').'" href="#">' . __('Reply') . '</a>';
		$actions['spam']      = "<a href ='$spam_url' data-wp-lists='delete:the-comment-list:comment-$comment->comment_ID::spam=1' class='vim-s vim-destructive' title='" . esc_attr__( 'Mark this comment as spam' ) . "'>" . /* translators: mark as spam link */ _x( 'Spam', 'verb' ) . '</a>';

        if ( !EMPTY_TRASH_DAYS )
			$actions['delete'] = "<a href ='$delete_url' data-wp-lists='delete:the-comment-list:comment-$comment->comment_ID::trash=1' class='delete vim-d vim-destructive'>" . __('Delete Permanently') . '</a>';
		else
			$actions['trash'] = "<a href='$trash_url' data-wp-lists='delete:the-comment-list:comment-$comment->comment_ID::trash=1' class='delete vim-d vim-destructive' title='" . esc_attr__( 'Move this comment to the trash' ) . "'>" . _x('Trash', 'verb') . '</a>';

		$actions = apply_filters( 'comment_row_actions', array_filter($actions), $comment );

		$i = 0;
		foreach ( $actions as $action => $link ) {
			++$i;
			( ( ('approve' == $action || 'unapprove' == $action) && 2 === $i ) || 1 === $i ) ? $sep = '' : $sep = ' | ';

			// Reply and quickedit need a hide-if-no-js span
			if ( 'reply' == $action || 'quickedit' == $action )
				$action .= ' hide-if-no-js';

			$actions_string .= "<span class='$action'>$sep$link</span>";
		}
	}

?>

		<div id="comment-<?php echo $comment->comment_ID; ?>" <?php comment_class( array( 'comment-item', wp_get_comment_status($comment->comment_ID) ) ); ?>>
			<?php if ( !$comment->comment_type || 'comment' == $comment->comment_type ) : ?>

			<?php echo get_avatar( $comment, 50 ); ?>

			<div class="dashboard-comment-wrap">
			<h4 class="comment-meta">
				<?php printf( /* translators: 1: comment author, 2: post link, 3: notification if the comment is pending */__( 'From %1$s on %2$s%3$s' ),
					'<cite class="comment-author">' . get_comment_author_link() . '</cite>', $comment_post_link.' '.$comment_link, ' <span class="approve">' . __( '[Pending]' ) . '</span>' ); ?>
			</h4>

			<?php
			else :
				switch ( $comment->comment_type ) :
				case 'pingback' :
					$type = __( 'Pingback' );
					break;
				case 'trackback' :
					$type = __( 'Trackback' );
					break;
				default :
					$type = ucwords( $comment->comment_type );
				endswitch;
				$type = esc_html( $type );
			?>
			<div class="dashboard-comment-wrap">
			<?php /* translators: %1$s is type of comment, %2$s is link to the post */ ?>
			<h4 class="comment-meta"><?php printf( _x( '%1$s on %2$s', 'dashboard' ), "<strong>$type</strong>", $comment_post_link." ".$comment_link ); ?></h4>
			<p class="comment-author"><?php comment_author_link(); ?></p>

			<?php endif; // comment_type ?>
			<blockquote><p><?php comment_excerpt(); ?></p></blockquote>
			<p class="row-actions"><?php echo $actions_string; ?></p>
			</div>
		</div>
            
<?php
        }//end of function recent comments row
        
}//end of class
?>
