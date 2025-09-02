document.addEventListener("DOMContentLoaded", function () {
    document.getElementById('pdfForm').addEventListener('submit', function (event) {
        event.preventDefault(); // Evita el envío del formulario tradicional

        // Captura el contenido del div con id impreso
        var element = document.getElementById('impreso');

        // Obtener el nombre del archivo del campo oculto
        var archivo = document.getElementById('pdfFilename').value.trim(); // Elimina espacios en blanco

        if (!archivo) {
            filename = 'reporte_actividades'; // Nombre por defecto si no se proporciona
        }

        // Configura las opciones de html2pdf
        var options = {
            margin: 1,
            filename: archivo + '.pdf', // Nombre del archivo para la descarga
            image: {
                type: 'pdf',
                quality: 0.98
            },
            html2canvas: {
                scale: 2
            },
            jsPDF: {
                unit: 'in',
                format: 'a4',
                orientation: 'landscape'
            }
        };

        // Genera y guarda el PDF
        html2pdf().set(options).from(element).save();
    });
});
