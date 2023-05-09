$(document).ready(function () {
    var tables = $.fn.dataTable.tables(true);
    var table_nom_nomina = $(tables).DataTable().search('nom_nomina');
    table_nom_nomina.search('').columns().search('').draw();

    var nominas_seleccionadas = [];
    var datatables = [];
    var elementos_seleccionados = new Map();

    let inicializa_datatable = (identificador, columns, entidad, clase) => {

        return $(identificador).DataTable({
            searching: false,
            paging: false,
            ordering: false,
            info: false,
            columns: columns,
            columnDefs: [
                {
                    targets: 1,
                    render: function (data, type, row, meta) {
                        return `<a href="${row.doc_documento_ruta_relativa}" style="color: #198754;text-decoration: underline;" 
                                   target="_blank">${row.doc_documento_nombre}</a>`;
                    }
                }
            ],
            order: [[0, 'asc']],
            displayLength: 'All',
        });
    };


    let timer = null;

    $('#nom_nomina').on('click', 'tbody td:first-child', function (event) {

        if (timer) {
            clearTimeout(timer);
        }

        timer = setTimeout(() => {
            var selectedData = table_nom_nomina.rows('.selected').data();

            datatables.forEach((tabla) => {
                tabla.clear();
            });

            nominas_seleccionadas = [];

            $('.tablas_nominas').empty();

            selectedData.each(function (value, row, data) {
                nominas_seleccionadas.push(value.nom_nomina_id);

                var contenedor = `<div class="col-md-12">
                                            <div class="tabla_titulo"><span class="text-header">Nomina - ${value.em_empleado_nombre_completo}</span></div>
                                            <table id="nomina_${value.nom_nomina_id}" class="datatables table table-striped "></table>
                                         </div>`;

                $('.tablas_nominas').append(contenedor);

                var table = inicializa_datatable(`#nomina_${value.nom_nomina_id}`, [
                    {data: 'doc_tipo_documento_codigo', title: "Tipo Documento"},
                    {data: 'doc_documento_nombre', title: "Documento"},
                ]);

                let url = get_url("nom_nomina_documento", "get_documentos_nomina", {nom_nomina_id: value.nom_nomina_id});

                get_data(url, function (rows) {
                    var registros = rows.registros;
                    table.rows.add(registros).draw();
                });
            });

            datatables.forEach((tabla) => {
                tabla.columns.adjust().draw();
            });

            $('#nominas_genera_xmls').val(nominas_seleccionadas);
            $('#nominas_timbra_xmls').val(nominas_seleccionadas);

        }, 1000);
    });

   $('.form_nominas').on('submit', function (e) {
        e.preventDefault();

        if (nominas_seleccionadas.length === 0) {
            alert("Seleccione una nómina");
            return;
        }

        let accion = $(this).data("accion");
        let url = get_url("tg_manifiesto", accion, {});
        var dataform = new FormData();
        dataform.append('nominas', nominas_seleccionadas);

        $.ajax({
            async: true,
            type: 'POST',
            url: url,
            data: dataform,
            contentType: false,
            processData: false,
            success: function (response) {

                console.log(response)
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                console.log(XMLHttpRequest)
            }
        });

    });

});