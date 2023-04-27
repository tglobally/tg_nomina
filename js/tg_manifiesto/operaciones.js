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
                {targets: 0, visible: false},
                {
                    targets: 3,
                    render: function (data, type, row, meta) {
                        var input = document.createElement('input');
                        input.setAttribute("type", "text");
                        input.setAttribute("class", `input-accion ${clase + '_' + data[clase + '_id'] + '_importe_gravado'}`);
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
                        input.setAttribute("class", `input-accion ${clase + '_' + data[clase + '_id'] + '_importe_exento'}`);
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


    let timer = null;

    $('#nom_nomina').on('click', 'tbody td:first-child', function (event) {

        if (timer) {
            clearTimeout(timer);
        }

        timer = setTimeout(() => {
            var selectedData = table_nom_nomina.rows('.selected').data().pluck('nom_nomina_id');

            datatables.forEach((tabla) => {
                tabla.clear();
            });

            nominas_seleccionadas = [];

            selectedData.each(function (value, row, data) {
                nominas_seleccionadas.push(value);

                let url_percepcion = get_url("nom_nomina_documento", "get_documentos_nomina", {nom_nomina_id: value});

                get_data(url_percepcion, function (rows) {
                    var registros = rows.registros;
                    console.log(registros);
                });
            });

            datatables.forEach((tabla) => {
                tabla.columns.adjust().draw();
            });

            $('#nominas_genera_xmls').val(nominas_seleccionadas);

        }, 1000);
    });

    $('.form_nominas').on('submit', function(e){
        e.preventDefault();

        if(nominas_seleccionadas.length === 0) {
            alert("Seleccione una nómina");
            return;
        }

        let url = get_url("tg_manifiesto", "genera_xmls", {});
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