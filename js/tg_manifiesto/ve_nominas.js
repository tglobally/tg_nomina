$(document).ready(function(){

    var tables = $.fn.dataTable.tables(true);
    var datatable = $(tables).DataTable().search('nom_nomina')

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




    $('#nom_nomina').on('click', 'tbody td:first-child', function () {

        var selected = $( this ).parent().hasClass( "selected" );

        if (!selected){
            var data = datatable.row( this ).data();

            var nomina = `<b> NOMINA: </b> ${data.nom_nomina_id} - ${data.em_empleado_descripcion}`

            let url = get_url("nom_par_percepcion","get_percepciones", {nom_nomina_id: data.nom_nomina_id});

            get_data(url, function (rows) {
                var registros = rows.registros;
                registros.forEach( function(valor, indice, array) {
                    nominas_percepciones.row.add([nomina, valor.nom_percepcion_codigo,
                        valor.nom_percepcion_descripcion, valor.nom_par_percepcion_importe_gravado,
                        valor.nom_par_percepcion_importe_exento]).draw(false);;
                });
            });

        }

    });

});









