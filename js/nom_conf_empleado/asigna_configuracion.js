let sl_cliente = $("#com_sucursal_id");
const sl_empleados = document.getElementById('empleados')

let sl_empresa = $("#org_sucursal_id");

sl_cliente.change(function () {
    let selected = $(this).find('option:selected');

    let url = get_url("tg_empleado_sucursal","get_empleados", {com_sucursal_id: selected.val()});

    get_data(url, function (data) {

        let index = sl_empleados.options.length;
        while (index--) {
            if (selected[index]) {
                sl_empleados.remove(index);
            }
        }

        let options = [];

        $.each(data.registros, function( index, registro ) {
            options.push({
                value: registro.em_empleado_id,
                text: registro.em_empleado_descripcion_select
            });
        });

        if (sl_empleados) {
            new coreui.MultiSelect(sl_empleados, {
                name: 'empleados',
                options: options,
                search: true
            })
        }

    });
})

sl_empresa.change(function () {
    let empleado = sl_empleado.find('option:selected');
    let cliente = sl_cliente.find('option:selected');
    let empresa = $(this).find('option:selected');

    let url = get_url("tg_conf_provisiones_empleado","get_provisiones", {em_empleado_id: empleado.val(),
        com_sucursal_id: cliente.val(), org_sucursal_id: empresa.val()});

    get_data(url, function (data) {

        $.each(data.registros, function( index, registro ) {

            switch (registro.tg_tipo_provision_descripcion) {
                case "PRIMA VACACIONAL":
                    $("input[name='prima_vacacional']").prop('checked', true);break;
                case "VACACIONES":
                    $("input[name='vacaciones']").prop('checked', true);break;
                case "PRIMA DE ANTIGÜEDAD":
                    $("input[name='prima_antiguedad']").prop('checked', true);break;
                case "GRATIFICACIÓN ANUAL (AGUINALDO)":
                    $("input[name='aguinaldo']").prop('checked', true);break;
            }
        });


    });
})






