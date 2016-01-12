
jQuery( document ).ready( function( $ ) {

    $( '.wp-tabbed-tabs').each( function(){
        var tabs =  $( this );

        tabs.on( 'click', '.wp-tabbed-nav li', function( e ){
            e.preventDefault();
            var tab = $( this );
            var t = tab.attr( 'data-tab' );
            if ( typeof t !== "undefined" ) {
               $( '.wp-tabbed-nav li', tabs ).removeClass('tab-active' );
                tab.addClass( 'tab-active' );
                $( '.wp-tabbed-cont',  tabs).removeClass('tab-active');
                $( '.wp-tabbed-cont.'+t,  tabs).addClass('tab-active');

            }
        } );

        $( '.wp-tabbed-nav li', tabs).eq( 0).trigger( 'click' );

    } );

} );