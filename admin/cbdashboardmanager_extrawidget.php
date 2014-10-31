<?php

/**
 * Class cbdashboardmanager_extrawidget
 */
class cbdashboardmanager_extrawidget {

    public $recentcomments, $recentpost;
    public $cb_extra_widgets_data = array();

    function __construct() {

        global $status, $page;
        //Set parent defaults
        $this->pluginbasename   = plugin_basename(__FILE__);
        $checkwidgets           = get_option('widgetsadded') ? get_option('widgetsadded') : array();
        $customcheckwidgets     = get_option('customwidgetsadded') ? get_option('customwidgetsadded') : array();
        $custom_post_type       = "dashboardposttype";
        self::extra_widgets();
    }

    /**
     * extra_widgets
     */

    function extra_widgets(){

    $checkwidgets = get_option('widgetsadded') ? get_option('widgetsadded') : array();
    array_push($this->cb_extra_widgets_data , array('ID'=>'lastmodified','title' =>'Last Modified Post Widgets'));
    array_push($this->cb_extra_widgets_data , array('ID'=>'recentpost','title' =>'Recent Post Widget'));
    array_push($this->cb_extra_widgets_data , array('ID'=>'recentcomments','title' =>'Recent Comment Widget For All Post Types'));

    ?>
    <form method = "post" id = "dwm_options2" action = "" enctype = "multipart/form-data">

        <div style = "">

            <p style = "margin-left:15px;">
                <h2><?php _e('Extra Widgets','cbdashboardmanager')?></h2>
            </p>

            <table id = "extra_widget" class = "widefat tablesorter display">

                <thead>
                <tr>
                    <th style = "background-image:none!important;text-align: left!important; padding: 10px!important;margin: 0px!important; ">
                        <input style="margin-left: 0px !important;" type = "checkbox" class = "cb-widget-check-all" name = "check-all-extra-widgets" value = "check-all" >
                    </th>

                    <th><?php _e('Title','cbdashboardmanager')?></th>

                </tr>
                </thead>

                <tbody>

                <tr id = "extra_widget_col">
                    <td><input type = "checkbox" class = "cb-widget-check" name = "widgetstoadd[]" value = "lastmodified" <?php echo (in_array("lastmodified", $checkwidgets)) ? 'checked = "checked"' : ''; ?> ></td>
                    <td><span style = "display:inline-block;"> <?php _e('Last Modified Post Widgets','cbdashboardmanager')?>  </span></td>
                </tr>
                <tr>
                    <td><input type = "checkbox" class = "cb-widget-check" name = "widgetstoadd[]" value = "recentpost" <?php echo (in_array("recentpost", $checkwidgets)) ? 'checked = "checked"' : ''; ?>></td>
                    <td><span style = "display:inline-block;"> <?php _e('Recent Post Widget ','cbdashboardmanager')?>  </span></td>
                </tr>
                <tr>
                    <td><input type = "checkbox" class = "cb-widget-check" name = "widgetstoadd[]" value = "recentcomments" <?php echo (in_array("recentcomments", $checkwidgets)) ? 'checked = "checked"' : ''; ?> ></td>
                    <td><span style = "display:inline-block;">  <?php _e('Recent Comment Widget For All Post Types','cbdashboardmanager')?> </span></td>
                </tr>
                </tbody>
            </table>
        </div>

        <p><input class = "button button-primary button-small" style = "" type = submit name = "widgets" value = "Save" tabindex = "8" /></p>
    </form>

<?php

} // end of function extra_widgets

        
}// end of function class
?>


