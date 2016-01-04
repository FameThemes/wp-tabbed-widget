/**
 * Created by truongsa on 1/4/16.
 */
jQuery( document ).ready( function( $ ){
   // alert( 'ok' );
} );


var WP_Tabbed_Widget;
WP_Tabbed_Widget = function( widget_id ){
    var tabs =  jQuery( widget_id );

    function tab_content_change( $context ){
        $context.on( 'change', '.widget_type', function(){
            var widget = jQuery( this).val();
            jQuery.ajax( {
                data: {
                    widget: widget,
                    action: 'wp_tabbed_get_settings_form',
                    _nonce: WP_Tabbed_Widget_Settings.nonce
                },
                url: ajaxurl,
                type: 'post',
                dataType: 'html',
                success: function( settings_html ){
                    jQuery( '.tabbed-widget-settings', $context ).html( settings_html );
                }
            } );

        } );
    }

    function update_value( nav, tab ){
        tab.on( 'change keyup', 'input, select, textarea',  function(){
            var data = jQuery( 'input, select, textarea', tab ).serialize();
            jQuery( 'input.tab-value', nav ).val( data );
            if ( jQuery('input[id*="-title"]', tab ).length > 0 ) {
                var title = jQuery('input[id*="-title"]', tab ).val() || WP_Tabbed_Widget_Settings.untitled;
                jQuery( '.wp-tw-label', nav ).text( title );
            }

        } );

    }

    //
    jQuery( '.wp-tw-tab-content', tabs ).each( function(){
        var settings_div =  jQuery( this );
        tab_content_change( settings_div );
    } );

    jQuery( '.wp-tw-nav li.wp-tw-title', tabs ).each( function( index ){
        var tab_id = 'tab-'+index+ ( new Date().getTime() );
        jQuery( this).attr( 'data-for', tab_id );
        jQuery( '.wp-tw-tab-content', tabs).eq( index ).attr( 'id', tab_id );
        jQuery( '.wp-tw-tab-content', tabs ).eq( index ).attr( 'data-index', index );
    } );


    // Witch to current tab
    tabs.on( 'click', '.wp-tw-nav li.wp-tw-title', function( e ){
        e.preventDefault();
        if ( ! jQuery( '.dashicons',  jQuery( this) ).is( e.target ) && ! jQuery( '.wp-tw-remove',  jQuery( this) ).is( e.target ) ) {
            if ( ! jQuery( this).hasClass( 'ui-state-disabled' ) ) {
                var id = jQuery( this ).attr( 'data-for' );
                jQuery( '.wp-tw-tab-content', tabs ).removeClass( 'tab-active' );
                jQuery( '#'+id+'.wp-tw-tab-content', tabs ).addClass( 'tab-active' );
                jQuery( '.wp-tw-nav li', tabs ).removeClass( 'nav-active' );
                jQuery( this ).addClass( 'nav-active' );

                jQuery( '.wp-tw-nav li.wp-tw-title', tabs ).each( function( index ){

                    if ( jQuery( this ).hasClass( 'nav-active' ) ) {
                        jQuery( 'input.current_active', tabs ).val( index );
                    }
                } );
            }
        }
    } );

    var current_active = jQuery( 'input.current_active', tabs).val();
    current_active =  parseInt( current_active );
    if( isNaN( current_active ) ) {
        current_active = 0;
    }

    console.log( current_active );

    if ( jQuery( '.wp-tw-nav li.wp-tw-title', tabs ).eq( current_active ).length ){
        jQuery( '.wp-tw-nav li.wp-tw-title', tabs ).eq( current_active ).trigger( 'click' );
    } else {
        jQuery( '.wp-tw-nav li.wp-tw-title', tabs ).eq( 0 ).trigger( 'click' );
    }




    function set_active_to_index( index ){
        if ( jQuery( '.wp-tw-nav li.wp-tw-title', tabs ).eq( index ).length > 0 ) {
            console.log( jQuery( '.wp-tw-nav li.wp-tw-title', tabs ).eq( index ) );
            jQuery( '.wp-tw-nav li.wp-tw-title', tabs ).eq( index ).trigger( 'click' );
        } else {
            jQuery( '.wp-tw-nav li.wp-tw-title', tabs ).eq( 0 ).trigger( 'click' );
        }
    }

    // Setup id for tabs
    jQuery( '.wp-tw-nav li.wp-tw-title', tabs ).each( function( index ){

        var tab_id = 'tab-'+index+ ( new Date().getTime() );
        var li =jQuery( this );
        li.attr( 'data-for', tab_id );
        var tab = jQuery( '.wp-tw-tab-content', tabs ).eq( index );
        tab.attr( 'id', tab_id );
        tab.attr( 'data-index', index );

        update_value( li, tab );

        var data = jQuery( 'input, select, textarea', tab ).serialize();
        jQuery( 'input.tab-value', li ).val( data );
        //console.log( data );

    } );


    // Sort tabs
    jQuery( ".wp-tw-nav", tabs ).sortable( {
        containment: "parent",
        items: "li:not(.ui-state-disabled)",
    });

    // Remove tab
    tabs.on( 'click', '.wp-tw-nav li .wp-tw-remove', function( e ){
        e.preventDefault();
        var parent = jQuery( this).parent();
        if ( ! parent.hasClass( 'ui-state-disabled' ) ) {
            var id = parent.attr( 'data-for' );
            jQuery( '#'+id+'.wp-tw-tab-content', tabs ).remove();
            parent.remove();
            var l = jQuery( '.wp-tw-nav li.wp-tw-title', tabs ).length;
            set_active_to_index( 0 );
        }
    } );

    // Add new tab
    tabs.on( 'click', '.add-new-tab', function( e ){
        e.preventDefault();
        var index = Math.floor( ( Math.random() * 100 ) + 1 );
        var tab_id = 'tab-'+index+ ( new Date().getTime() );

        var new_li = jQuery( WP_Tabbed_Widget_Settings.title_tpl ); // wp-tw-nav
        new_li.attr( 'data-for', tab_id );
        jQuery( '.wp-tw-nav', tabs).append( new_li );

        var tab_content = jQuery( WP_Tabbed_Widget_Settings.tab_tpl ); // wp-tw-nav
        tab_content.attr( 'id', tab_id );
        jQuery( '.wp-tw-tab-contents', tabs).append( tab_content );
        tab_content_change( tab_content );
        update_value( new_li, tab_content );
        new_li.trigger( 'click' );

    } );


};