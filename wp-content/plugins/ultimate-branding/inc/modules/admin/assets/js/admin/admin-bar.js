/**
 * Branda: Admin Bar
 * http://premium.wpmudev.org/
 *
 * Copyright (c) 2018-2019 Incsub
 * Licensed under the GPLv2 +  license.
 */
/* global window, SUI, ajaxurl */
/**
 * Globals
 */
var Branda = Branda || {};
Branda.admin_bar_dialog_edit = 'branda-admin-bar-edit';
Branda.admin_bar_dialog_delete = 'branda-admin-bar-delete';
var $branda_admin_bar_entries_parent;
jQuery( document ).ready( function( $ ) {
    $branda_admin_bar_entries_parent = $( '.branda-settings-tab-content-admin-bar .branda-admin-bar-items-custom-entries' );
});
/**
 * Bind row buttons
 */
Branda.admin_bar_row_buttons_bind = function( container ) {
    jQuery( '[data-a11y-dialog-show=' + Branda.admin_bar_dialog_edit + ']', container ).on( 'click', function( e ) {
        var jQuerybutton = jQuery(this);
        var jQuerydialog = jQuery( '#' + Branda.admin_bar_dialog_edit );
        var jQueryparent = jQuerybutton.closest( '.sui-builder-field' );
        var data = {
            id: 'undefined' !== typeof jQueryparent.data( 'id' )? jQueryparent.data( 'id' ):'new'
        };
        var template, nonce;
        e.preventDefault();
        /**
         * Dialog class
         */
        if ( 'new' === data.id ) {
            jQuerydialog.addClass( 'branda-dialog-new' );
            nonce = jQuerybutton.data( 'nonce' );
        } else {
            jQuerydialog.removeClass( 'branda-dialog-new' );
            nonce = jQueryparent.data( 'nonce' );
        }
        /**
         * fetch data
         */
        var args = {
            action: 'branda_admin_bar_get',
            _wpnonce: nonce,
            id: data.id
        };
        jQuery('.sui-box-title span.edit', jQuerydialog ).show();
        jQuery.ajax({
            url: ajaxurl,
            method: 'POST',
            data: args,
            async: false
        }).success( function( response ) {
            if ( ! response.success ) {
                window.ub_sui_notice( response.data.message, 'error' );
            }
            data = response.data;
        });
        if ( 'undefined' === typeof data.title ) {
            return false;
        }
        /**
         * set ID
         */
        jQuery( 'input[name="branda[id]"]', jQuerydialog ).val( data.id );
        jQuery( 'input[name="branda[nonce]"]', jQuerydialog ).val( jQueryparent.data( 'nonce' ) );
        /**
         * General
         */
        template = wp.template( Branda.admin_bar_dialog_edit + '-pane-general' );
        jQuery( '.' + Branda.admin_bar_dialog_edit + '-pane-general', jQuerydialog ).html( template( data ) );
        /**
         * submenu
         */
        template = wp.template( Branda.admin_bar_dialog_edit + '-pane-submenu' );
        jQuery( '.' + Branda.admin_bar_dialog_edit + '-pane-submenu', jQuerydialog ).html( template( data ) );
        /**
         * visibility
         */
        template = wp.template( Branda.admin_bar_dialog_edit + '-pane-visibility' );
        jQuery( '.' + Branda.admin_bar_dialog_edit + '-pane-visibility', jQuerydialog ).html( template( data ) );
        /**
         * Re-init elements
         */
        branda_admin_bar_redirect_bind();
		branda_admin_bar_dashicons_bind(data.icon);
        branda_admin_bar_submenu_bind();
        if ( 'undefined' !== typeof data.submenu ) {
            jQuery.each( data.submenu, function( id, args )  {
                branda_admin_bar_submenu_add( args );
            });
        }
        SUI.suiTabs();
        SUI.brandaSideTabs();
        jQuery( '.sui-accordion', jQuerydialog ).each( function() {
            SUI.suiAccordion( this );
        });
        jQuery( '.sui-tabs-flushed .branda-first-tab', jQuerydialog ).trigger( 'click' );
    });
    /**
     * Set data on delete modal
     */
    jQuery( '[data-a11y-dialog-show=' + Branda.admin_bar_dialog_delete + ']', container ).on( 'click', function( e ) {
        var jQueryparent = jQuery(this).closest( '.sui-builder-field' );
        jQuery( 'button.branda-admin-bar-delete', jQuery('#'+ jQuery(this).data( 'a11yDialogShow' ) ) )
            .data( 'nonce', jQueryparent.data( 'nonce' ) )
            .data( 'id', jQueryparent.data( 'id' ) )
        ;
    });
};
/**
 * Search icons
 */
