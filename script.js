document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const results = document.getElementById("results");
    const selectBox = document.getElementById("selectBox");
    const options = selectBox.querySelectorAll("option");

    // Crear una matriz de nombres de usuarios que coincidan con las opciones
    const names = Array.from(options).map(option => option.textContent);

    // ...

    searchInput.addEventListener("input", function () {
        const searchTerm = searchInput.value.toLowerCase();
        const filteredNames = names.filter(name => name.toLowerCase().includes(searchTerm));

        // Mostrar resultados coincidentes
        renderResults(filteredNames);
    });

    function renderResults(resultsArray) {
        results.innerHTML = "";
        resultsArray.forEach(name => {
            const li = document.createElement("li");
            li.textContent = name;
            li.addEventListener("click", function () {
                // Rellenar el campo de búsqueda con la opción seleccionada
                searchInput.value = name;
                // Limpiar los resultados
                results.innerHTML = "";

                // Actualizar el selectBox cuando se selecciona una opción
                options.forEach(option => {
                    if (option.textContent === name) {
                        option.selected = true;
                    } else {
                        option.selected = false;
                    }
                });
            });
            results.appendChild(li);
        });
    }

    // ...

    // Cerrar la lista de resultados al hacer clic en cualquier parte de la página
    document.addEventListener("click", function (event) {
        if (event.target !== searchInput && event.target !== results) {
            results.innerHTML = "";
        }
    });
});
