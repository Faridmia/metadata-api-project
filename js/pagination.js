document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        if (e.target && e.target.matches('#student-info-table-container .pagination a')) {
            e.preventDefault();
            var url = e.target.getAttribute('href');

            fetch(url)
                .then(response => response.text())
                .then(html => {
                    var container = document.getElementById('student-info-table-container');
                    container.innerHTML = new DOMParser().parseFromString(html, 'text/html').getElementById('student-info-table-container').innerHTML;
                })
                .catch(error => console.error('Error:', error));
        }
    });
});