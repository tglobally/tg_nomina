let url = getAbsolutePath();

let session_id = getParameterByName('session_id');

let sl_nom_empleado = $("#em_empleado_id");
let sl_cat_sat_periodicidad_pago_nom = $("#cat_sat_periodicidad_pago_nom_id");
let sl_em_cuenta_bancaria_id = $("#em_cuenta_bancaria_id");

let txt_rfc = $('#rfc');
let txt_curp = $('#curp');
let txt_nss = $('#nss');
let txt_salario_diario = $('#salario_diario');
let txt_salario_diario_integrado = $('#salario_diario_integrado');
let txt_fecha_inicio_rel_laboral = $('#fecha_inicio_rel_laboral');
let txt_num_dias_pagados = $('#num_dias_pagados');
let txt_fecha_inicial_pago = $('#fecha_inicial_pago');
let txt_fecha_final_pago = $('#fecha_final_pago');
let txt_subtotal = $('#subtotal');
let txt_descuento = $('#descuento');
let txt_total = $('#total');


sl_nom_empleado.change(function(){
    let selected = $(this).find('option:selected');

    let rfc = selected.data('em_empleado_rfc');
    let curp = selected.data('em_empleado_curp');
    let nss = selected.data('em_empleado_nss');
    let salario_diario = selected.data('em_empleado_salario_diario');
    let salario_diario_integrado = selected.data('em_empleado_salario_diario_integrado');
    let fecha_inicio_rel_laboral  = selected.data('em_empleado_fecha_inicio_rel_laboral');

    txt_rfc.val(rfc);
    txt_curp.val(curp);
    txt_nss.val(nss);
    txt_salario_diario.val(salario_diario);
    txt_salario_diario_integrado.val(salario_diario_integrado);
    txt_fecha_inicio_rel_laboral.val(fecha_inicio_rel_laboral);

    let fechaInicioRelLaboral = new Date(txt_fecha_inicio_rel_laboral.val());
    let fechaInicialPago = new Date(txt_fecha_inicial_pago.val());

    if(fechaInicioRelLaboral > fechaInicialPago){
        txt_fecha_inicial_pago.val(fecha_inicio_rel_laboral);
    }

    let sub_Total = subTotal(txt_salario_diario.val(),txt_num_dias_pagados.val())
    let total = sub_Total - txt_descuento.val()

    txt_subtotal.val(sub_Total)
    txt_total.val(total)

    em_empleado_id = $(this).val();

    let url = "index.php?seccion=em_cuenta_bancaria&ws=1&accion=get_cuentas_bancarias&em_empleado_id="+em_empleado_id+"&session_id="+session_id;

    $.ajax({
        type: 'GET',
        url: url,
    }).done(function( data ) {
        console.log(data);

        sl_em_cuenta_bancaria_id.empty();
        integra_new_option("#em_cuenta_bancaria_id",'Seleccione una cuenta bancaria','-1');

        $.each(data.registros, function( index, em_cuenta_bancaria ) {
            integra_new_option("#em_cuenta_bancaria_id",em_cuenta_bancaria.bn_banco_descripcion_select+' '+em_cuenta_bancaria.em_cuenta_bancaria_num_cuenta,em_cuenta_bancaria.em_cuenta_bancaria_id);
        });


        sl_em_cuenta_bancaria_id.selectpicker('refresh');

    }).fail(function (jqXHR, textStatus, errorThrown){
        alert('Error al ejecutar');
        console.log(jqXHR);
    });


});

txt_descuento.change(function() {

    let sub_Total = subTotal(txt_salario_diario.val(),txt_num_dias_pagados.val())
    let total = sub_Total - txt_descuento.val()

    txt_total.val(total)

});

sl_cat_sat_periodicidad_pago_nom.change(function(){
    let selected = $(this).find('option:selected');
    let num_dias_pagados = selected.data('cat_sat_periodicidad_pago_nom_n_dias');

    let fechaFinal

    if (selected.val() !== '') {
        fechaFinal = fecha(txt_fecha_inicial_pago, num_dias_pagados)
    } else {
        fechaFinal = fecha(txt_fecha_inicial_pago)
    }
    txt_num_dias_pagados.val(num_dias_pagados);
    txt_fecha_final_pago.val(fechaFinal)
    let sub_Total = subTotal(txt_salario_diario.val(),txt_num_dias_pagados.val())
    let total = sub_Total - txt_descuento.val()

    txt_subtotal.val(sub_Total)
    txt_total.val(total)
});

txt_fecha_inicial_pago.change(function() {
    let selected = sl_cat_sat_periodicidad_pago_nom.find('option:selected');
    let num_dias_pagados = selected.data('cat_sat_periodicidad_pago_nom_n_dias');

    let fechaFinal

    if (selected.val() !== '') {
        fechaFinal = fecha(txt_fecha_inicial_pago, num_dias_pagados)
    } else {
        num_dias_pagados = (txt_num_dias_pagados.val() !== '') ? txt_num_dias_pagados.val() : 1
        fechaFinal = fecha(txt_fecha_inicial_pago, num_dias_pagados)
    }
    txt_fecha_final_pago.val(fechaFinal)
});

txt_num_dias_pagados.change(function() {
    let selected = sl_cat_sat_periodicidad_pago_nom.find('option:selected');
    let num_dias_pagados = selected.data('cat_sat_periodicidad_pago_nom_n_dias');

    let fechaFinal

    if (selected.val() !== '') {
        fechaFinal = fecha(txt_fecha_inicial_pago, num_dias_pagados)
    } else {
        num_dias_pagados = (txt_num_dias_pagados.val() !== '') ? txt_num_dias_pagados.val() : 1
        fechaFinal = fecha(txt_fecha_inicial_pago, num_dias_pagados)
    }
    txt_fecha_final_pago.val(fechaFinal)
    let sub_Total = subTotal(txt_salario_diario.val(),txt_num_dias_pagados.val())
    let total = sub_Total - txt_descuento.val()
    txt_subtotal.val(sub_Total)
    txt_total.val(total)
});


let fecha = (fechaInicio, numDias = 1) => {

    var fechaInicial = new Date(fechaInicio.val());
    var fechaFinal   = new Date(fechaInicio.val());
    var dias = parseInt(numDias);

    fechaFinal.setDate(fechaInicial.getDate() + dias);
    return fechaFinal.getFullYear() + "-" +  ('0' + (fechaFinal.getMonth()+1)).slice(-2) + "-" + ('0' + fechaFinal.getDate()).slice(-2);
};

let subTotal = (salario = 0, diasPagados = 0) => {
    return salario * diasPagados
};



