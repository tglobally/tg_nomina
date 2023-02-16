$(document).ready(function () {
    var tables = $.fn.dataTable.tables(true);
    var table_nom_nomina = $(tables).DataTable().search('nom_nomina');

    var nominas_seleccionadas = [];

    let inicializa_datatable = (identificador, columns, entidad) => {

        return $(identificador).DataTable({
            searching: false,
            paging: false,
            ordering: false,
            info: false,
            columns: columns,
            columnDefs: [
                {targets: 0, visible: false},
                {
                    targets: 3,
                    render: function (data, type, row, meta) {
                        var input = document.createElement('input');
                        input.setAttribute("type", "text");
                        input.setAttribute("class", "input-accion");
                        input.setAttribute("name", "importe_gravado");
                        input.setAttribute("value", data[entidad + "_importe_gravado"]);

                        return input.outerHTML;
                    }
                },
                {
                    targets: 4,
                    render: function (data, type, row, meta) {
                        var input = document.createElement('input');
                        input.setAttribute("type", "text");
                        input.setAttribute("class", "input-accion");
                        input.setAttribute("name", "importe_exento");
                        input.setAttribute("value", data[entidad + "_importe_exento"]);

                        return input.outerHTML;
                    }
                },
                {
                    targets: 5,
                    render: function (data, type, row, meta) {
                        return `<a role='button' title='Elimina' data-id='${data[entidad + "_id"]}' 
                               class='btn btn-danger btn-sm delete-btn' style='margin-left: 2px; margin-bottom: 2px; '>Elimina</a>`;
                    }
                }
            ],
            order: [[0, 'asc']],
            displayLength: 'All',
            drawCallback: function (settings) {
                var api = this.api();
                var rows = api.rows({page: 'current'}).nodes();
                var last = null;

                api
                    .column(0, {page: 'current'}).data()
                    .each(function (group, i) {
                        if (last !== group.nom_nomina_id) {
                            var salida = `<b> NOMINA: </b> ${group.nom_nomina_id} - ${group.em_empleado_descripcion}`;
                            $(rows)
                                .eq(i)
                                .before('<tr class="group"><td colspan="5">' + salida + '</td></tr>');
                            last = group.nom_nomina_id;
                        }
                    });
            },
        });
    };

    var table_percepciones = inicializa_datatable("#nominas_percepciones", [
        {data: null, title: "Nomina"},
        {data: 'nom_percepcion_codigo', title: "Código"},
        {data: 'nom_percepcion_descripcion', title: "Descripción"},
        {data: null, title: "Importe Gravado"},
        {data: null, title: "Importe Exento"},
        {data: null, title: "Acciones"},
    ], "nom_par_percepcion");

    var table_deducciones = inicializa_datatable("#nominas_deducciones", [
        {data: null, title: "Nomina"},
        {data: 'nom_deduccion_codigo', title: "Código"},
        {data: 'nom_deduccion_descripcion', title: "Descripción"},
        {data: null, title: "Importe Gravado"},
        {data: null, title: "Importe Exento"},
        {data: null, title: "Acciones"},
    ], "nom_par_deduccion");

    var table_otros_pagos = inicializa_datatable("#nominas_otros_pagos", [
        {data: null, title: "Nomina"},
        {data: 'nom_otro_pago_codigo', title: "Código"},
        {data: 'nom_otro_pago_descripcion', title: "Descripción"},
        {data: null, title: "Importe Gravado"},
        {data: null, title: "Importe Exento"},
        {data: null, title: "Acciones"},
    ], "nom_par_otro_pago");

    let timer = null;

    $('#nom_nomina').on('click', 'tbody td:first-child', function (event) {

        if (timer) {
            clearTimeout(timer);
        }

        timer = setTimeout(() => {
            var selectedData = table_nom_nomina.rows('.selected').data().pluck('nom_nomina_id');

            table_percepciones.clear();
            table_deducciones.clear();
            table_otros_pagos.clear();

            selectedData.each(function (value, row, data) {
                nominas_seleccionadas.push(value);

                let url_percepcion = get_url("nom_par_percepcion", "get_percepciones", {nom_nomina_id: value});
                let url_deduccion = get_url("nom_par_deduccion", "get_deducciones", {nom_nomina_id: value});
                let url_otro_pago = get_url("nom_par_otro_pago", "get_otros_pagos", {nom_nomina_id: value});

                get_data(url_percepcion, function (rows) {
                    var registros = rows.registros;
                    table_percepciones.rows.add(registros).draw();
                });

                get_data(url_deduccion, function (rows) {
                    var registros = rows.registros;
                    table_deducciones.rows.add(registros).draw();
                });

                get_data(url_otro_pago, function (rows) {
                    var registros = rows.registros;
                    table_otros_pagos.rows.add(registros).draw();
                });
            });
            table_percepciones.columns.adjust().draw();
            table_deducciones.columns.adjust().draw();
            table_otros_pagos.columns.adjust().draw();

            $('#agregar_percepcion').val(nominas_seleccionadas);
            $('#agregar_deduccion').val(nominas_seleccionadas);
            $('#agregar_otro_pago').val(nominas_seleccionadas);

        }, 1000);
    });
});