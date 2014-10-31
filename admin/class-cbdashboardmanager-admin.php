<?php

/**
 * cbdashboardmanager
 *
 * @package    cbdashboardmanager
 * @author     Codeboxr <info@codeboxr.com>
 * @license    GPL-2.0+
 * @link       http://www.codeboxr.com
 * @copyright  2014 Codeboxr
 */

define('cbdashboardmanager', plugin_basename(__FILE__));
define('cbdashboardmanagername', 'Codeboxr Dashboard Widget Manager');
define('cbdashboardmanagerversion', '1.1.5');

if (!class_exists('cbdashboardmanager_Admin')):
    /**
     * cbdashboardmanager_Admin
     */
    class cbdashboardmanager_Admin
    {

        /**
         * Plugin version, used for cache-busting of style and script file references.
         *
         * @since   1.1
         *
         * @var     string
         */
        const VERSION           = '1.1.5';
        protected $plugin_slug  = 'cbdashboardmanager';
        /**
         * Instance of this class.
         *
         * @since    1.0.0
         *
         * @var      object
         */
        protected static $instance = null;
        /**
         * Initialize the plugin by setting localization and loading public scripts
         * and styles.
         *
         * @since     1.0.0
         */
        public $cb_widgets_data = array();

        public function __construct()
        {

            global $status, $page;

            require_once(plugin_dir_path(__FILE__) . "cbdashboardmanager_extrawidget.php");
            require_once(plugin_dir_path(__FILE__) . "cbdashboardmanager_customwidgets.php");

            $last_modified        = new cbdashboardmanager_customwidgets();
            $this->pluginbasename = plugin_basename(__FILE__);

            add_action('init', array($this, 'cbdashmanager_load_plugin_textdomain'));
            add_action('admin_enqueue_scripts', array($this, 'add_cbdashmanager_stylesheet'));
            // Activate plugin when new blog is added
            add_action('wpmu_new_blog', array($this, 'activate_new_site'));
            add_action('wp_dashboard_setup', array($this, 'cb_widget_manager'), 100);
            // Add an action link pointing to the options page.
            $plugin_basename = plugin_basename(plugin_dir_path(__DIR__) . $this->plugin_slug . '.php');
            add_filter('plugin_action_links_' . $plugin_basename, array($this, 'add_action_links'));
            add_action('admin_menu', array($this, 'add_cbdashmanager_main_plugin_page'));

        }

        /**
         * Add settings action link to the plugins page.
         *
         * @since    1.0.0
         */
        public function add_action_links($links)
        {

            return array_merge(
                array(
                    'settings' => '<a href = "' . admin_url('options-general.php?page=' . $this->plugin_slug) . '">' . __('Settings', $this->plugin_slug) . '</a>'
                ),
                $links
            );

        }

        /**
         * this function takes parameter hook for adding stylesheet to only this plugin option page
         * function to add additional scripts and style ,add my css file and icheck css,js
         */
        function custom_widgets()
        {

            $custom_widgets_array = array();
            global $wp_meta_boxes, $post;
            $args = array('post_type' => 'dashboardwidgets', 'posts_per_page' => -1);
            $loop = new WP_Query($args);

            while ($loop->have_posts()) : $loop->the_post(); //get all post of custom type named dashboardwidgets

                array_push($custom_widgets_array, $post->ID);

            endwhile;

            array_push($custom_widgets_array, md5('recent_comments'));
            array_push($custom_widgets_array, md5('recent_posts'));
            array_push($custom_widgets_array, md5('modified_posts'));

            return $custom_widgets_array;

        }

        /**
         * @param $hook
         * add scripts
         */
        function add_cbdashmanager_stylesheet($hook)
        {

            if ($hook != $this->page_hook) return;
            wp_enqueue_script('cbdashboardmanagercustom', plugin_dir_url(__FILE__) . 'assets/js/cbdashboardmanagercustom.js', array('jquery'));
            wp_enqueue_style('dashboardcb-style', plugin_dir_url(__FILE__) . 'assets/css/cbdmanager.css');
            wp_enqueue_style('dashboard-css', plugin_dir_url(__FILE__) . 'assets/css/cbdmanager.css');
            wp_enqueue_script('dashboard-js-2', plugin_dir_url(__FILE__) . 'assets/js/jquery.dataTables.js', array('jquery'));
            wp_enqueue_style('dashboard-cb-style2', plugin_dir_url(__FILE__) . 'assets/css/jquery.dataTables.css');

        }//end of function add_my_cb_stylesheet

        //adding option page

        function add_cbdashmanager_main_plugin_page()
        {

            $this->page_hook = add_options_page('Codeboxr Dashboard Widget Manager', 'Dashboard Widget Manager', 'manage_options', 'cbdashboardmanager', array($this, 'cbdashboardmanager_show_main_page'));
        }


        //showing main option page
        function cbdashboardmanager_show_main_page()
        {
            ?>
            <div class = "wrap">
            <div class = "icon32" id = "icon-options-general"></div>
            <h2><?php _e('Codeboxr Dashboard Widget Manager','cbdashboardmanager')?></h2>

            <div id = "poststuff" class = "metabox-holder has-right-sidebar">
            <div id = "post-body">
            <div id = "post-body-content">
            <div class = "stuffbox" style="padding: 15px;">

            <h2 style = ""><?php _e('Plugin Settings','cbdashboardmanager')?></h2>
            <p style = ""><?php _e('Check The Widgets Which You Want To Hide/Active For All Users .','cbdashboardmanager')?></p>
            <p style = "font-weight: bold;"><?php _e('Current Dashboard Widgets :','cbdashboardmanager')?></p>

            <?php
            global $wpdb;
            $cbcheckdashboard = '';

            require_once(ABSPATH . "/wp-admin/includes/dashboard.php");

            $dashWidgets            = array();
            $dashWidgets_side       = array();
            $dashWidgets_normalhigh = array();
            $dashWidgets_sidehigh   = array();
            $dashWidgetsfornormal   = array(); //for checkbox tracking only
            $dashWidgets_sideforside = array(); //for checkbox tracking only

            $registered_meta_boxes = get_option('dash_widget_manager_registered_widgets') ? get_option('dash_widget_manager_registered_widgets') : array();


            //*********************************************for adding extra widgets ***************************************************************
            if (isset($_POST['widgets']) && $_POST['widgets'] == "Save") {

                update_option('widgetsadded', (array)$_POST['widgetstoadd']);

            }
            if (isset($_POST['customwidgets']) && ($_POST['customwidgets'] == "Save" || $_POST['customwidgets'] == "Remove")) {

                update_option('customwidgetsadded', (array)$_POST['customwidgetstoadd']);

            }

            //*******************************************check all and save ************************************************************************
            if (isset($_POST['dashclsubmit']) && $_POST['dashclsubmit'] == "Check All & Deactive") {

                unset($dashWidgets);
                unset($dashWidgetsfornormal);
                unset($dashWidgets_normalhigh);
                unset($dashWidgets_side);
                unset($dashWidgets_sideforside);
                unset($dashWidgets_sidehigh);

                $dashWidgets             = array();
                $dashWidgetsfornormal    = array();
                $dashWidgets_normalhigh  = array();
                $dashWidgets_side        = array();
                $dashWidgets_sideforside = array();
                $dashWidgets_sidehigh    = array();

                foreach (is_array($registered_meta_boxes['normal']['core']) ? $registered_meta_boxes['normal']['core'] : array() as $normal) {

                    array_push($dashWidgets, $normal['id']);
                    array_push($dashWidgetsfornormal, $normal['id']);
                }

                foreach ((array_key_exists('high' , $registered_meta_boxes['normal'] ) && is_array($registered_meta_boxes['normal']['high'])) ? $registered_meta_boxes['normal']['high'] : array() as $normal) {

                    array_push($dashWidgets_normalhigh, $normal['id']);
                }

                foreach (is_array($registered_meta_boxes['side']['core']) ? $registered_meta_boxes['side']['core'] : array() as $side) {

                    array_push($dashWidgets_side, $side['id']);
                    array_push($dashWidgets_sideforside, $side['id']);
                }

                foreach ((array_key_exists('high' , $registered_meta_boxes['side'] ) && is_array($registered_meta_boxes['side']['high']) )? $registered_meta_boxes['side']['high'] : array() as $side) {

                    array_push($dashWidgets_sidehigh, $side['id']);
                }
                update_option('cbdashboardclean', $dashWidgets);
                update_option('cbdashboardcleanside', $dashWidgets_side);
                update_option('cbdashboardcleanfornormal', $dashWidgetsfornormal);
                update_option('cbdashboardcleansideforside', $dashWidgets_sideforside);
                update_option('cbdashboardclean_normal_high', $dashWidgets_normalhigh);
                update_option('cbdashboardclean_side_high', $dashWidgets_sidehigh);

            }

            //********************************************check none and save ***************************************************************

            if (isset($_POST['dashclsubmit']) && $_POST['dashclsubmit'] == "Check All & Active") {

                $blank_array = array();
                update_option('cbdashboardclean', $blank_array = array());
                update_option('cbdashboardcleanside', $blank_array = array());
                update_option('cbdashboardcleanfornormal', $blank_array = array());
                update_option('cbdashboardcleansideforside', $blank_array = array());
                update_option('cbdashboardclean_normal_high', $blank_array = array());
                update_option('cbdashboardclean_side_high', $blank_array = array());

            }

            //********************************************save button for unsetting all checked widget ********************************************************

            if (isset($_POST['dashclsubmit']) && $_POST['dashclsubmit'] == "Deactive") {

                update_option('cbdashboardclean', isset($_POST['Normal-Core']) ? (array)$_POST['Normal-Core'] :array() );
                update_option('cbdashboardcleanside', isset($_POST['Side-Core']) ?(array)$_POST['Side-Core'] :array()  );
                update_option('cbdashboardcleanfornormal', isset($_POST['Normal-Core']) ?(array)$_POST['Normal-Core'] :array()  );
                update_option('cbdashboardcleansideforside', isset($_POST['Side-Core']) ?(array)$_POST['Side-Core'] :array()  );
                update_option('cbdashboardclean_normal_high',isset($_POST['Normal-High']) ? (array)$_POST['Normal-High'] :array()  );
                update_option('cbdashboardclean_side_high',isset($_POST['Side-High']) ? (array)$_POST['Side-High'] :array()  );

            }
            if (isset($_POST['dashclsubmit']) && $_POST['dashclsubmit'] == "Active") {


                unset($dashWidgets);
                unset($dashWidgetsfornormal);
                unset($dashWidgets_normalhigh);
                unset($dashWidgets_side);
                unset($dashWidgets_sideforside);
                unset($dashWidgets_sidehigh);

                $dashWidgets             = array();
                $dashWidgetsfornormal    = array();
                $dashWidgets_normalhigh  = array();
                $dashWidgets_side        = array();
                $dashWidgets_sideforside = array();
                $dashWidgets_sidehigh    = array();

                foreach (is_array($registered_meta_boxes['normal']['core']) ? $registered_meta_boxes['normal']['core'] : array() as $normal) {

                    array_push($dashWidgets, $normal['id']);
                    array_push($dashWidgetsfornormal, $normal['id']);
                }

                foreach ((array_key_exists('high' , $registered_meta_boxes['normal'] ) && is_array($registered_meta_boxes['normal']['high']) )? $registered_meta_boxes['normal']['high'] : array() as $normal) {

                    array_push($dashWidgets_normalhigh, $normal['id']);
                }

                foreach (is_array($registered_meta_boxes['side']['core']) ? $registered_meta_boxes['side']['core'] : array() as $side) {

                    array_push($dashWidgets_side, $side['id']);
                    array_push($dashWidgets_sideforside, $side['id']);
                }

                foreach ((array_key_exists('high' , $registered_meta_boxes['side'] ) && is_array($registered_meta_boxes['side']['high']))? $registered_meta_boxes['side']['high'] : array() as $side) {

                    array_push($dashWidgets_sidehigh, $side['id']);
                }

                $normal_core_list = array_diff($dashWidgets, isset($_POST['Normal-Core']) ? (array)$_POST['Normal-Core'] : array());
                $side_core_list   = array_diff($dashWidgets_side,isset($_POST['Side-Core']) ? (array)$_POST['Side-Core'] : array());

                $normal_high_list = array_diff($dashWidgets_normalhigh,isset($_POST['Normal-High']) ? (array)$_POST['Normal-High'] : array());
                $side_high_list   = array_diff($dashWidgets_sidehigh, isset($_POST['Side-High']) ? (array)$_POST['Side-High'] : array());

                update_option('cbdashboardclean', $normal_core_list);
                update_option('cbdashboardcleanside', $side_core_list);
                update_option('cbdashboardcleanfornormal', $normal_core_list);
                update_option('cbdashboardcleansideforside', $side_core_list);
                update_option('cbdashboardclean_normal_high', $normal_high_list);
                update_option('cbdashboardclean_side_high', $side_high_list);


            }
            //  array_push($this->example_data,array('id'=>'5'));

            ?>
            <div class = "" style = "">

                <form method ="post" id = "dwm_options" action = "" enctype = "multipart/form-data">

                    <table id = "log-results" class = "widefat tablesorter display">
                        <thead>
                        <tr>
                            <th style = "background-image:none!important;text-align: left!important;padding: 2px!important;margin: 0px!important;">
                                <input type = "checkbox" class = "cb-widget-list-check-all" name = "check-all-widgets"   value = "check-all"></th>
                            <th><?php _e('Title','cbdashboardmanager'); ?></th>
                            <th><?php _e('Position','cbdashboardmanager'); ?></th>
                            <th><?php _e('Status','cbdashboardmanager'); ?></th>
                            <th><?php _e('Type','cbdashboardmanager'); ?></th>
                        </tr>
                        </thead>
                        <tbody id = "widgetlist">

                        <?php
                        $cbdashboardclean               = get_option('cbdashboardclean');
                        $cbdashboardcleanside           = get_option('cbdashboardcleanside');
                        $cbdashboardcleanfornormal      = get_option('cbdashboardcleanfornormal') ? get_option('cbdashboardcleanfornormal') : array();
                        $cbdashboardcleansideforside    = get_option('cbdashboardcleansideforside') ? get_option('cbdashboardcleansideforside') : array();
                        $cbdashboardclean_for_sidehigh  = get_option('cbdashboardclean_side_high') ? get_option('cbdashboardclean_side_high') : array();
                        $cbdashboardclean_for_normalhigh = get_option('cbdashboardclean_normal_high') ? get_option('cbdashboardclean_normal_high') : array();
                        ?>
                        <?php
                        // pushing the value  to example data
                        $normal_core = $registered_meta_boxes['normal']['core'] ? $registered_meta_boxes['normal']['core'] : array();

                        foreach ($normal_core as $normal) {

                            $normal_core_position = 'Normal-Core';
                            $normal_core_status   = (in_array($normal['id'], $cbdashboardcleanfornormal)) ? 'Hidden' : 'Active';
                            $normal_core_type     = (in_array($normal['id'], self::custom_widgets())) ? 'Custom' : 'Default';

                            array_push($this->cb_widgets_data, array('ID' => $normal['id'], 'title' => $normal['title'], 'cbstatus' => $normal_core_status, 'cbposition' => $normal_core_position, 'type' => $normal_core_type));

                            ?>
                            <tr class="cbcheckbox">

                                <td>
                                    <input id = "cb-select" class = "cbwdchkbox" type = "checkbox" name = "Normal-Core[]"  value = "<?php echo $normal['id'] ?>"<?php echo (in_array($normal['id'], $cbdashboardcleanfornormal)) ? 'checked' : ''; ?> >
                                </td>
                                <td>
                                    <label><?php echo $normal['title'] ?></label>
                                </td>
                                <td>
                                    <label><?php echo 'Normal-Core' ?></label>
                                </td>
                                <td>
                                    <label><?php echo (in_array($normal['id'], $cbdashboardcleanfornormal)) ? 'Hidden' : 'Active'; ?></label>
                                </td>
                                <td>
                                    <label><?php echo (in_array($normal['id'], self::custom_widgets())) ? 'Custom' : 'Default'; ?></label>
                                </td>
                            </tr>

                        <?php } ?>
                        <?php

                        $side_core = $registered_meta_boxes['side']['core'] ? $registered_meta_boxes['side']['core'] : array();

                        foreach ($side_core as $side) {

                            $side_core_position = 'Side-Core';
                            $side_core_status   = (in_array($side['id'], $cbdashboardcleansideforside)) ? 'Hidden' : 'Active';
                            $side_core_type     = (in_array($side['id'], self::custom_widgets())) ? 'Custom' : 'Default';

                            array_push($this->cb_widgets_data, array('ID' => $side['id'], 'title' => $side['title'], 'cbstatus' => $side_core_status, 'cbposition' => $side_core_position, 'type' => $side_core_type));

                            ?>
                            <tr class = "cbcheckbox">

                                <td>
                                    <input class = "cbwdchkbox" style = "" type = "checkbox" name = "Side-Core[]"
                                           value = "<?php echo $side['id'] ?>" <?php echo (in_array($side['id'], $cbdashboardcleansideforside)) ? 'checked' : ''; ?>>
                                </td>
                                <td>
                                    <label>  <?php echo $side['title'] ?></label>
                                </td>
                                <td>
                                    <label>  <?php echo 'Side-Core' ?></label>
                                </td>
                                <td>
                                    <label><?php echo (in_array($side['id'], $cbdashboardcleansideforside)) ? 'Hidden' : 'Active'; ?></label>
                                </td>
                                <td>
                                    <label><?php echo (in_array($side['id'], self::custom_widgets())) ? 'Custom' : 'Default'; ?></label>
                                </td>

                            </tr>

                        <?php } ?>
                        <?php
                        $side_high_position_widget = (array_key_exists('high', $registered_meta_boxes['side']) && $registered_meta_boxes['side']['high']) ? $registered_meta_boxes['side']['high'] : array();

                        foreach ($side_high_position_widget as $sidehigh) {

                            $sidehigh_core_position = 'Side-High';
                            $sidehigh_core_status   = (in_array($sidehigh['id'], $cbdashboardclean_for_sidehigh)) ? 'Hidden' : 'Active';
                            $sidehigh_core_type     = (in_array($sidehigh['id'], self::custom_widgets())) ? 'Custom' : 'Default';

                            array_push($this->cb_widgets_data, array('ID' => $sidehigh['id'], 'title' => $sidehigh['title'], 'cbstatus' => $sidehigh_core_status, 'cbposition' => $sidehigh_core_position, 'type' => $sidehigh_core_type));

                            ?>
                            <tr class="cbcheckbox">
                                <td>
                                    <input class = "cbwdchkbox" style = "" type = "checkbox" name = "Side-High[]"
                                           value = "<?php echo $sidehigh['id'] ?>" <?php echo (in_array($sidehigh['id'], $cbdashboardclean_for_sidehigh)) ? 'checked' : ''; ?>>
                                </td>
                                <td>
                                    <label>  <?php echo $sidehigh['title'] ?></label>
                                </td>
                                <td>
                                    <label>  <?php echo 'Side-High' ?></label>
                                </td>
                                <td>
                                    <label><?php echo (in_array($sidehigh['id'], $cbdashboardclean_for_sidehigh)) ? 'Hidden' : 'Active'; ?></label>
                                </td>
                                <td>
                                    <label><?php echo (in_array($sidehigh['id'], self::custom_widgets())) ? 'Custom' : 'Default'; ?></label>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php
                        $normal_high_position_widget = (array_key_exists('high', $registered_meta_boxes['normal']) && $registered_meta_boxes['normal']['high']) ? $registered_meta_boxes['normal']['high'] : array();

                        foreach ($normal_high_position_widget as $normalhigh) {

                            $normalhigh_core_position = 'Normal-High';
                            $normalhigh_core_status   = (in_array($normalhigh['id'], $cbdashboardclean_for_normalhigh)) ? 'Hidden' : 'Active';
                            $normalhigh_core_type     = (in_array($normalhigh['id'], self::custom_widgets())) ? 'Custom' : 'Default';

                            array_push($this->cb_widgets_data, array('ID' => $normalhigh['id'], 'title' => $normalhigh['title'], 'cbstatus' => $normalhigh_core_status, 'cbposition' => $normalhigh_core_position, 'type' => $normalhigh_core_type));
                            ?>
                            <tr class="cbcheckbox">

                                <td class="cbcheckbox">
                                    <input class = "cbwdchkbox" style =" " type ="checkbox"  name = "Normal-High[]" value = "<?php echo $normalhigh['id'] ?>"<?php echo (in_array($normalhigh['id'], $cbdashboardclean_for_normalhigh)) ? 'checked' : ''; ?> >
                                </td>
                                <td>
                                    <label>  <?php echo $normalhigh['title'] ?></label>
                                </td>
                                <td>
                                    <label>  <?php echo 'Normal-High' ?></label>
                                </td>
                                <td>
                                    <label><?php echo (in_array($normalhigh['id'], $cbdashboardclean_for_normalhigh)) ? 'Hidden' : 'Active'; ?></label>
                                </td>
                                <td>
                                    <label><?php echo (in_array($normalhigh['id'], self::custom_widgets())) ? 'Custom' : 'Default'; ?></label>
                                </td>

                            </tr>

                        <?php } ?>

                        </tbody>
                    </table>


                    <div style = "margin-top: 10px;margin-bottom: 10px">

                        <input class = "button button-primary button-small" style = "margin-left:0px;" type = submit
                               name = "dashclsubmit" value = "Active" tabindex = "8">

                        <input class = "button button-primary button-small" style = "margin-left:10px;" type = submit
                               name = "dashclsubmit" value = "Deactive" tabindex = "8">

                        <input class = "cbdashboardwidgetcheckall button button-primary button-small" style = "margin-left:10px;" type = submit
                               name = "dashclsubmit" value = "Check All & Active" tabindex = "8">

                        <input class = "cbdashboardwidgetcheckall button button-primary button-small" style = "margin-left:10px;" type = submit
                               name = "dashclsubmit" value = "Check All & Deactive" tabindex = "8">

                    </div>
                </form>
            </div>

            <?php
            $customcheckwidgets = get_option('customwidgetsadded') ? get_option('customwidgetsadded') : array();
            $checkwidgets = get_option('widgetsadded') ? get_option('widgetsadded') : array();
            ?>
            <h2 ><?php _e('Custom Widgets','cbdashboardmanager');?></h2>
            <form method="post" id="dwm_options2" action="" enctype="multipart/form-data">

                
                    <p>
                        <span style = "margin-top:5px;"> <input type = "checkbox" id = "custom_widget_checkbox"
                                                              name = "customwidgetstoadd[]"
                                                              value = "dashboardposttype" <?php echo (in_array("dashboardposttype", $customcheckwidgets)) ? 'checked = "checked"' : ''; ?>>
                       <span style = "display:inline-block; margin-top:5px"><?php _e(' Enable Custom Post Type For Widgets','cbdashboardmanager');?>  </span></span>

                    </p>
                    <p>
                        <input class = "button button-primary button-small" id = "custom_widget" style = "margin-top:15px; "
                               type = submit name = "customwidgets" value = "Save" tabindex = "8">
                    </p>

            </form>

            <div style="">
                <?php new cbdashboardmanager_extrawidget(); ?>
            </div>

            </div>
            </div>
            </div>

            <?php
            //****************************************addintional features*******************************************
            $plugin_data = get_plugin_data(plugins_url() . '/cbdashboardmanager/cbdashboardmanager.php');

            ?>

            <div id="side-info-column" class="inner-sidebar">
                <div class="postbox">
                    <h3>Plugin Info</h3>

                    <div class="inside">
                        <?php //var_dump($plugin_data); ?>
                        <p>Plugin Name
                            : <?php echo cbdashboardmanagername ?> <?php echo cbdashboardmanagerversion ?></p>

                        <p>Author : <?php echo 'Codeboxr' ?></p>

                        <p>Website : <a href="http://codeboxr.com" target="_blank">codeboxr.com</a></p>

                        <p>Email : <a href="mailto:info@codeboxr.com" target="_blank">info@codeboxr.com</a></p>

                        <p>Twitter : @<a href="http://twitter.com/codeboxr" target="_blank">Codeboxr</a></p>

                        <p>Facebook : <a href="http://facebook.com/codeboxr" target="_blank">http://facebook.com/codeboxr</a>
                        </p>

                        <p>Linkedin : <a href="www.linkedin.com/company/codeboxr" target="_blank">codeboxr</a></p>

                        <p>Gplus : <a href="https://plus.google.com/104289895811692861108" target="_blank">Google
                                Plus</a></p>
                    </div>
                </div>

                <div class="postbox">
                    <h3>Help & Supports</h3>

                    <div class="inside">
                        <p>Support: <a href="http://codeboxr.com/contact-us.html" target="_blank">Contact Us</a></p>

                        <p><i class="icon-envelope"></i> <a href="mailto:info@codeboxr.com">info@codeboxr.com</a></p>

                        <p><i class="icon-phone"></i> <a href="tel:008801717308615">+8801717308615</a> (Sabuj Kundu, CEO)</p>

                    </div>
                </div>

                <div class="postbox">
                    <h3>Codeboxr Updates</h3>

                    <div class="inside">
                        <?php
                        include_once(ABSPATH . WPINC . '/feed.php');
                        if (function_exists('fetch_feed')) {
                            $feed = fetch_feed('http://codeboxr.com/feed');
                            // $feed = fetch_feed('http://feeds.feedburner.com/codeboxr'); // this is the external website's RSS feed URL
                            if (!is_wp_error($feed)) : $feed->init();
                                $feed->set_output_encoding('UTF-8'); // this is the encoding parameter, and can be left unchanged in almost every case
                                $feed->handle_content_type(); // this double-checks the encoding type
                                $feed->set_cache_duration(21600); // 21,600 seconds is six hours
                                $limit = $feed->get_item_quantity(6); // fetches the 18 most recent RSS feed stories
                                $items = $feed->get_items(0, $limit); // this sets the limit and array for parsing the feed

                                $blocks = array_slice($items, 0, 6); // Items zero through six will be displayed here
                                echo '<ul>';
                                foreach ($blocks as $block) {
                                    $url = $block->get_permalink();
                                    echo '<li><a target="_blank" href="' . $url . '">';
                                    echo '<strong>' . $block->get_title() . '</strong></a></li>';
                                }
                                //end foreach
                                echo '</ul>';


                            endif;
                        }
                        ?>
                    </div>
                </div>

                <div class="postbox">
                    <h3>Codeboxr on facebook</h3>

                    <div class="inside">
                        <iframe
                            src="//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Fcodeboxr&amp;width=260&amp;height=258&amp;show_faces=true&amp;colorscheme=light&amp;stream=false&amp;border_color&amp;header=false&amp;appId=558248797526834"
                            scrolling="no" frameborder="0"
                            style="border:none; overflow:hidden; width:260px; height:258px;"
                            allowTransparency="true"></iframe>
                    </div>
                </div>

            </div>
            </div>
            </div>
        <?php

        }
//end of main option page show function


//*************************************main unset function*************************************************************************
        function cb_widget_manager(){

            $checkwidgets = get_option('widgetsadded') ? get_option('widgetsadded') : array();
            foreach ($checkwidgets as $widgettoadd) {
                //checking
            }

            global $wp_meta_boxes, $wpdb; //global wp_meta_boxs has all type of meta boxs

            //this saves all metaboxs of dashboard .its necessary for listing all in main option page

            if (current_user_can('administrator') && is_array($wp_meta_boxes['dashboard'])) {

                update_option('dash_widget_manager_registered_widgets', $wp_meta_boxes['dashboard']);
            }

            //getting all options as array which was checked and updated

            $getWidgets             = (array)get_option('cbdashboardclean');
            $getWidgets_side        = (array)get_option('cbdashboardcleanside');
            $getWidgets_normal_high = (array)get_option('cbdashboardclean_normal_high');
            $getWidgets_side_high   = (array)get_option('cbdashboardclean_side_high');

            //to unset normal core
            $unset_normal_core = $wp_meta_boxes['dashboard']['normal']['core'] ? $wp_meta_boxes['dashboard']['normal']['core'] : array();
            foreach ($unset_normal_core as $widget) {

                if (in_array($widget['id'], $getWidgets)) {

                    unset($wp_meta_boxes['dashboard']['normal']['core'][$widget['id']]);
                }
            }
            //to unset side core

            $unset_side_core = $wp_meta_boxes['dashboard']['side']['core'] ? $wp_meta_boxes['dashboard']['side']['core'] : array();
            foreach ($unset_side_core as $widgetside) {

                if (in_array($widgetside['id'], $getWidgets_side)) {

                    unset($wp_meta_boxes['dashboard']['side']['core'][$widgetside['id']]);
                }
            }
            //to unset side high
            $unset_side_high = (array_key_exists('high', $wp_meta_boxes['dashboard']['side']) && $wp_meta_boxes['dashboard']['side']['high']) ? $wp_meta_boxes['dashboard']['side']['high'] : array();

            foreach ($unset_side_high as $widget_side_high) {

                if (in_array($widget_side_high['id'], $getWidgets_side_high)) {

                    unset($wp_meta_boxes['dashboard']['side']['high'][$widget_side_high['id']]);
                }
            }
            //to unset normal high
            // $unset_normal_high =
            $normal_high_metabox = (array_key_exists('high', $wp_meta_boxes['dashboard']['normal']) && $wp_meta_boxes['dashboard']['normal']['high']) ? $wp_meta_boxes['dashboard']['normal']['high'] : array();
            foreach ($normal_high_metabox as $widget_normal_high) {

                if (in_array($widget_normal_high['id'], $getWidgets_normal_high)) {

                    unset($wp_meta_boxes['dashboard']['normal']['high'][$widget_normal_high['id']]);
                }
            }
            //end of all for each

        }//end of function main unset function

        /**
         * Return the plugin slug.
         *
         * @since    1.0.0
         *
         * @return    Plugin slug variable.
         */
        public function get_plugin_slug()
        {
            return $this->plugin_slug;
        }

        /**
         * Return an instance of this class.
         *
         * @since     1.0.0
         *
         * @return    object    A single instance of this class.
         */
        public static function get_instance()
        {

            // If the single instance hasn't been set, set it now.
            if (null == self::$instance) {
                self::$instance = new self;
            }

            return self::$instance;
        }


        /**
         * Load the plugin text domain for translation.
         *
         * @since    1.0.0
         */
        public function cbdashmanager_load_plugin_textdomain()
        {

            $domain = $this->plugin_slug;
            $locale = apply_filters('plugin_locale', get_locale(), $domain);

            load_textdomain($domain, trailingslashit(WP_LANG_DIR) . $domain . '/' . $domain . '-' . $locale . '.mo');

        }


    }// end of classs
endif;