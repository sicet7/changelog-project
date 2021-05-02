var targets = [];
function bootSearch(input, targetsSelector, valueAttribute, hiddenDisplay, shownDisplay) {
    input.addEventListener('keyup', function(e){
        var i, current;
        if (e.keyCode === 13 && e.target.value.length >= 1) {
            console.log(targetsSelector + '.shown')
            var element = document.querySelectorAll(targetsSelector + '.shown')[0];
            window.location = element.getAttribute('href');
            return;
        } else {
            if (targets.length === 0) {
                var t = document.querySelectorAll(targetsSelector);
                var tk = Object.keys(t);
                for(i = 0; i < tk.length; i++) {
                    targets.push(t[tk[i]]);
                }
            }
            for(i = 0; i < targets.length; i++) {
                current = targets[i];
                if (!current.hasAttribute(valueAttribute) || !current.getAttribute(valueAttribute).toLowerCase().includes(e.target.value.toLowerCase())) {
                    current.style.display = hiddenDisplay;
                    toggleClass(current, 'shown', false);
                } else {
                    current.style.display = shownDisplay;
                    toggleClass(current, 'shown', true);
                }
            }
        }
    });
}

function toggleClass(e, className, state) {
    if (state && !e.classList.contains(className)) {
        e.classList.add(className);
    }
    if (!state && e.classList.contains(className)) {
        e.classList.remove(className);
    }
}

function saveEdit(id, newLocation)
{
    $.ajax({
        url: "/changelogs/save",
        method: "POST",
        data: {
            id: id,
            name: document.getElementById('edit-name').value,
            description: document.getElementById('edit-description').value
        },
        success: function() {
            window.location = newLocation;
            return;
        },
        error: function(res) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: res.responseText,
            })
        }
    });
}

function deleteChangelog(deleteLocation, name)
{
    Swal.fire({
        title: 'Are you sure?',
        text: "You are about to delete the entire Changelog with the name \"" + name + "\".",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location = deleteLocation;
        }
    })
}

function gotoEntry(entry) {
    window.location = '/changelogs/entry/' + entry;
}

function selectFilter(element)
{
    var dropdown = document.getElementById('filterDropdown'),
        activeElements = dropdown.getElementsByClassName('active'),
        activeElementKeys = Object.keys(activeElements),
        activeE,
        i;
    for (i = 0; i < activeElementKeys.length; i++) {
        activeE = activeElements[activeElementKeys[i]];
        if (activeE.classList.contains('active')) {
            activeE.classList.remove('active');
        }
    }
    if (!element.classList.contains('active')) {
        element.classList.add('active');
    }
    document.getElementById('filterNavbarDropdown').innerText = element.innerText;
    document.getElementById('valueInput').value = '';
    if (element.hasAttribute('data-value') && element.getAttribute('data-value') === 'createdAt') {
        $( "#valueInput" ).flatpickr({
            mode: "range",
            dateFormat: "d-m-Y",
            weekNumbers: true,
            maxDate: "today"
        });
    } else {
        if (document.getElementById('valueInput').classList.contains('flatpickr-input')) {
            flatpickr("#valueInput", {}).destroy();
        }
    }
}

function parse_query_string(query) {
    var vars = query.split("&");
    var query_string = {};
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split("=");
        var key = decodeURIComponent(pair[0]);
        var value = decodeURIComponent(pair[1]);
        // If first entry with this name
        if (typeof query_string[key] === "undefined") {
            query_string[key] = decodeURIComponent(value);
            // If second entry with this name
        } else if (typeof query_string[key] === "string") {
            var arr = [query_string[key], decodeURIComponent(value)];
            query_string[key] = arr;
            // If third or later entry with this name
        } else {
            query_string[key].push(decodeURIComponent(value));
        }
    }
    return query_string;
}

function getCurrentQuery()
{
    if (window.location.search.trim() === '') {
        return {};
    }
    return parse_query_string(window.location.search.substring(1));
}

function setQuery(query, removes)
{
    if (typeof query !== 'object') {
        return;
    }
    var current = getCurrentQuery(),
        queryKeys = Object.keys(query),
        i, key, value, queryString = '',
        href = window.location.href.replace(window.location.search, '');

    for (i = 0; i < queryKeys.length; i++) {
        key = queryKeys[i];
        value = query[key];
        current[key] = value;
    }

    var currentKeys = Object.keys(current);

    for (i = 0; i < currentKeys.length; i++) {
        key = currentKeys[i];
        value = current[key];
        if (Array.isArray(removes) && removes.indexOf(key) !== -1) {
            continue;
        }
        if (queryString === '') {
            queryString += '?' + key + '=' + value;
        } else {
            queryString += '&' + key + '=' + value;
        }
    }

    var location = href + queryString;

    $.ajax({
        url: location,
        method: "GET",
        success: function(data) {
            document.getElementById('tableContent').innerHTML = data;
            window.history.pushState({},"", location);
        },
        error: function(res) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: res.responseText,
            })
        }
    });
}

var enterSearch = function(e) {
    if (e.keyCode === 13) {
        e.preventDefault();
        triggerSearch();
    }
}

function triggerSearch()
{
    var input = document.getElementById('valueInput'),
        filter = document.querySelectorAll('#filterDropdown .dropdown-menu .dropdown-item.active[data-name="filter"][data-value]')[0];

    if (input.value.trim().length < 2) {
        return;
    }

    var inputValue = btoa(input.value),
        filterValue = filter.getAttribute('data-value');

    setQuery({
        'filter': filterValue,
        'value': inputValue,
    }, ['page']);
}

function triggerSort(field, dir)
{
    setQuery({
        "sort": field,
        "dir": dir,
    })
}

function resetSearchAndFilters()
{
    setQuery({}, [
        'page',
        'size',
        'filter',
        'value',
        'sort',
        'dir',
    ]);
}