document.addEventListener('DOMContentLoaded', function () {
    // Ketika direktori diklik
    document.getElementById('dir-tree').addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('dir')) {
            const dir = e.target.dataset.dir;
            const subDirUl = e.target.closest('li');

            // Jika sub-direktori sudah dimuat, tidak perlu memuat ulang
            if (subDirUl.children.length === 0) {
                loadDirContent(dir, subDirUl);
            } else {
                // Toggle visibility subdirektori
                //subDirUl.style.display = subDirUl.style.display === 'none' ? 'block' : 'none';
            }
        }
    });

    // Fungsi untuk memuat konten direktori
    function loadDirContent(dir, subDirUl) {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'load-dir.php?dir=' + encodeURIComponent(dir), true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                const dirs = JSON.parse(xhr.responseText);
                displayDirContent(dirs, subDirUl);
            }
        };
        xhr.send();
    }

    // Fungsi untuk menampilkan isi direktori di dalam <ul> sub-direktori
    function displayDirContent(dirs, subDirUl) {
        let dirUl = document.createElement('ul');
        dirs.forEach(function (dir) {
            const dirLi = document.createElement('li');
            if (dir.type === 'dir') {
                const dirSpan = document.createElement('span');
                dirSpan.textContent = dir.name;
                dirSpan.dataset.dir = dir.path;
                dirSpan.classList.add('dir');

                // Buat <ul> untuk sub-direktori
                const subUl = document.createElement('ul');
                subUl.style.display = 'none'; // Mulai dengan menyembunyikan subdirektori
                dirLi.appendChild(dirSpan);
                dirUl.appendChild(dirLi);

            } else if (dir.type === 'file') {
                const fileLi = document.createElement('li');
                const fileSpan = document.createElement('span');
                fileSpan.textContent = dir.name;
                fileSpan.style.cursor = 'pointer';
                fileSpan.dataset.file = dir.path;
                fileSpan.classList.add('file');

                // Menambahkan event untuk membuka file
                fileSpan.addEventListener('click', function () {
                    openFile(dir.path);
                });

                fileLi.appendChild(fileSpan);
                dirUl.appendChild(fileLi);
            }
        });
        subDirUl.appendChild(dirUl);

    }

    // Fungsi untuk membuka file
    function openFile(file) {
        const fileDiv = document.getElementById('file-content');
        fileDiv.innerHTML = ''; // Bersihkan konten sebelumnya

        const extension = file.split('.').pop().toLowerCase();

        if (extension === 'txt') {
            const textarea = document.createElement('textarea');
            fetch(file)
                .then(response => response.text())
                .then(text => {
                    textarea.value = text;
                    fileDiv.appendChild(textarea);
                });
        } else if (['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
            const img = document.createElement('img');
            img.src = file;
            img.style.maxWidth = '100%';
            img.style.maxHeight = '400px';
            fileDiv.appendChild(img);
        } else {
            fileDiv.textContent = 'Tidak bisa membuka file ini.';
        }
    }
});
