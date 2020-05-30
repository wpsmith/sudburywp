/* global jQuery */
(function($){
    var media       = wp.media,
        Attachment  = media.model.Attachment,
        Attachments = media.model.Attachments,
        l10n        = media.view.l10n;

    media.model.Query.prototype.initialize = function( models, options ) {
        var allowed;

        options = options || {};
        Attachments.prototype.initialize.apply( this, arguments );

        this.args     = options.args;
        this._hasMore = true;
        this.created  = new Date();

        this.filters.order = function( attachment ) {
            var orderby = this.props.get('orderby'),
                order = this.props.get('order');

            if ( ! this.comparator )
                return true;

            // We want any items that can be placed before the last
            // item in the set. If we add any items after the last
            // item, then we can't guarantee the set is complete.
            if ( this.length ) {
                return 1 !== this.comparator( attachment, this.last(), { ties: true });

                // Handle the case where there are no items yet and
                // we're sorting for recent items. In that case, we want
                // changes that occurred after we created the query.
            } else if ( 'DESC' === order && ( 'date' === orderby || 'modified' === orderby ) ) {
                return attachment.get( orderby ) >= this.created;

                // If we're sorting by menu order and we have no items,
                // accept any items that have the default menu order (0).
            } else if ( 'ASC' === order && 'menuOrder' === orderby ) {
                return attachment.get( orderby ) === 0;
            }

            // Otherwise, we don't want any items yet.
            return false;
        };

        // Observe the central `wp.Uploader.queue` collection to watch for
        // new matches for the query.
        //
        // Only observe when a limited number of query args are set. There
        // are no filters for other properties, so observing will result in
        // false positives in those queries.
        allowed = [ 's', 'order', 'orderby', 'posts_per_page', 'post_mime_type', 'post_parent', 'global_library' ];
        if ( wp.Uploader && _( this.args ).chain().keys().difference( allowed ).isEmpty().value() )
            this.observe( wp.Uploader.queue );
    }

    media.view.MediaFrame.Select.prototype.bindHandlers = function() {
        this.on( 'router:create:browse', this.createRouter, this );
        this.on( 'router:render:browse', this.browseRouter, this );
        this.on( 'content:create:browse', this.browseContent, this );
        this.on( 'router:create:browse_global', this.createRouter, this );
        this.on( 'router:render:browse_global', this.browseRouter, this );
        this.on( 'content:create:browse_global', this.browseGlobalContent, this );
        this.on( 'content:render:upload', this.uploadContent, this );
        this.on( 'toolbar:create:select', this.createSelectToolbar, this );
    }

    media.view.MediaFrame.Select.prototype.browseRouter = function( view ) {
        view.set({
            upload: {
                text:     l10n.uploadFilesTitle,
                priority: 20
            },
            browse: {
                text:     l10n.mediaLibraryTitle,
                priority: 40
            },
            browse_global: {
                text:     "Global Media",
                priority: 60
            }
        });
    }

    media.view.MediaFrame.Select.prototype.browseContent = function( content ) {
        var state = this.state();

        this.$el.removeClass('hide-toolbar');

        // Browse our library of attachments.
        content.view = new wp.media.view.AttachmentsBrowser({
            controller: this,
            collection: state.get('library'),
            selection:  state.get('selection'),
            model:      state,
            sortable:   state.get('sortable'),
            search:     state.get('searchable'),
            filters:    state.get('filterable'),
            display:    state.get('displaySettings'),
            dragInfo:   state.get('dragInfo'),

            AttachmentView: state.get('AttachmentView')
        });
    }

    media.view.MediaFrame.Select.prototype.browseGlobalContent = function( content ) {
        var state = this.state();

        this.$el.removeClass('hide-toolbar');

        // Browse our library of attachments.
        content.view = new wp.media.view.AttachmentsBrowser({
            controller: this,
            collection: state.get('global_library'),
            selection:  state.get('selection'),
            model:      state,
            sortable:   state.get('sortable'),
            search:     state.get('searchable'),
            filters:    state.get('filterable'),
            display:    state.get('displaySettings'),
            dragInfo:   state.get('dragInfo'),

            AttachmentView: state.get('AttachmentView')
        });
    }

    media.view.MediaFrame.Post.prototype.createStates = function() {
        var options = this.options;

        // Add the default states.
        this.states.add([
            // Main states.
            new media.controller.Library({
                id:         'insert',
                title:      l10n.insertMediaTitle,
                priority:   20,
                toolbar:    'main-insert',
                filterable: 'all',
                library:    media.query( options.library ),
                global_library:   media.query( {orderby: "date", query: true,global_library:101} ),
                multiple:   options.multiple ? 'reset' : false,
                editable:   true,
                metadata: {},

                // If the user isn't allowed to edit fields,
                // can they still edit it locally?
                allowLocalEdits: true,

                // Show the attachment display settings.
                displaySettings: true,
                // Update user settings when users adjust the
                // attachment display settings.
                displayUserSettings: true
            }),

            new media.controller.Library({
                id:         'gallery',
                title:      l10n.createGalleryTitle,
                priority:   40,
                toolbar:    'main-gallery',
                filterable: 'uploaded',
                multiple:   'add',
                editable:   false,

                library:  media.query( _.defaults({
                    type: 'image'
                }, options.library ) )
            }),

            // Embed states.
            new media.controller.Embed({}),

            // Gallery states.
            new media.controller.GalleryEdit({
                library: options.selection,
                editing: options.editing,
                menu:    'gallery'
            }),

            new media.controller.GalleryAdd()
        ]);


        if ( media.view.settings.post.featuredImageId ) {
            this.states.add( new media.controller.FeaturedImage() );
        }
    }
})( jQuery );