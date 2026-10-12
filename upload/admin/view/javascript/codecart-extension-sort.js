/* CodeCart PRO extension list sorting. Keeps nested instances with their parent. */
(function ($) {
    'use strict';

    var sortByType = {};

    function currentType() {
        return $('select[name="type"]').val() || 'extensions';
    }

    function sortTable() {
        var table = $('#extension table.ccp-extension-table');
        var sort = sortByType[currentType()];

        if (!table.length || !sort) {
            return;
        }

        var body = table.find('tbody').first();
        var groups = [];
        var current = null;

        body.children('tr').each(function () {
            if ($(this).hasClass('ccp-extension-main')) {
                current = { head: this, rows: [this], original: groups.length };
                groups.push(current);
            } else if (current && $(this).hasClass('ccp-extension-child')) {
                current.rows.push(this);
            }
        });

        var locale = $('html').attr('lang') || undefined;
        groups.sort(function (a, b) {
            var left = a.head.getAttribute('data-sort-' + sort.key) || '';
            var right = b.head.getAttribute('data-sort-' + sort.key) || '';
            var result = 0;

            // Unknown installation dates always stay after known dates.
            if (sort.key === 'date' && (!left || !right)) {
                result = left ? -1 : (right ? 1 : 0);
                if (result) {
                    return result;
                }
            } else if (sort.key === 'installed') {
                result = Number(left) - Number(right);
                result *= sort.direction;
            } else {
                result = left.toLocaleLowerCase().localeCompare(right.toLocaleLowerCase(), locale, { sensitivity: 'base', numeric: true });
                result *= sort.direction;
            }

            return result || a.original - b.original;
        });

        var fragment = document.createDocumentFragment();
        groups.forEach(function (group) {
            group.rows.forEach(function (row) { fragment.appendChild(row); });
        });
        body.get(0).appendChild(fragment);

        table.find('thead [data-ccp-sort]').each(function () {
            var button = $(this);
            var active = button.attr('data-ccp-sort') === sort.key;
            var ascending = active && sort.direction === 1;
            button.find('i').attr('class', 'fa ' + (active ? (ascending ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort'));
            button.attr('aria-pressed', active ? 'true' : 'false');
            button.closest('td').attr('aria-sort', active ? (ascending ? 'ascending' : 'descending') : 'none');
        });
    }

    $('#extension').on('click', '.ccp-extension-sort', function () {
        var type = currentType();
        var key = $(this).attr('data-ccp-sort');
        var prior = sortByType[type];
        var firstDirection = (key === 'date' || key === 'installed') ? -1 : 1;

        sortByType[type] = {
            key: key,
            direction: prior && prior.key === key ? -prior.direction : firstDirection
        };
        sortTable();
    });

    window.CodeCartExtensionSort = { apply: sortTable };
})(jQuery);
