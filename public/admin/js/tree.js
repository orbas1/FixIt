(function($) {
    'use strict';
    var tree_custom = {
        init: function() {
            $('#treeBasic').jstree({
                'core': {
                    'themes': {
                        'responsive': false,
                    },
                },
                'types': {
                    'default': {
                        'icon': 'ti-gallery'
                    },
                    'file': {
                        'icon': 'ti-file'
                    }
                },
                "search": {
                    "case_insensitive": true,
                    "show_only_matches": true
                },
                'plugins': ['types', 'search']
            });

            $('#search').keyup(function() {
                $('#treeBasic').jstree('search', $(this).val());
            });

            $('#treeBasic').on('click', '.edit-icon', function(e) {
                var id = $(this).attr('value');
                window.location.href = '/backend/category/' + id + '/edit';
            });

            $('#treeBasic').on('click', '.edit-child', function(e) {
                var id = $(this).attr('value');
                window.location.href = '/backend/category/' + id + '/edit';
            });
        }
    };
    
    $(document).ready(function() {
        tree_custom.init();
        setTimeout(function() {
            $('.jstree-loader').fadeOut('fast');
            $('#treeBasic').show();
        });
    });
})(jQuery); 
