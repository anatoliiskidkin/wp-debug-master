jQuery(document).ready(function($) {
    var logContainer = $('#wp-debug-master-log-content-wrap');
    var searchInput = $('#wp-debug-master-search');
    var sortSelect = $('#wp-debug-master-sort-order');

    // Function to highlight search results in the log content
    function highlightSearchResults(keyword) {
        var logContent = logContainer.data('original-content');
        var logRecords = logContent.split('\n');
        var regex = new RegExp('(' + keyword + ')', 'gi');
        var highlightedContent = '';

        logRecords.forEach(function(logRecord, index) {
            var highlightedRecord = logRecord.replace(regex, '<span class="highlight">$1</span>');
            highlightedContent += '<pre class="log-record">' + highlightedRecord + '</pre>';

            // Add a gap between log records
            if (index < logRecords.length - 1) {
                highlightedContent += '<div class="log-record-gap"></div>';
            }
        });

        logContainer.html(highlightedContent);
    }


    // Function to load and display the debug log content
    function loadLogContent(searchKeyword = '', sortOption = 'newest') {
        logContainer.html('Loading...');
    
        $.ajax({
            url: wpDebugMaster.ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wp_debug_master_load_log_content',
                search: searchKeyword,
                sort: sortOption,
                nonce: wpDebugMaster.nonce // Include the nonce here
            },
            success: function(response) {
                logContainer.data('original-content', response.content);

                var logRecords = response.content.split('\n');
                var logHTML = '';

                logRecords.forEach(function(logRecord, index) {
                    logHTML += '<pre class="log-record">' + logRecord + '</pre>';

                    // Add a gap between log records
                    if (index < logRecords.length - 1) {
                        logHTML += '<div class="log-record-gap"></div>';
                    }
                });

                logContainer.html(logHTML);

                if (searchKeyword) {
                    highlightSearchResults(searchKeyword);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                logContainer.html('Error loading debug log: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    // Function to handle search input changes with debouncing
    var debounceTimeout = null;
    function handleSearchInput() {
        var keyword = searchInput.val().trim().toLowerCase();

        if (debounceTimeout !== null) {
            clearTimeout(debounceTimeout);
        }

        debounceTimeout = setTimeout(function() {
            loadLogContent(keyword, sortSelect.val());
        }, 300);
    }

    // Function to handle sort order changes
    function handleSortOrderChange() {
        var sortOrder = sortSelect.val();
        var keyword = searchInput.val().trim().toLowerCase();
        loadLogContent(keyword, sortOrder);
    }

    // Attach input event listener to the search input
    searchInput.on('input', handleSearchInput);

    // Attach change event listener to the sort order select
    sortSelect.on('change', handleSortOrderChange);

    // Load initial debug log content
    loadLogContent('', sortSelect.val());
});
