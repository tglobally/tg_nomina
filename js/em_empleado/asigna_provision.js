let sl_empleado = $("#em_empleado_id");
let sl_cliente = $("#com_sucursal_id");
let sl_empresa = $("#org_sucursal_id");

sl_cliente.change(function () {
    let empleado = sl_empleado.find('option:selected');
    let selected = $(this).find('option:selected');

    let url = get_url("tg_empleado_sucursal","get_empresas", {em_empleado_id: empleado.val(), com_sucursal_id: selected.val()});

    get_data(url, function (data) {
        sl_empresa.empty();

        integra_new_option(sl_empresa,'Seleccione un registro','-1');

        $.each(data.registros, function( index, registro ) {
            if (registro.org_sucursal_descripcion_select !== null){
                integra_new_option(sl_empresa,registro.org_sucursal_descripcion_select, registro.org_sucursal_id);
            }
        });
        sl_empresa.selectpicker('refresh');
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






