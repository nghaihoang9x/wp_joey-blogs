(function($) {
    'use strict';

    $(document).ready(function() {
        var $postsContainer = $('.blogs-page-lists');
        var $paginationContainer = $('.blogs-list-pagination');
        var $categoryButtons = $('#category-buttons');
        var $sortSelect = $('#sort');
        var $searchInput = $('.blogs-page-search-box__input');
        var $searchButton = $('.blogs-page-search-box__button');

        /**
         * Get the currently selected category slug.
         */
        function getSelectedCategory() {
            return $categoryButtons.find('.blogs-page-filter__btn.active').data('category') || '';
        }

        /**
         * Debounce helper to delay execution until after a pause.
         */
        var debounceTimer;

        function debounce(func, delay) {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(func, delay);
        }

        /**
         * Fetch filtered posts via AJAX.
         */
        function fetchPosts(paged) {
            paged = paged || 1;

            var data = {
                action: 'blogs_filter',
                nonce: blogs_ajax.nonce,
                category: getSelectedCategory(),
                sort: $sortSelect.val(),
                search: $searchInput.val(),
                paged: paged
            };

            $.ajax({
                url: blogs_ajax.ajax_url,
                type: 'POST',
                data: data,
                beforeSend: function() {
                    $postsContainer.addClass('loading');
                },
                success: function(response) {
                    if (response.success) {
                        $postsContainer.html(response.data.posts);
                        $paginationContainer.html(response.data.pagination);
                    }
                },
                complete: function() {
                    $postsContainer.removeClass('loading');
                }
            });
        }

        /**
         * Handle category button clicks.
         */
        $categoryButtons.on('click', '.blogs-page-filter__btn', function(e) {
            e.preventDefault();
            var $btn = $(this);

            // Toggle active class.
            $categoryButtons.find('.blogs-page-filter__btn').removeClass('active');
            $btn.addClass('active');

            fetchPosts(1);
        });

        /**
         * Handle sort select change.
         */
        $sortSelect.on('change', function() {
            fetchPosts(1);
        });

        /**
         * Handle pagination clicks via event delegation.
         */
        $paginationContainer.on('click', 'a', function(e) {
            e.preventDefault();
            var page = $(this).data('page');
            if (page) {
                fetchPosts(page);
            }
        });

        /**
         * Handle search button click.
         */
        $searchButton.on('click', function(e) {
            e.preventDefault();
            fetchPosts(1);
        });

        /**
         * Auto-search after the user stops typing for 500ms.
         */
        $searchInput.on('input', function() {
            debounce(function() {
                fetchPosts(1);
            }, 500);
        });
    });

})(jQuery);
