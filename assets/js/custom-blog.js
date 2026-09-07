/**
 * Custom JS for all single post pages.
 *
 * Loaded on single posts (and on the plugin's custom single template).
 * Add your single-page behaviour inside the ready handler below.
 *
 * @package Joey_Blogs
 */
(function ($) {
    'use strict';

    $(document).ready(function () {
        // Localized data is available as `joeyBlogsSingle` (ajax_url, nonce, post_id).

        // Your custom code here.
    });
})(jQuery);

document.addEventListener('DOMContentLoaded', function () {
    var faqSections = document.querySelectorAll('.schema-faq .schema-faq-section');

    faqSections.forEach(function (section) {
        var question = section.querySelector('.schema-faq-question');

        if (question) {
            question.addEventListener('click', function () {
                // Nếu muốn chỉ mở 1 câu tại 1 thời điểm, bỏ comment đoạn dưới
                // faqSections.forEach(function (s) {
                //     if (s !== section) s.classList.remove('active');
                // });

                section.classList.toggle('active');
            });
        }
    });
});


document.addEventListener('DOMContentLoaded', function () {
    var tables = document.querySelectorAll('.requirement-table');

    tables.forEach(function (table) {
        // Lấy danh sách tiêu đề từ thead
        var headers = table.querySelectorAll('thead th');
        var headerTexts = Array.prototype.map.call(headers, function (th) {
            return th.textContent.trim();
        });

        // Duyệt qua từng dòng trong tbody
        var rows = table.querySelectorAll('tbody tr');
        rows.forEach(function (row) {
            var cells = row.querySelectorAll('td');
            cells.forEach(function (td, index) {
                if (headerTexts[index]) {
                    td.setAttribute('data-label', headerTexts[index]);
                }
            });
        });
    });
});


document.addEventListener('DOMContentLoaded', function () {
    // Áp dụng cho mọi bảng trong Gutenberg table block
    var tables = document.querySelectorAll('.wp-block-table table');

    tables.forEach(function (table) {
        if (table.dataset.mobileCardsInit) return; // tránh chạy trùng
        table.dataset.mobileCardsInit = 'true';

        var grid = buildGrid(table);
        if (!grid.length) return;

        var headers = grid[0].map(stripTags);
        var cardsWrap = document.createElement('div');
        cardsWrap.className = 'table-mobile-cards';

        for (var r = 1; r < grid.length; r++) {
            var card = document.createElement('div');
            card.className = 'table-mobile-card';

            for (var c = 0; c < grid[r].length; c++) {
                if (!headers[c]) continue;
                var field = document.createElement('div');
                field.className = 'table-mobile-field';

                var label = document.createElement('span');
                label.className = 'table-mobile-label';
                label.textContent = headers[c];

                var value = document.createElement('span');
                value.className = 'table-mobile-value';
                value.innerHTML = grid[r][c];

                field.appendChild(label);
                field.appendChild(value);
                card.appendChild(field);
            }
            cardsWrap.appendChild(card);
        }

        table.parentNode.insertBefore(cardsWrap, table.nextSibling);
    });

    // Giải nén table (kể cả rowspan/colspan) thành ma trận 2 chiều
    function buildGrid(table) {
        var rows = Array.prototype.slice.call(table.querySelectorAll('tr'));
        var grid = [];
        var rowSpans = {};

        rows.forEach(function (row) {
            var cells = Array.prototype.slice.call(row.children);
            var gridRow = [];
            var colIndex = 0;

            function fillCarry() {
                while (rowSpans[colIndex] && rowSpans[colIndex].count > 0) {
                    gridRow[colIndex] = rowSpans[colIndex].content;
                    rowSpans[colIndex].count--;
                    colIndex++;
                }
            }

            fillCarry();
            cells.forEach(function (cell) {
                fillCarry();
                var colspan = parseInt(cell.getAttribute('colspan') || '1', 10);
                var rowspan = parseInt(cell.getAttribute('rowspan') || '1', 10);
                var content = cell.innerHTML.trim();

                for (var c = 0; c < colspan; c++) {
                    gridRow[colIndex] = content;
                    if (rowspan > 1) {
                        rowSpans[colIndex] = { count: rowspan - 1, content: content };
                    }
                    colIndex++;
                }
                fillCarry();
            });

            grid.push(gridRow);
        });

        return grid;
    }

    function stripTags(html) {
        var tmp = document.createElement('div');
        tmp.innerHTML = html;
        return tmp.textContent.trim();
    }
});