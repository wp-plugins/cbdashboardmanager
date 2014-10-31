/**
 * Created by codeboxr on 10/29/14.
 */
jQuery( document ).ready(function($) {
    $('.cb-widget-list-check-all').click(function(){
        if($(this).prop('checked') == true){
            $('.cbwdchkbox').prop('checked','checked');
        }
        else{
            $('.cbwdchkbox').prop('checked','');
        }
    });
////////////////////////////check all custom widget
    $('.cb-widget-check-all').click(function(){
        if($(this).prop('checked') == true){
            $('.cb-widget-check').prop('checked','checked');
        }
        else{
            $('.cb-widget-check').prop('checked','');
        }
    });
    ////////////////////////////cbdashboardwidgetcheckall check all button click
    $('.cbdashboardwidgetcheckall').click(function(){

            $('.cbwdchkbox').prop('checked','checked');

    });
});// end of dom ready