function branda_admin_bar_dashicons_bind(icon) {
	var $dialog = jQuery('#' + Branda.admin_bar_dialog_edit),
		select = new Branda.Dashicon_Select(
			jQuery('.branda-admin-bar-edit-pane-general .branda-general-icon'),
			icon
		);

	jQuery(select).on('dashicon-selected', function () {
		jQuery('.branda-visibility-mobile', $dialog).show();
	});

	jQuery(select).on('dashicon-cleared', function () {
		jQuery('.branda-visibility-mobile', $dialog).hide();
	});
}

jQuery(document).ready(function($){
    var Branda_Ordering = {
        children : function(hide){
            hide = typeof hide === "undefined" ? true : false;
            if( hide ){
                $("#wpadminbar ul#wp-admin-bar-root-default > li").css({
                    cursor : "move"
                }).find(".ab-sub-wrapper").css({
                    visibility : "hidden"
                });
            }else{
                $("#wpadminbar ul#wp-admin-bar-root-default > li").css({
                    cursor : "default"
                }).find(".ab-sub-wrapper").css({
                    visibility : "visible"
                });
            }

        },
        sortable : function( make ) {
            make = typeof make === "undefined" ? true : false;
            if( make ){
                $("#wpadminbar ul#wp-admin-bar-root-default .ab-item").addClass("click_disabled");
                $("#wpadminbar ul#wp-admin-bar-root-default").sortable({
                    axis: "x",
                    forceHelperSize: true,
                    forcePlaceholderSize: true,
                    distance : 2,
                    handle: ".ab-item",
                    tolerance: "intersect",
                    cursor: "move"
                }).sortable( "enable" );
            }else{
                $("#wpadminbar ul#wp-admin-bar-root-default .ab-item").removeClass("click_disabled");
                $("#wpadminbar ul#wp-admin-bar-root-default").sortable( "disable" );
            }
        },
        wiggle : function(wiggle) {
            wiggle = typeof wiggle === "undefined" ? true : false;
            var $el = $("#wpadminbar ul#wp-admin-bar-root-default > li");
            if( wiggle ){
                $( document ).scrollTop( 0 );
                $el.ClassyWiggle("start", {
                    degrees: ['2', '4', '2', '0', '-2', '-4', '-2', '0'],
                    delay : 90
                });
            }else{
                $el.ClassyWiggle("stop");
            }
        },
        add_save_button : function(){
            $("#ub_admin_bar_save_ordering").remove();
            $("#wp-admin-bar-root-default").after('<div class="sui-wrap"><button id="ub_admin_bar_save_ordering" class="sui-button sui-button-blue" type="button"><span class="sui-loading-text"><i class="sui-icon-save"></i>'+ub_admin.buttons.save_changes+'</span></button></div>' );
        },
        start : function(){
            this.children();
            this.sortable();
            this.wiggle();
            this.add_save_button();
        },
        stop : function(){
            this.children( false );
            this.sortable( false );
            this.wiggle( false );
            $("#ub_admin_bar_save_ordering").slideUp(100, function(){
                $(this).remove();
            });
        },
        save : function(){
            var self = this, $button = $( "#ub_admin_bar_save_ordering" );
            $button.attr("disabled", true).addClass("ub_loading");
            order = [];
            $("#wpadminbar #wp-admin-bar-root-default > li").each(function(){
                if( typeof this.id === "string" &&  this.is !== "" ){
                    order.push( this.id.replace( "wp-admin-bar-", "" ) );
                }
            });
            $.ajax({
                url      : ajaxurl,
                type     : 'post',
                data     : {
                    action: 'branda_admin_bar_order_save',
                    _wpnonce: $('#branda-admin-bar-reorder-nonce').val(),
                    order: order
                },
                success  : function( response ) {
                    if ( "undefined" !== typeof response.data.message ) {
                        if ( response.success ) {
                            window.ub_sui_notice( response.data.message );
                        } else {
                            window.ub_sui_notice( response.data.message, 'error' );
                        }
                    }
                },
                complete: function() {
                    $button.attr("disabled", false).removeClass("ub_loading").remove();
                    self.stop();
                }
            });
        }
    };
    $("#ub_admin_bar_start_ordering").on("click", function( e ){
        e.preventDefault();
        Branda_Ordering.start();
    });
    $(document).on("click", "#ub_admin_bar_save_ordering", function( e ){
        e.preventDefault();
        Branda_Ordering.save();
    });
});
/**
 * Add item
 */
