let url = getAbsolutePath();

let session_id = getParameterByName('session_id');
let registro_id = getParameterByName('registro_id');

let txt_salario_diario = $('#salario_diario');
let txt_salario_diario_integrado = $('#salario_diario_integrado');
let txt_fecha_inicio_rel_laboral = $('#fecha_inicio_rel_laboral');

txt_salario_diario.change(function (){
    let fecha_inicio_rel_laboral = txt_fecha_inicio_rel_laboral.val();
    if(fecha_inicio_rel_laboral === ''){
        alert("Por favor integra una fecha de inicio de relacion laborar valida")
        let salario_diario = $(this).val('0');
        return false;
    }
    let salario_diario = $(this).val();
    let url = "index.php?seccion=em_empleado&ws=1&accion=calcula_sdi&em_empleado_id="+registro_id+"&fecha_inicio_rel_laboral="+fecha_inicio_rel_laboral+"&salario_diario="+salario_diario+"&session_id="+session_id;

    getData(url,(data) => {
        txt_salario_diario_integrado.val(data);
    });
});

    let getData = async (url, acciones) => {
    fetch(url)
        .then(response => response.json())
        .then(data => acciones(data))
        .catch(err => {
            alert(err.message);
            console.error("ERROR: ", err.message)
        });
    }


