let sl_cliente = $("#com_sucursal_id");
let sl_empleados = $(".lista");
let myMultiSelect = document.getElementsByClassName('lista')

let instance = new coreui.MultiSelect(myMultiSelect[0], {})

sl_cliente.change(function () {
    let selected = $(this).find('option:selected');

    let url = get_url("tg_empleado_sucursal","get_empleados", {com_sucursal_id: selected.val()});

    get_data(url, function (data) {
        sl_empleados.find('option').remove();

        $.each(data.registros, function( index, registro ) {
            sl_empleados.append($('<option>', {
                value: registro.em_empleado_id,
                text : registro.em_empleado_descripcion_select
            }));
        });

        instance.update();
    });
})