jQuery( window.document ).ready( function( $ ){
    "use strict";
    /**
     * Open add/edit modal
     */
    $('.branda-admin-bar-save').on( 'click', function() {
        var parent = $('.sui-box-body', $(this).closest( '.sui-box' ) );
        var $dialog = $(this).closest( '.sui-dialog' );
        var reqired = false;
        var id = $(this).data('id');
        $('[data-required=required]', parent ).each( function() {
            if ( '' === $(this).val() ) {
                var local_parent = $(this).parent();
                var accordion_parent;
                local_parent.addClass('sui-form-field-error');
                $( 'span.hidden', local_parent ).addClass( 'sui-error-message' );
                /**
                 * Go only to first field
                 */
                if ( ! reqired ) {
                    /**
                     * Go to proper tab
                     */
                    $( 'div[data-tabs] div[data-tab=' + $(this).closest( '[data-tab]' ).data('tab') + ']', $dialog ).trigger( 'click' );
                    /**
                     * Check & go to proper accordion item
                     */
                    accordion_parent = $(this).closest( '.sui-accordion-item' );
                    if ( 0 < accordion_parent.length ) {
                        if ( ! accordion_parent.hasClass( 'sui-accordion-item--open' ) ) {
                            $( '.sui-accordion-open-indicator', accordion_parent ).trigger( 'click' );
                        }
                    }
                }
                reqired = true;
            }
        });
        if ( reqired ) {
            return;
        }
        var data = {
            action: 'branda_admin_bar_menu_save',
            _wpnonce: $(this).data('nonce'),
            id: id,
        };
        $('input, textarea', parent).each( function() {
            var n = $(this).attr('name');
            switch( $(this).attr('type') ) {
                case 'checkbox':
                case 'radio':
                    if ( $(this).is(':checked') ) {
                        data[n] = $(this).val();
                    }
                    break;
                default:
                    data[n] = $(this).val();
            }
        });
        $.post( ajaxurl, data, function( response ) {
            if ( response.success ) {
                var $row = $('[data-id=' + response.data.id + ']', $branda_admin_bar_entries_parent );
                if ( 0 < $row.length ) {
                    $( '.sui-builder-field-label', $row ).html( response.data.title_to_show );
                    $( '.sui-builder-field', $row ).data( 'nonce', response.data.nonce );
                } else {
                    var template = wp.template( Branda.admin_bar_dialog_edit + '-row' );
                    $('.sui-box-builder-fields', $branda_admin_bar_entries_parent ).append( template( response.data ) );
                    $.fn.branda_sui_dialog_rebind([
                        Branda.admin_bar_dialog_edit,
                        Branda.admin_bar_dialog_delete
                    ]);
                    $row = $('[data-id=' + response.data.id + ']', $branda_admin_bar_entries_parent );
                    Branda.admin_bar_row_buttons_bind( $row );
                }
                SUI.dialogs[ parent.closest( '.sui-dialog' ).attr('id') ].hide();
            } else {
                window.ub_sui_notice( response.data.message, 'error' );
            }
        });
    });
    /**
     * Delete feed
     */
    $('.branda-admin-bar-delete').on( 'click', function() {
        var dialog_id = $(this).closest('.sui-dialog').attr( 'id' );
        var data = {
            action: 'branda_admin_bar_delete',
            _wpnonce: $(this).data('nonce'),
            id: $(this).data('id' )
        };
        $.post( ajaxurl, data, function( response ) {
            if ( response.success ) {
                $( '[data-id=' + data.id + ']', $branda_admin_bar_entries_parent ).detach();
                SUI.dialogs[ dialog_id ].hide();
                window.ub_sui_notice( response.data.message, 'success' );
            } else {
                window.ub_sui_notice( response.data.message, 'error' );
            }
        });
    });
    /**
     * Reset order
     */
    $('.branda-admin-bar-reset').on( 'click', function() {
        var data = {
            action: 'branda_admin_bar_order_reset',
            _wpnonce: $(this).data('nonce')
        };
        $.post( ajaxurl, data, function( response ) {
            if ( response.success ) {
                window.location.reload();
            } else {
                window.ub_sui_notice( response.data.message, 'error' );
            }
        });
    });
});
/**
 * Submenu: add
 */
