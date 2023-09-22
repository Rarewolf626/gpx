document.addEventListener('DOMContentLoaded', function () {

    document.querySelector('.tab-menu-items').addEventListener('click', function (e) {
        if(e.target.tagName !== 'A') return;
        e.preventDefault();
        let $li = e.target.closest('li');
        let tab = e.target.getAttribute('href');
        document.querySelectorAll('.tab-menu-items li, .tabbed .w-information').forEach(el => el.classList.remove("active"));
        $li.classList.add('active');
        document.querySelector(tab).classList.add("active");
        history.replaceState({}, "", tab);
    });
    if (document.getElementById("view-profile-sc")) {
        // this is the view profile page
        if (window.location.hash) {
            // there is a pre-selected tab
            let hash = window.location.hash.substring(1);
            let tab = document.getElementById(hash);
            if (tab) {
                // deselect all tabs
                document.querySelectorAll('.tab-menu-items li, .tabbed .w-information').forEach(el => el.classList.remove("active"));
                // activate the requested tab
                tab.classList.add('active');
                document.querySelector('.tab-menu-items li a[href="#' + hash + '"]').closest('li').classList.add('active');
            }
        }
    }

    const table = document.getElementById('holdweeks');
    if (!table) return;
    table.addEventListener('click', function (e) {
        const button = e.target.closest('button');
        if (!button) return;
        if (button.classList.contains('hold-edit-type-cancel')) {
            // cancel button was clicked
            e.preventDefault();
            e.stopPropagation();
            const container = button.closest('.profile-week-type');
            container.classList.remove('open');
            container.querySelector('select').value = container.dataset.value;
            return;
        }
        if (!button.classList.contains('hold-edit-type')) return;
        // edit button was clicked
        e.preventDefault();
        e.stopPropagation();
        const container = button.closest('.profile-week-type');
        container.classList.add('open');
    });
    table.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form.classList.contains('hold-edit-type-form')) return;
        e.preventDefault();
        e.stopPropagation();
        axios.post(form.action, new FormData(form))
            .then(function (response) {
                if (!response.data.success) {
                    const message = response.data.message || 'Could not change week type.';
                    window.alertModal.alert(message, false);

                    return;
                }
                const container = form.closest('.profile-week-type');
                container.querySelector('.profile-week-type-value').textContent = response.data.label;
                container.classList.remove('open');
                form.querySelector('select').value = response.data.value;
                container.dataset.value = response.data.value;
                if (response.data.url) {
                    container.closest('tr').querySelector('.hold-confirm').href = response.data.url;
                }
            })
            .catch(function (error) {
                window.alertModal.alert('Could not change week type.', false);
            });
    });
});
