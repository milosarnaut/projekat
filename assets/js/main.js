/*$("#search_index").on("keyup", function () {
    var str = $("#search_index").val().trim();

    let indeksi = document.querySelectorAll('#index_number > option');

    for (let indeks of indeksi) {
        let textIndeksa = indeks.innerText.trim();

        indeks.style.display = 'block';

        if (textIndeksa === 'Odaberite broj indeksa studenta') {
            continue;
        }

        if (textIndeksa.indexOf(str) == -1) {
            indeks.style.display = 'none';
        }

        indeks.removeAttribute('selected');
    }

    let selektovaniIndeks = 0;
    let trenutniIndeks = 0;

    for (let indeks of indeksi) {
        let textIndeksa = indeks.innerText.trim();

        if (textIndeksa === 'Odaberite broj indeksa studenta') {
            continue;
        }

        if (indeks.style.display == 'block') {
            indeks.setAttribute('selected', 'selected');
            selektovaniIndeks = trenutniIndeks;
            break;
        }

        trenutniIndeks++;
    }

    if (selektovaniIndeks === 0) {
        document.querySelector('#index_number > option:nth-child(1)').style.display = 'block';
        document.querySelector('#index_number > option:nth-child(1)').setAttribute('selected', 'selected');
    }
});*/
