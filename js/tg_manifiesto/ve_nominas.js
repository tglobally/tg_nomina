$(document).ready(function(){

    var tables = $.fn.dataTable.tables(true);
    var datatable = $(tables).DataTable().search('nom_nomina')

    var lista_nominas = [];
    var groupColumn = 0;

    var nominas_percepciones = $("#nominas_percepciones").DataTable({
        searching: false,
        paging: false,
        ordering: false,
        info: false,
        columnDefs: [
            { targets: groupColumn, visible: false },
            { targets: 5,
                render: function (data, type, row, meta) {
                    return `<a role='button' title='Elimina' data-id='${data}' class='btn btn-danger btn-sm delete-btn' 
                               style='margin-left: 2px; margin-bottom: 2px; '>Elimina</a>`;
                }
            }
        ],
        order: [[groupColumn, 'asc']],
        displayLength: 10,
        drawCallback: function (settings) {
            var api = this.api();
            var rows = api.rows({ page: 'current' }).nodes();
            var last = null;

            api
                .column(groupColumn, { page: 'current' })
                .data()
                .each(function (group, i) {
                    if (last !== group) {
                        $(rows)
                            .eq(i)
                            .before('<tr class="group"><td colspan="5">' + group + '</td></tr>');
                        last = group;
                    }
                });
        },
    } );

    $('.form_nominas').on('submit', function(e){
        if(lista_nominas.length === 0) {
            e.preventDefault();
            alert("Seleccione una nómina");
        }
    });

    $(document).on('click', '.delete-btn', function(e){
        const id = $(e.currentTarget).data('id');

        $.ajax({
            url: `http://localhost/tg_nomina/index.php?seccion=nom_par_percepcion&accion=elimina_bd&session_id=7358996659&registro_id=${id}`,
            type: 'DELETE',
            DataType: 'json',
            success: function (response) {
                nominas_percepciones.clear();

                lista_nominas.forEach( function(value,i,a) {
                    let url = get_url("nom_par_percepcion","get_percepciones", {nom_nomina_id: value});

                    get_data(url, function (rows) {
                        var registros = rows.registros;
                        registros.forEach( function(valor, indice, array) {
                            let button = get_url("nom_par_percepcion","elimina_bd", {registro_id: valor.nom_par_percepcion_id});
                            button = `http://localhost/tg_nomina/index.php?seccion=nom_par_percepcion&accion=elimina_bd&session_id=7358996659&registro_id=${valor.nom_par_percepcion_id}`;

                            var nomina = `<b> NOMINA: </b> ${valor.nom_nomina_id} - ${valor.em_empleado_descripcion}`;
                            nominas_percepciones.row.add([nomina, valor.nom_percepcion_codigo,
                                valor.nom_percepcion_descripcion, valor.nom_par_percepcion_importe_gravado,
                                valor.nom_par_percepcion_importe_exento, valor.nom_par_percepcion_id]).draw(false);
                        });
                    });
                });
            }

        });
    });




    $('#nom_nomina').on('click', 'tbody td:first-child', function () {

        var data = datatable.row( this ).data();

        if (!lista_nominas.includes(data.nom_nomina_id)) {
            lista_nominas.push(data.nom_nomina_id);
        } else {
            lista_nominas = lista_nominas.filter((item) => item !== data.nom_nomina_id)
        }

        $('#agregar_percepcion').val(lista_nominas)
        $('#percepciones_eliminar').val(lista_nominas)

        nominas_percepciones.clear();

        lista_nominas.forEach( function(value,i,a) {
            let url = get_url("nom_par_percepcion","get_percepciones", {nom_nomina_id: value});

            get_data(url, function (rows) {
                var registros = rows.registros;
                registros.forEach( function(valor, indice, array) {
                    let button = get_url("nom_par_percepcion","elimina_bd", {registro_id: valor.nom_par_percepcion_id});
                    button = `http://localhost/tg_nomina/index.php?seccion=nom_par_percepcion&accion=elimina_bd&session_id=7358996659&registro_id=${valor.nom_par_percepcion_id}`;

                    var nomina = `<b> NOMINA: </b> ${valor.nom_nomina_id} - ${valor.em_empleado_descripcion}`;
                    nominas_percepciones.row.add([nomina, valor.nom_percepcion_codigo,
                        valor.nom_percepcion_descripcion, valor.nom_par_percepcion_importe_gravado,
                        valor.nom_par_percepcion_importe_exento, valor.nom_par_percepcion_id]).draw(false);
                });
            });
        });

    });

});