function bind_branda_submenu_title( parent ) {
    jQuery('.branda-admin-bar-submenu-title input[type=text]', parent ).on( 'change paste cut keydown keyup keypress', function( event ) {
        jQuery('.sui-accordion-item-title', jQuery(this).closest('.sui-accordion-item')).html(
            '<i class="sui-icon-drag" aria-hidden="true"></i>' + jQuery(this).val()
        );
    });
}

function branda_admin_bar_submenu_add( args ) {
    var $button = jQuery('.branda-admin-bar-submenu-add', jQuery( '#' + Branda.admin_bar_dialog_edit ) );
    var target = jQuery('.sui-box-builder-body', $button.closest('.sui-form-field') );
    var template = wp.template( $button.data('template') );
    var submenu;
    jQuery('.sui-accordion', target ).append( template( args ) );
    SUI.brandaSideTabs();
    jQuery('.branda-admin-bar-no-submenu').hide();
    submenu = jQuery( '#branda-admin-bar-submenu-' + args.id );
    bind_branda_submenu_title( submenu );
    jQuery('.branda-admin-bar-submenu-delete', submenu ).on( 'click', function() {
        var parent = jQuery(this).closest( '.sui-box-builder-body' );
        jQuery(this).closest('.sui-accordion-item').detach();
        if ( 1 > jQuery( '.sui-accordion-item', parent ).length ) {
            jQuery( '.branda-admin-bar-no-submenu', parent ).show();
        }
    });
    jQuery( '.branda-sui-accordion-sortable' ).sortable({
        items: '.ui-sortable-handle'
    });
}

function branda_admin_bar_submenu_bind() {
    bind_branda_submenu_title( jQuery('body') );
    jQuery('.branda-admin-bar-submenu-add').on( 'click', function() {
        var args = {
            id: Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15),
            target: 'current',
            url: 'admin',
            url_admin: '',
            url_site: '',
            url_custom: ''
        };
        branda_admin_bar_submenu_add( args );
    });
    /**
     * Submenu: delete
     */
    jQuery('.branda-admin-bar-submenu-delete').on( 'click', function() {
        jQuery(this).closest('.sui-accordion-item').detach();
    });
    jQuery( '.branda-sui-accordion-sortable' ).sortable({
        items: '.ui-sortable-handle'
    });
}
/**
 * change custom element visibility
 */
function branda_admin_bar_redirect_bind() {
    var $dialog = jQuery( '#' + Branda.admin_bar_dialog_edit );
    jQuery( 'input[name="branda[url]"]', $dialog ).on( 'change', function() {
        var value = jQuery('.branda-general-url input:checked', $dialog ).val();
        if ( undefined === value ) {
            value = jQuery('.branda-general-url .active input', $dialog ).val();
        }
        switch( value ) {
            case 'custom':
                jQuery('.branda-general-custom', $dialog ).show();
                jQuery('.branda-admin-bar-url-options', $dialog ).show();
                break;
            case 'main':
            case 'current':
            case 'wp-admin':
                jQuery('.branda-general-custom', $dialog ).hide();
                jQuery('.branda-admin-bar-url-options', $dialog ).show();
                break;
            default:
                jQuery('.branda-general-custom', $dialog ).hide();
                jQuery('.branda-admin-bar-url-options', $dialog ).hide();
        }
    });
}
jQuery( window.document ).ready( function( $ ) {
    Branda.admin_bar_row_buttons_bind( $( '.branda-admin-page' ) );
});
