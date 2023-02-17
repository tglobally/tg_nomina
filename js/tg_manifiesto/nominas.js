$(document).ready(function () {
    var tables = $.fn.dataTable.tables(true);
    var table_nom_nomina = $(tables).DataTable().search('nom_nomina');

    var nominas_seleccionadas = [];
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

    function buscarValor(objeto, valor) {
        for (var propiedad in objeto) {
            if (typeof objeto[propiedad] == "object") {
                if (buscarValor(objeto[propiedad], valor)) {
                    return true;
                }
            } else {
                if (objeto[propiedad] === valor) {
                    return true;
                }
            }
        }
        return false;
    }

    function buscarKey(objeto, clave) {
        if (objeto.hasOwnProperty(clave)) {
            return true;
        }
        for (var propiedad in objeto) {
            if (typeof objeto[propiedad] == "object") {
                if (buscarKey(objeto[propiedad], clave)) {
                    return true;
                }
            }
        }
        return false;
    }

    let update_registro = (identificador, entidad, accion, tabla) => {

        $(`${identificador} tbody`).on('keyup', 'input.input-accion', function (e) {
            let tr = $(this).parent().parent();
            let rowData = tabla.row(tr).data();
            var classList = $(".input-accion:focus").attr("class").split(/\s+/);

            $(`.${classList[1]}`).val($(this).val());
            $(`.${classList[1]}`).css({'color': 'red'});
            $(`.${classList[1]}`).addClass("update");


            if ((e.keyCode === 13)) {
                let acepta = confirm("Esta seguro de realizar esta acción ?");

                if (acepta) {
                    var elementos = $(`${identificador} .update`);
                    elementos_seleccionados = new Map();

                    $.each(elementos, function (key, value) {
                        let fila = $(value).parent().parent();
                        let filaData = tabla.row(fila).data();
                        let name = $(value).attr("name");

                        filaData[`${entidad}_${name}`] = $(value).val();

                        elementos_seleccionados.set(filaData[entidad + '_id'],
                            {
                                importe_gravado: filaData[entidad + '_importe_gravado'],
                                importe_exento: filaData[entidad + '_importe_exento'],
                            });
                    });

                    let url = get_url(entidad, "modifica_ajax", {registro_id: rowData[entidad + "_id"]});

                    let objetoSimple = Object.fromEntries(elementos_seleccionados);

                    $.post(url, {datos: objetoSimple}, function (e) {

                        console.log(e);

                    }).done(function (e) {
                        const respuesta = JSON.parse(e);
                        alert(respuesta.mensaje);

                        table_percepciones.clear();
                        table_deducciones.clear();
                        table_otros_pagos.clear();

                        nominas_seleccionadas.forEach(function (valor, indice, array) {
                            let url_percepcion = get_url("nom_par_percepcion", "get_percepciones", {nom_nomina_id: valor});
                            let url_deduccion = get_url("nom_par_deduccion", "get_deducciones", {nom_nomina_id: valor});
                            let url_otro_pago = get_url("nom_par_otro_pago", "get_otros_pagos", {nom_nomina_id: valor});

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


                    }).fail(function (error) {
                        alert(error)
                    });
                }
            }
        });
    };


    var table_percepciones = inicializa_datatable("#nominas_percepciones", [
        {data: null, title: "Nomina"},
        {data: 'nom_percepcion_codigo', title: "Código"},
        {data: 'nom_percepcion_descripcion', title: "Descripción"},
        {data: null, title: "Importe Gravado"},
        {data: null, title: "Importe Exento"},
        {data: null, title: "Acciones"},
    ], "nom_par_percepcion", "nom_percepcion");

    var table_deducciones = inicializa_datatable("#nominas_deducciones", [
        {data: null, title: "Nomina"},
        {data: 'nom_deduccion_codigo', title: "Código"},
        {data: 'nom_deduccion_descripcion', title: "Descripción"},
        {data: null, title: "Importe Gravado"},
        {data: null, title: "Importe Exento"},
        {data: null, title: "Acciones"},
    ], "nom_par_deduccion", "nom_deduccion");

    var table_otros_pagos = inicializa_datatable("#nominas_otros_pagos", [
        {data: null, title: "Nomina"},
        {data: 'nom_otro_pago_codigo', title: "Código"},
        {data: 'nom_otro_pago_descripcion', title: "Descripción"},
        {data: null, title: "Importe Gravado"},
        {data: null, title: "Importe Exento"},
        {data: null, title: "Acciones"},
    ], "nom_par_otro_pago", "nom_otro_pago");

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

            nominas_seleccionadas = [];

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

        }, 1500);
    });

    update_registro('#nominas_percepciones', "nom_par_percepcion", "get_percepciones", table_percepciones);
    update_registro('#nominas_deducciones', "nom_par_deduccion", "get_deducciones", table_deducciones);
    update_registro('#nominas_otros_pagos', "nom_par_otro_pago", "get_otros_pagos", table_otros_pagos);

    $('.form_nominas').on('submit', function(e){
        if(nominas_seleccionadas.length === 0) {
            e.preventDefault();
            alert("Seleccione una nómina");
        }
    });

});