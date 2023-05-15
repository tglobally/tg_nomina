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

    $('#nom_nomina').on('click', 'thead:first-child, tbody', function () {

        if (timer) {
            clearTimeout(timer);
        }

        timer = setTimeout(() => {
            var rows_selected = table_nom_nomina.rows('.selected').data();

            datatables.forEach((tabla) => {
                tabla.clear();
            });

            nominas_seleccionadas = [];

            $('.tablas_nominas').empty();

            rows_selected.each(function (value, row, data) {
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
                var dataform = new FormData();

                Loader.load('.tablas_nominas', url, dataform,
                    function (response) {
                        var registros = response.registros;
                        table.rows.add(registros).draw();
                    }, function (XMLHttpRequest, textStatus, errorThrown) {
                        let response = XMLHttpRequest.responseText;
                        console.log(response)
                    });

            });

            datatables.forEach((tabla) => {
                tabla.columns.adjust().draw();
            });

            $('#nominas_genera_xmls').val(nominas_seleccionadas);
            $('#nominas_timbra_xmls').val(nominas_seleccionadas);
            $('#nominas_exportar_documentos').val(nominas_seleccionadas);
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

        Loader.post('.login-body .container', url, dataform,
            function (response) {
                let status = 'info';
                let body = '';

                if (response.status == "Error") {
                    status = 'danger';
                    let title = response.title;
                    let code = response.code;
                    let message = response.message;

                    body = `<h4 class="alert-heading"><strong>${title}</strong></h4><hr>`;
                    body += `<strong>Error ${code} :</strong> ${message}`;
                } else if (response.status == "Success") {
                    status = 'success';
                    body = response.message;

                    if (accion !== "exportar_documentos"){
                        $(".tablas_nominas").empty();

                        nominas_seleccionadas.forEach(function (value, row, data) {

                            var contenedor = `<div class="col-md-12">
                                            <div class="tabla_titulo"><span class="text-header">Nomina - ${value}</span></div>
                                            <table id="nomina_${value}" class="datatables table table-striped "></table>
                                         </div>`;

                            $('.tablas_nominas').append(contenedor);

                            var table = inicializa_datatable(`#nomina_${value}`, [
                                {data: 'doc_tipo_documento_codigo', title: "Tipo Documento"},
                                {data: 'doc_documento_nombre', title: "Documento"},
                            ]);

                            let url = get_url("nom_nomina_documento", "get_documentos_nomina", {nom_nomina_id: value});
                            var dataform = new FormData();

                            Loader.load('.tablas_nominas', url, dataform,
                                function (response) {
                                    var registros = response.registros;
                                    table.rows.add(registros).draw();
                                }, function (XMLHttpRequest, textStatus, errorThrown) {
                                    let response = XMLHttpRequest.responseText;
                                    console.log(response)
                                });

                        });
                    }
                }

                let alert = `<div class="alert alert-${status} alert-fixed">${body}</div>`
                $('.login-body').append(alert);
                setTimeout(function () {
                    $('.alert').alert('close');
                }, 10000);
            }, function (XMLHttpRequest, textStatus, errorThrown) {
                let response = XMLHttpRequest.responseText;
                console.log(response)
            });
    });

});