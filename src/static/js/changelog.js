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
    })
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