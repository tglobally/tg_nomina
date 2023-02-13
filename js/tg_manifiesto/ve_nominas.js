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
        columnDefs: [{ visible: false, targets: groupColumn }],
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
                            .before('<tr class="group"><td colspan="4">' + group + '</td></tr>');
                        last = group;
                    }
                });
        },
    } );

    $('#agregar_percepcion').click(function(event) {
        if (lista_nominas.length == 0){
            event.preventDefault();
            alert("Seleccione una nómina");
        }
    });



    $('#nom_nomina').on('click', 'tbody td:first-child', function () {

        var data = datatable.row( this ).data();

        if (!lista_nominas.includes(data.nom_nomina_id)) {
            lista_nominas.push(data.nom_nomina_id);
        } else {
            lista_nominas = lista_nominas.filter((item) => item !== data.nom_nomina_id)
        }

        $('#percepciones_eliminar').val(lista_nominas)

        nominas_percepciones.clear();

        lista_nominas.forEach( function(value,i,a) {
            let url = get_url("nom_par_percepcion","get_percepciones", {nom_nomina_id: value});

            get_data(url, function (rows) {
                var registros = rows.registros;
                registros.forEach( function(valor, indice, array) {
                    var nomina = `<b> NOMINA: </b> ${valor.nom_nomina_id} - ${valor.em_empleado_descripcion}`;
                    nominas_percepciones.row.add([nomina, valor.nom_percepcion_codigo,
                        valor.nom_percepcion_descripcion, valor.nom_par_percepcion_importe_gravado,
                        valor.nom_par_percepcion_importe_exento]).draw(false);
                });
            });
        });

    });

});









