$(document).ready(function () {
    var tables = $.fn.dataTable.tables(true);
    var table_nom_nomina = $(tables).DataTable().search('nom_nomina');

    var nominas_seleccionadas = [];
    var percepciones_seleccionadas = [];
    var deducciones_seleccionadas = [];
    var otros_pagos_seleccionadas = [];

    let inicializa_datatable = (identificador , columns, entidad) => {

        return $(identificador).DataTable({
            searching: false,
            paging: false,
            ordering: false,
            info: false,
            columns: columns,
            columnDefs: [
                {targets: 0, visible: false},
                {
                    targets: 5,
                    render: function (data, type, row, meta) {
                        return `<a role='button' title='Elimina' data-id='${data[entidad]}' 
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
                    .column(0, {page: 'current'})
                    .data()
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

    let carga_tabla = (identificador, entidad, accion, tabla) => {
        $(document).on('click', `${identificador} .delete-btn`, function(e){
            const id = $(e.currentTarget).data('id');
            let url = get_url(entidad,"elimina_bd", {registro_id: id}, 0);

            $.ajax({
                url: url,
                type: 'DELETE',
                DataType: 'json',
                success: function (response) {
                    nominas_seleccionadas.forEach( function(valor, indice, array) {
                        let url_data = get_url(entidad,accion, {nom_nomina_id: valor});

                        get_data(url_data, function (rows) {
                            var registros = rows.registros;
                            tabla.clear();
                            tabla.rows.add( registros ).draw();
                        });
                    });
                }
            });
        });
    };

    var table_percepciones = inicializa_datatable("#nominas_percepciones", [
        { data: null, title: "Nomina" },
        { data: 'nom_percepcion_codigo', title: "Código" },
        { data: 'nom_percepcion_descripcion', title: "Descripción" },
        { data: 'nom_par_percepcion_importe_gravado', title: "Importe Gravado" },
        { data: 'nom_par_percepcion_importe_exento', title: "Importe Exento" },
        { data: null, title: "Acciones" },
    ], "nom_par_percepcion_id");

    var table_deducciones = inicializa_datatable("#nominas_deducciones", [
        { data: null, title: "Nomina" },
        { data: 'nom_deduccion_codigo', title: "Código" },
        { data: 'nom_deduccion_descripcion', title: "Descripción" },
        { data: 'nom_par_deduccion_importe_gravado', title: "Importe Gravado" },
        { data: 'nom_par_deduccion_importe_exento', title: "Importe Exento" },
        { data: null, title: "Acciones" },
    ], "nom_par_deduccion_id");

    var table_otros_pagos = inicializa_datatable("#nominas_otros_pagos", [
        { data: null, title: "Nomina" },
        { data: 'nom_otro_pago_codigo', title: "Código" },
        { data: 'nom_otro_pago_descripcion', title: "Descripción" },
        { data: 'nom_par_otro_pago_importe_gravado', title: "Importe Gravado" },
        { data: 'nom_par_otro_pago_importe_exento', title: "Importe Exento" },
        { data: null, title: "Acciones" },
    ], "nom_par_otro_pago_id");


    $('#nom_nomina').on('click', 'tbody td:first-child', function () {

        setTimeout(function () {
            var seleccionadas = $('.selected').map(function () {
                return this;
            }).get();

            nominas_seleccionadas = [];

            seleccionadas.forEach( function(valor, indice, array) {
                var data = table_nom_nomina.row( valor ).data();
                nominas_seleccionadas.push(data.nom_nomina_id);
            });

            nominas_seleccionadas.forEach( function(valor, indice, array) {
                let url_percepcion = get_url("nom_par_percepcion","get_percepciones", {nom_nomina_id: valor});
                let url_deduccion = get_url("nom_par_deduccion","get_deducciones", {nom_nomina_id: valor});
                let url_otro_pago = get_url("nom_par_otro_pago","get_otros_pagos", {nom_nomina_id: valor});

                get_data(url_percepcion, function (rows) {
                    var registros = rows.registros;
                    table_percepciones.clear();
                    table_percepciones.rows.add( registros ).draw();
                });

                get_data(url_deduccion, function (rows) {
                    var registros = rows.registros;
                    table_deducciones.clear();
                    table_deducciones.rows.add( registros ).draw();
                });

                get_data(url_otro_pago, function (rows) {
                    var registros = rows.registros;
                    table_otros_pagos.clear();
                    table_otros_pagos.rows.add( registros ).draw();
                });
            });

            $('#agregar_percepcion').val(nominas_seleccionadas);

        }, 500);
    });

    carga_tabla('#nominas_percepciones', "nom_par_percepcion", "get_percepciones", table_percepciones);
    carga_tabla('#nominas_deducciones', "nom_par_deduccion", "get_deducciones", table_deducciones);
    carga_tabla('#nominas_otros_pagos', "nom_par_otro_pago", "get_otros_pagos", table_otros_pagos);

    $('.form_nominas').on('submit', function(e){
        if(nominas_seleccionadas.length === 0) {
            e.preventDefault();
            alert("Seleccione una nómina");
        }
    });

});